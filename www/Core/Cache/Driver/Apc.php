<?php
namespace Core\Cache\Driver;


use Core\Cache\Exception\CacheException;

class Apc extends  AbstractDriver {

    protected $keyPrefix = 'QSCache.';

    protected $defragmentationKey = 'QSCacheStg.defragmentation';

    protected $checkPeriod = 1800;

    protected $cacheKeysToApc = array(
        self::KEY => 'key',
        self::VALUE => 'value',
        self::CREATE_TIME => 'ctime',
        self::LIFE_TIME => 'ttl',
        self::ACCESS_TIME => 'atime',
        self::TTL => '_ttl'

    );

    public function __construct($config = array())
    {
        $this->setup($config);
        if(!$this->checkdriver() && !isset($config['skipError'])) {
            $this->fallback = true;
        }
    }

    /**
     * @param string $key
     * @param mixed $value
     * @param int|null $ttl
     * @param bool $override
     * @return array|bool
     */
    public function set($key, $value, $ttl=null, $override=true){
        static $cleaned = false;
        if (!$cleaned) {
            $this->checkToClean();
            $cleaned = true;
        }
        $ttl = is_null($ttl) || (int) $ttl < 0 ? $this->getDefaultTTL() : (int) $ttl;
        if (!$override){
            return apc_add($this->keyPrefix.$key, $value, $ttl);
        }
        return apc_store($this->keyPrefix.$key, $value, $ttl);
    }

    /**
     * @param string $key
     * @param string|array|null $options
     * @return array
     * @throws CacheException
     */
    public function get($key, $options=self::VALUE){
        if ($options === self::VALUE){
            $success = false;
            $result = apc_fetch($this->keyPrefix.$key, $success);
            if (!$success){
                if (!apc_exists($this->keyPrefix.$key)){
                    return $this->onKeyNotFoundAction($key);
                }
                return $this->onKeyExpireAction($key);
            }
            return $result;
        }
        $format = 0;

        if (is_null($options)){
            $options = array(self::KEY, self::VALUE, self::CREATE_TIME, self::ACCESS_TIME, self::LIFE_TIME, self::TTL);
        }elseif (is_string($options)){
            $options = array($options);
        }elseif (!is_array($options)){
            throw new CacheException(__CLASS__.': cant get key: '.$key.' with options: '.$options);
        }
        if (in_array(self::KEY, $options)){
            $format += APC_ITER_KEY;
        }
        if (in_array(self::VALUE, $options)){
            $format += APC_ITER_VALUE;
        }
        if (in_array(self::CREATE_TIME, $options) || in_array(self::TTL, $options)){
            $format += APC_ITER_CTIME;
        }
        if (in_array(self::ACCESS_TIME, $options) || in_array(self::TTL, $options)){
            $format += APC_ITER_ATIME;
        }
        if (in_array(self::LIFE_TIME, $options) || in_array(self::TTL, $options)){
            $format += APC_ITER_TTL;
        }
        if ($format == 0){
            throw new CacheException(__CLASS__.'Cant build key: '.$key.' options');
        }
        $item = new \APCIterator('user', '/^'.preg_quote($this->keyPrefix.$key).'$/', $format, 1);
        $item = $item->current();
        if (empty($item)){
            return $this->onKeyNotFoundAction($key);
        }

        $ttl = $item[$this->cacheKeysToApc[self::LIFE_TIME]] == 0 ? 0 :
            ($item[$this->cacheKeysToApc[self::CREATE_TIME]] + $item[$this->cacheKeysToApc[self::LIFE_TIME]]) - time();
        $item[$this->cacheKeysToApc[self::TTL]] = $ttl;
        $item[$this->cacheKeysToApc[self::KEY]] = substr($item[$this->cacheKeysToApc[self::KEY]], strlen($this->keyPrefix));
        $result = array();
        foreach ($options as $option){
            $result[$option] = $item[$this->cacheKeysToApc[$option]];
        }
        return count($options) == 1 ? $result[$options[0]] : $result;
    }

