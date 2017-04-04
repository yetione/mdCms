<?php
namespace Core;


class SharedMemory {

    /**
     * @var bool
     */
    protected $useSemaphores = true;

    /**
     * @var string
     */
    protected $filePath;

    /**
     * @var int
     */
    protected $perm;

    /**
     * @var int
     */
    protected $fileToken;

    /**
     * @var int
     */
    protected $shmId;

    /**
     * Размер участка памяти
     * @var int
     */
    protected $shmSize = 1024;

    protected $semId = null;

    protected $secsToGetSemaphore = 10;

    protected $tickPerSec = 2;

    const READ = 'a';

    const CREATE = 'c';

    const WRITE = 'w';

    const SAFE_CREATE = 'n';


    public function __construct($fileName, $useSemaphores=true, $perm=0644){
        $this->useSemaphores = $useSemaphores;
        $this->perm = $perm;
        \QS_path(array('_cache', 'shm'), false, true, true, $perm);
        $this->filePath = \QS_path(array('_cache', 'shm', $fileName), false, false, true, $perm);
        if (!\file_exists($this->filePath)){
            \file_put_contents($this->filePath, '');
        }
        $this->fileToken = \ftok($this->filePath, 'q');
        $this->shmId = null;
        $this->semId = null;
        $this->open();
    }

    public function __destruct(){
        if ($this->useSemaphores && $this->semId){
            \sem_remove($this->semId);
        }
        $this->close();
    }

    public function open($flag=self::READ, $mode=0, $size=0){
        $this->shmId = \shmop_open($this->fileToken, $flag, $mode, $size);
        return $this->shmId;
    }

    public function read(){
        if ($this->acquire()){
            $this->open();
            $storageLength = (int) \shmop_read($this->shmId, 0, 8);
            $storageReaded = \shmop_read($this->shmId, 8, (int) $storageLength);
            $storage= \unserialize(\strval($storageReaded));
            $this->release();
            return $storage;
        }
        return null;
    }

    public function write($data){
        if ($this->acquire()){
            $dataToWrite = \serialize($data);
            $size = strlen($dataToWrite)+8;
            $this->open(self::CREATE, $this->perm, $size);
            //$this->open(self::CREATE, $this->perm, $this->shmSize);
            $storageLength =  \strlen($dataToWrite);
            \shmop_write($this->shmId,  (int) $storageLength, 0);
            \shmop_write($this->shmId, $dataToWrite, 8);
            $this->close();
            return $this->release();
        }
        return false;

    }

    public function delete(){
        if ($this->shmId){
            return \shmop_delete($this->shmId);
        }
        return false;
    }

    public function size(){
        if ($this->shmId){
            return \shmop_size($this->shmId) - 8;
        }
        return -1;
    }

    public function close(){
        if ($this->shmId){
            \shmop_close($this->shmId);
        }
    }

    protected function acquire(){
        if ($this->useSemaphores){
            $this->getSemaphoreId();
            if (!\sem_acquire($this->semId)){
                var_dump('Cant acquire semaphore with id');
                \sem_remove($this->semId);
                return false;
            }
            return true;

        }
        return true;
    }

    protected function release(){
        if ($this->useSemaphores && !is_null($this->semId)){
            return \sem_release($this->semId);
        }
        return false;
    }

    protected function getSemaphoreId(){
        if ($this->useSemaphores){
            if (is_null($this->semId)){
                if (!($this->semId = \sem_get($this->fileToken, 1))){
                    throw new \RuntimeException(__CLASS__.': Can\'t use semaphore');
                }
            }
            return $this->semId;
        }
        return null;
    }
} 