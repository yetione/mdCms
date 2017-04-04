<?php
namespace Core\Geobaza;


use Core\Geobaza\Exception\GeobazaException;

class GeobazaQuery extends Serializer {
    /**
     * @var int[]
     */
    private $level = array();

    /**
     * @var int
     */
    private $offset;

    /**
     * @var FileSingleton
     */
    private $f;

    /**
     * @var object
     */
    private $meta;

    /**
     * @var string
     */
    private $encoding;

    /**
     * @param string $name
     * @return string
     */
    public function __get($name) {
        if ($name == 'encoding') {
            return $this->encoding;
        }
    }

    /**
     * @param string $path
     * @param int $cache
     * @param string $encoding
     * @throws GeobazaException
     */
    public function __construct($path=GEOBAZA_FILE_PATH, $cache=GEOBAZA_CACHE_NO, $encoding='utf-8') {
        $this->encoding = strtolower($encoding);

        if ($cache == GEOBAZA_CACHE_MEMORY) {
            $this->f = BinaryBlob::init($path);
            $this->f->seek(0);
        }
        else {
            $this->f = BinaryFile::init($path);
            $this->f->seek(0);
        }

        if ($this->f->read(7) != 'GEOBAZA') {
            throw new GeobazaException('Invalid datafile signature!');
        }

        # Headers
        $unpack = $this->f->unpack(2, 'nlen');
        $this->headers = json_decode($this->f->read($unpack['len']));
        $this->headers->lite = (isset($this->headers->lite) ? true : false);
        $this->headers->release = trim($this->headers->release);

        while (true) {
            $data = $this->f->unpack(1, 'Cdata');
            $hi = ($data['data'] >> 4) & 0x0f;
            $this->level[] = $hi;
            if ($hi == 0) {
                break;
            }
            $lo = $data['data'] & 0x0f;
            $this->level[] = $lo;
            if ($lo == 0) {
                break;
            }
        }

        $this->offset = $this->f->tell();
    }

    /**
     * @param int $offset
     * @param bool $path
     * @return string
     */
    protected function get_json($offset, $path=false) {
        $json_str = array();
        while ($offset) {
            $length = $this->get_length($offset);
            if ($length > 0) {
                $obj_str = $this->f->read($length);
                $unp_offset = $this->f->unpack(4, 'Ndata');
                $offset = $unp_offset['data'];
                $json_str[] = $obj_str;
                if ($path == false) {
                    $length = $this->get_length($offset);
                    if ($length && $offset) {
                        $json_str[] = sprintf('{"parent": %d}', $offset);
                    }
                    break;
                }
            }
            else {
                return null;
            }
        }
        return sprintf('[%s]', join(',', $json_str));
    }

    /**
     * @param int $offset
     * @return int
     */
    protected function get_length($offset) {
        $position = $offset & 0x7fffffff;
        $this->f->seek($position, 0);
        $unpack = $this->f->unpack(2, 'nlen');
        return $unpack['len'] & 0xffff;
    }

    /**
     * @param $ip
     * @param bool $path
     * @return string
     */
    protected function get_data($ip, $path=false) {
        $offset = $this->offset;
        $ip_int = ip2long($ip);
        $shift = 32;
        for ($i = 0; $i < sizeof($this->level); $i++) {
            $shift -= $this->level[$i];
            $index = (($ip_int >> $shift)) & ((1 << $this->level[$i]) - 1);
            $tell = $offset + $index * 4;
            $this->f->seek($tell, 0);
            $unpack = $this->f->unpack(4, 'Ndata');
            $offset = $unpack['data'] & 0xffffffff;
            if ($offset & 0x80000000) {
                return $this->get_json($offset, $path);
            }
        }
    }

    /**
     * @param $ip
     * @return object
     */
    protected function get_meta($ip) {
        $meta = array(
            'ip' => $ip,
            'query' => $this,
            'headers' => $this->headers,
            'encoding' => $this->encoding
        );
        return (object)$meta;
    }

    /**
     * @param $ip
     * @return GeobazaObject
     */
    public function get($ip) {
        $json_str = $this->get_data($ip);
        if (!empty($json_str)) {
            $geobaza = Geobaza::from_array(json_decode($json_str), $this->get_meta($ip));
            return $geobaza->first();
        }
    }

    /**
     * @param $ip
     * @return Geobaza
     */
    public function get_list($ip) {
        $json_str = $this->get_data($ip);
        if (!empty($json_str)) {
            $geobaza = Geobaza::from_array(json_decode($json_str), $this->get_meta($ip));
            return $geobaza;
        }
    }

    /**
     * @param $ip
     * @return Geobaza
     */
    public function get_path($ip) {
        $json_str = $this->get_data($ip, $path=true);
        if (!empty($json_str)) {
            $geobaza = Geobaza::from_array(json_decode($json_str), $this->get_meta($ip));
            return $geobaza;
        }
    }

    /**
     * @param int $offset
     * @param bool $asArray
     * @return mixed
     */
    public function get_from($offset, $asArray=false) {
        $json_str = $this->get_json($offset);
        if (!empty($json_str)) {
            return json_decode($json_str, $asArray);
        }
    }

    /**
     * @return \DOMDocument
     */
    public function as_xml(){
        $xml = new \DOMDocument('1.0');
        return $xml;
    }

    /**
     * @return array
     */
    public function as_array(){
        return array();
    }
}