<?php
namespace Core\Geobaza;


class Geobaza extends Serializer implements \Iterator, \Countable, \ArrayAccess {
    /**
     * Iterator position
     * @var integer
     */
    private $position = 0;

    /**
     * @var null|bool
     */
    private $is_special = NULL;

    /**
     * @var GeobazaObject[]
     */
    private $list = array();

    /**
     * @var Country
     */
    private $country;

    /**
     * @var Region[]
     */
    private $regions = array();

    /**
     * @var Locality[]
     */
    private $localities = array();

    /**
     * @param object $meta
     */
    public function __construct($meta) {
        $this->meta = $meta;
        $this->headers = $meta->headers;
    }

    /**
     * @return GeobazaObject|mixed
     */
    public function current() {
        return $this->list[$this->position];
    }

    /**
     * @return int|mixed
     */
    public function key() {
        return $this->position;
    }

    public function next() {
        $this->position++;
    }

    public function rewind() {
        $this->position = 0;
    }

    /**
     * @return bool
     */
    public function valid() {
        if ($this->position < count($this->list)) {
            return true;
        }
        return false;
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     * @throws \Exception
     */
    public function offsetSet($offset, $value) {
        throw new \Exception(__CLASS__.': You can not assign values to Geobaza!');
    }

    /**
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists($offset) {
        return isset($this->list[$offset]);
    }

    /**
     * @param mixed $offset
     * @throws \Exception
     */
    public function offsetUnset($offset) {
        throw new \Exception(__CLASS__.':You can not change Geobaza object list!');
    }

    /**
     * @param mixed $offset
     * @return GeobazaObject|mixed
     */
    public function offsetGet($offset) {
        if (isset($this->list[$offset])) {
            return $this->list[$offset];
        }
        throw new \OutOfRangeException(__CLASS__.':Invalid offset!');
    }

    protected function set_special_flag() {
        if (!isset($this->is_special)) {
            foreach ($this->list as $obj) {
                if ($obj->type == GeobazaObject::SPECIAL) {
                    $this->is_special = true;
                    return;
                }
            }
            $this->is_special = false;
        }
    }

    /**
     * @param $name
     * @return Geobaza|bool
     */
    public function __get($name) {
        switch ($name) {
            case 'is_special':
                $this->set_special_flag();
                return $this->is_special;
            case 'localities':
                return self::from_list($this->localities, $this->meta);
            case 'regions':
                return self::from_list($this->regions, $this->meta);
            case 'country':
                return $this->country;
        }
    }

    /**
     * @param AbstractObject $item
     */
    public function append($item) {
        $this->add_by_type($item);
        $this->list[] = $item;
    }

    /**
     * @param GeobazaObject[] $items
     */
    public function extend($items) {
        foreach ($items as $item) {
            $this->append($item);
        }
    }

    /**
     * @param array $data
     * @param object $meta
     * @return Geobaza
     */
    static public function from_array($data, $meta) {
        $geobaza = new self($meta);
        $data = array_reverse($data);
        $parent = NULL;

        foreach ($data as $obj) {
            if (isset($obj->special)) {
                $geobaza->append(new SpecialRange($obj, $meta->encoding));
            }
            else if (isset($obj->parent)) {
                # If object only contains link
                $parent = $obj->parent;
            }
            else {
                $inst = Geobaza::create_instance($obj, $meta->encoding);
                $inst->query = $meta->query;
                $geobaza->append($inst);
                $inst->parent = $parent;
                if ($parent instanceof AbstractObject) {
                    $parent->child = $inst;
                }
                $parent = $inst;
            }
        }
        return $geobaza;
    }

    /**
     * @param GeobazaObject[] $items
     * @param object $meta
     * @return Geobaza
     */
    static public function from_list($items, $meta) {
        $geobaza = new self($meta);
        $geobaza->extend($items);
        return $geobaza;
    }

    /**
     * @param GeobazaObject $obj
     * @param string $encoding
     * @param GeobazaObject|null $child
     * @param GeobazaObject|null $parent
     * @return GeobazaObject
     */
    static public function create_instance($obj, $encoding, $child=NULL, $parent=NULL) {
        $type_map = array(
            GeobazaObject::COUNTRY => '\\Core\\Geobaza\\Country',
            GeobazaObject::REGION => '\\Core\\Geobaza\\Region',
            GeobazaObject::LOCALITY => '\\Core\\Geobaza\\Locality'
        );
        $type = $obj->type;
        $item_class = $type_map[$type];
        $item = new $item_class($obj, $encoding);
        $item->child = $child;
        if (!empty($parent)) {
            $item->parent = $parent;
        }
        return $item;
    }

    /**
     * @param GeobazaObject $obj
     */
    protected function add_by_type($obj) {
        switch ($obj->type) {
            case GeobazaObject::COUNTRY:
                $this->country = $obj;
                break;
            case GeobazaObject::REGION:
                $this->regions[] = $obj;
                break;
            case GeobazaObject::LOCALITY:
                $this->localities[] = $obj;
                break;
        }
    }

    /**
     * @param string $delimiter
     * @return string
     */
    public function name_path($delimiter=', ') {
        return join($delimiter, $this->name_list());
    }

    /**
     * @return string[]
     */
    public function name_list() {
        $list = array();
        foreach ($this as $item) {
            $list[] = $item->name;
        }
        return $list;
    }

    /**
     * @return int[]
     */
    public function id_list() {
        $list = array();
        foreach ($this as $item) {
            $list[] = $item->id;
        }
        return $list;
    }

    /**
     * @return GeobazaObject
     */
    public function first() {
        return $this->list[0];
    }

    /**
     * @return GeobazaObject
     */
    public function last() {
        return $this->list[count($this->list) - 1];
    }

    /**
     * @return int
     */
    public function count() {
        return count($this->list);
    }

    /**
     * @return \DOMDocument
     */
    public function as_xml() {
        $xml = new \DOMDocument('1.0', $this->meta->encoding);
        $root = $xml->createElement('geobaza');
        $root->setAttribute('api-version', $this->headers->api_version);
        $root->setAttribute('release', $this->headers->release);
        $root->setAttribute('build-timestamp', $this->headers->build_timestamp);
        $root->setAttribute('build-date', $this->headers->build_date);
        $root->setAttribute('lite', (int)$this->headers->lite);
        $root->setAttribute('query-timestamp', time(true));
        $root->setAttribute('ip', $this->meta->ip);
        $this->set_special_flag();
        $root->setAttribute('is-special', (int)$this->is_special);

        $objects = $xml->createElement('objects');
        foreach ($this as $item) {
            $item_xml = $item->as_xml();
            $node = $xml->importNode($item_xml->documentElement, true);
            $objects->appendChild($node);
        }
        $root->appendChild($objects);
        $xml->appendChild($root);
        return $xml;
    }

    /**
     * @return array
     */
    public function as_array() {
        $this->set_special_flag();
        $object = array(
            'api_version' => $this->headers->api_version,
            'release' => $this->headers->release,
            'build_timestamp' => $this->headers->build_timestamp,
            'build_date' => $this->headers->build_date,
            'lite' => (int)$this->headers->lite,
            'query_timestamp' => time(true),
            'ip' => $this->meta->ip,
            'is_special' => (int)$this->is_special,
        );
        $object['objects'] = array();
        foreach ($this as $item) {
            $object['objects'][] = $item->as_array();
        }
        return $object;
    }
}