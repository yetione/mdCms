<?php
namespace Core\Session;


interface StorageInterface {

    /**
     * @param string $savePath
     * @param string $sessionName
     * @return bool
     */
    public function open($savePath, $sessionName);

    /**
     * @return bool
     */
    public function close();

    /**
     * @param string $sessionId
     * @return string
     */
    public function read($sessionId);

    /**
     * @param string $sessionId
     * @param string $data
     * @return bool
     */
    public function write($sessionId, $data);


    /**
     * @param string $sessionId
     * @return bool
     */
    public function destroy($sessionId);

    /**
     * @param int $lifetime
     * @return bool
     */
    public function gc($lifetime);
} 