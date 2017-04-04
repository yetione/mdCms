<?php
namespace Core\Cache\Driver;
class KeyAutoUnLocker implements IKeyLocker {
    protected $key = '';
    /**
     * @param callable $Unlock
     */
    protected $Unlock = NULL;

    public function __construct($Unlock)
    {
        if (is_callable($Unlock)) $this->Unlock = $Unlock;
    }

    public function revoke()
    {
        $this->Unlock = NULL;
    }

    public function __destruct()
    {
        if (isset($this->Unlock)) call_user_func($this->Unlock, $this);
    }

    public function getKey()
    {
        return $this->key;
    }

    public function setKey($key)
    {
        $this->key = $key;
    }
} 