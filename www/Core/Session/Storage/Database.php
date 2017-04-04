<?php
namespace Core\Session\Storage;


use Core\Session\StorageInterface;

class Database implements StorageInterface{

    public function __construct(){
        $this->time = 'TIME';
    }

    /**
     * @param string $savePath
     * @param string $sessionName
     * @return bool
     */
    public function open($savePath, $sessionName)
    {
        // TODO: Implement open() method.
    }

    /**
     * @return bool
     */
    public function close()
    {
        // TODO: Implement close() method.
    }

    /**
     * @param string $sessionId
     * @return string
     */
    public function read($sessionId)
    {
        // TODO: Implement read() method.
    }

    /**
     * @param string $sessionId
     * @param string $data
     * @return bool
     */
    public function write($sessionId, $data)
    {
        // TODO: Implement write() method.
    }

    /**
     * @param string $sessionId
     * @return bool
     */
    public function destroy($sessionId)
    {
        // TODO: Implement destroy() method.
    }

    /**
     * @param int $lifetime
     * @return bool
     */
    public function gc($lifetime)
    {
        // TODO: Implement gc() method.
    }
}