    /**
     * @return array
     */
    public function getKeys(){
        $iter = new \APCIterator('user', '/^'.preg_quote($this->keyPrefix).'/', APC_ITER_KEY);
        $l = strlen($this->keyPrefix);
        $result = array();
        foreach ($iter as $item) {
            $result[] = substr($item[$this->cacheKeysToApc[self::KEY]], $l);
        }
        return $result;
    }

    /**\
     * @param $key
     * @return bool|\string[]
     */
    public function remove($key){
        return apc_delete($this->keyPrefix.$key);
    }

    /**
     * @return bool
     */
    public function clean(){
        return apc_clear_cache('user');
    }

    /**
     * @return bool
     */
    protected function checkToClean(){
        $curTime = time();
        $ittl              = new \APCIterator('user', '/^'.preg_quote($this->defragmentationKey).'$/', APC_ITER_ATIME, 1);
        $cttl              = $ittl->current();
        $previous_cleaning = $cttl[$this->cacheKeysToApc[self::ACCESS_TIME]];
        if (empty($previous_cleaning) || ($curTime-$previous_cleaning) > $this->checkPeriod) {
            apc_store($this->defragmentationKey, $curTime, $this->checkPeriod);
            $this->removeOld();
        }
        return true;
    }

    public function removeOld(){
        $curTime = time();
        $toDel = array();
        $apc_user_info = apc_cache_info('user', true);
        $apcTTL       = 0;
        if (!empty($apc_user_info['ttl'])) {
            $apcTTL = $apc_user_info['ttl']/2;
        }
        $i = new \APCIterator('user', null, APC_ITER_TTL+APC_ITER_KEY+APC_ITER_CTIME+APC_ITER_ATIME);
        foreach ($i as $key) {
            if ($key[$this->cacheKeysToApc[self::LIFE_TIME]] > 0 && ($curTime-$key[$this->cacheKeysToApc[self::CREATE_TIME]]) > $key[$this->cacheKeysToApc[self::LIFE_TIME]]) $toDel[] = $key[$this->cacheKeysToApc[self::KEY]];
            else {
                //this code is necessary to prevent deletion variables from cache by apc.ttl (they deletes not by their ttl+ctime, but apc.ttl+atime)
                if ($apcTTL > 0 && (($curTime-$key[$this->cacheKeysToApc[self::ACCESS_TIME]]) > $apcTTL)) apc_fetch($key[$this->cacheKeysToApc[self::KEY]]);
            }
        }
        if (!empty($toDel)) {
            $r = apc_delete($toDel);
            if (!empty($r)) return $r;
            else return true;
        }
        return true;

    }

    public function checkdriver()
    {
        if(extension_loaded('apc') && ini_get('apc.enabled')) {
            return true;
        } else {
            $this->fallback = true;
            return false;
        }

    }

    public function driver_set($keyword, $value = "", $time = 300, $option = array())
    {
        if(isset($option['skipExisting']) && $option['skipExisting'] == true) {
            return apc_add($keyword,$value,$time);
        } else {
            return apc_store($keyword,$value,$time);
        }
    }

    public function driver_get($keyword, $option = array())
    {
        $data = apc_fetch($keyword,$bo);
        if($bo === false) {
            return null;
        }
        return $data;
    }

    public function driver_stats($option = array())
    {
        $res = array(
            "info" => "",
            "size"  => "",
            "data"  =>  "",
        );
        try {
            $res['data'] = apc_cache_info("user");
        } catch(\Exception $e) {
            $res['data'] =  array();
        }
        return $res;
    }

    public function driver_delete($keyword, $option = array())
    {
        return apc_delete($keyword);
    }

    public function driver_clean($option = array())
    {
        @apc_clear_cache();
        @apc_clear_cache("user");
    }

    public function driver_isExisting($keyword) {
        if(apc_exists($keyword)) {
            return true;
        } else {
            return false;
        }
    }

    public static function __set_state(array $data)
    {
        return new self($data['config']);
    }
}