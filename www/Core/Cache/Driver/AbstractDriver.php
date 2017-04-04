<?php
namespace Core\Cache\Driver;


use Core\Cache\Exception\CacheException;
use Core\IExportable;

abstract class AbstractDriver implements IExportable {

    /**
     * Время истечения по умолчанию
     * @var int
     */
    protected $defaultTTL = 0;

    const KEY = 'key';
    const VALUE = 'value';
    const CREATE_TIME = 'create_time';
    const ACCESS_TIME = 'access_time';
    const LIFE_TIME = 'lifetime';
    const TTL = 'ttl';

    const THROW_EXCEPTION = 0;
    const RETURN_FALSE = 1;
    const RETURN_NULL = 2;

    protected $onKeyNotFound = self::THROW_EXCEPTION;
    protected $onKeyExpire = self::RETURN_NULL;

    protected $config = array();

    /*
     * Check if this Cache driver is available for server or not
     */
    public function __construct($config = array()){
        $this->setup($config);
    }
    abstract public function checkdriver();

    /**
     * @return int
     */
    public function getOnKeyNotFound(){
        return $this->onKeyNotFound;
    }

    /**
     * @param int $onKeyNotFound
     */
    public function setOnKeyNotFound($onKeyNotFound){
        $this->onKeyNotFound = $onKeyNotFound;
    }

    /**
     * @return int
     */
    public function getOnKeyExpire(){
        return $this->onKeyExpire;
    }

    /**
     * @param int $onKeyExpire
     */
    public function setOnKeyExpire($onKeyExpire){
        $this->onKeyExpire = $onKeyExpire;
    }


    /**
     * @param $key
     * @return false|null
     * @throws CacheException
     */
    protected function onKeyNotFoundAction($key){
        return $this->doAction($this->getOnKeyNotFound(), 'Core\\Cache\\Exception\\KeyNotExists', "Key {$key} not found.");
    }

    /**
     * @param $key
     * @return false|null
     * @throws CacheException
     */
    protected function onKeyExpireAction($key){
        return $this->doAction($this->getOnKeyNotFound(), 'Core\\Cache\\Exception\\KeyExpire', "Key {$key} expire.");
    }

    /**
     * @param int $action
     * @param string $exceptionClass
     * @param string $exceptionMessage
     * @return null|false
     * @throws CacheException
     */
    protected function doAction($action, $exceptionClass='Core\\Cache\\Exception\\CacheException', $exceptionMessage=''){
        switch ($action){
            case self::THROW_EXCEPTION:
                throw new $exceptionClass($exceptionMessage);
                break;
            case self::RETURN_NULL;
                return null;
            case self::RETURN_FALSE:
                return false;
            default:
                throw new CacheException('Unknown action type: '.$action);
        }
    }

    /*
     * SET
     * set a obj to cache
     */
    abstract public function driver_set($keyword, $value = "", $time = 300, $option = array() );
    /*
     * GET
     * return null or value of cache
     */
    abstract public function driver_get($keyword, $option = array());
    /*
     * Stats
     * Show stats of caching
     * Return array ("info","size","data")
     */
    abstract public function driver_stats($option = array());
    /*
     * Delete
     * Delete a cache
     */
    abstract public function driver_delete($keyword, $option = array());
    /*
     * clean
     * Clean up whole cache
     */
    abstract public function driver_clean($option = array());

    /**
     * @return int
     */
    public function getDefaultTTL(){
        return $this->defaultTTL;
    }

    /**
     * @param int $defaultTTL
     */
    public function setDefaultTTL($defaultTTL){
        $this->defaultTTL = (int) $defaultTTL;
    }

    protected function encode($data) {
        return serialize($data);
    }

    protected function decode($value) {
        $x = @unserialize($value);
        if($x == false) {
            return $value;
        } else {
            return $x;
        }
    }

    protected function readfile($file) {
        return file_get_contents($file);
    }

    protected function setup(array $config){
        $this->config = $config;
    }
} 