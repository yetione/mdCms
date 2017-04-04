<?php
namespace Core\Geobaza;


class FileSingleton {
    /**
     * @var FileSingleton[]
     */
    protected static $instances = array();

    public function __clone() {
        trigger_error('Clone is not allowed.', E_USER_ERROR);
    }

    public function __wakeup() {
        trigger_error('Unserializing is not allowed.', E_USER_ERROR);
    }

    /**
     * @param int $bytes
     * @param string $fmt
     * @return array
     */
    public function unpack($bytes, $fmt) {
        $data = $this->read($bytes);
        $unp = unpack($fmt, $data);
        return $unp;
    }
}