<?php
namespace Core\Geobaza;


class BinaryFile extends FileSingleton {
    /**
     * @param $path
     * @return BinaryFile
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
        $this->file = fopen($path, 'r');
    }

    /**
     * @param $bytes
     * @return string
     */
    public function read($bytes) {
        return fread($this->file, $bytes);
    }

    /**
     * @param $offset
     * @param int $whence
     * @return int
     */
    public function seek($offset, $whence=SEEK_SET) {
        return fseek($this->file, $offset, $whence);
    }

    /**
     * @return int
     */
    public function tell() {
        return ftell($this->file);
    }
}