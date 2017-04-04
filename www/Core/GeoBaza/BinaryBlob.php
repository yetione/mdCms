<?php
namespace Core\Geobaza;


class BinaryBlob extends FileSingleton {
    /**
     * @var int
     */
    private $position;

    /**
     * @var string
     */
    private $blob;

    /**
     * @param $path
     * @return BinaryBlob
     */
    public static function init($path) {
        if (!array_key_exists($path, self::$instances)) {
            self::$instances[$path] = new self($path);
        }
        return self::$instances[$path];
    }

    /**
     * @param string $path
     */
    private function __construct($path) {
        $this->blob = file_get_contents($path);
        $this->position = 0;
    }

    /**
     * @param int $bytes
     * @return string
     */
    public function read($bytes) {
        $chunk = substr($this->blob, $this->position, $bytes);
        $this->position += $bytes;
        return $chunk;
    }

    /**
     * @param int $offset
     * @param int $whence
     */
    public function seek($offset, $whence=0) {
        switch ($whence) {
            case 1:
                $this->position += $offset;
            case 2:
                $this->position = count($this->blob) + $offset;
            default:
                $this->position = $offset;
        }
    }

    /**
     * @return int
     */
    public function tell() {
        return $this->position;
    }
}