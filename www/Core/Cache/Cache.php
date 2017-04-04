<?php
namespace Core\Cache;


use Core\Cache\Driver\AbstractDriver;
use Core\IExportable;

class Cache implements IExportable {

    protected $configs = array(
        'storage' => 'apc',
        'chmod' => 0777,
        'security_key' => 'auto',
        'htaccess' => true,
        'path' => '',
        'memcache' => array("127.0.0.1",11211,1),
        'redis' => array(
            'host' => '',
            'port' => '',
            'password' => '',
            'database' => '',
            'timeout' => ''
        ),
        'extensions' => array(),
        'fallback' => 'files',
        'autoload_driver' => true
    );

    protected $temp = array();

    /**
     * @var AbstractDriver
     */
    protected $_driver;

    public function __construct($configs=array()){
        $this->configs = array_merge($this->configs, $configs);
        $driver = $this->configs['storage'] == '' || $this->configs['storage'] == 'auto'? $this->getAutoClass() : $this->configs['storage'];
        if (($this->configs['path'] == '' || $this->configs['path'] == 'auto') && $driver == 'file'){
            //Только если используется файловый кэш, нужно высчитать путь.
            $this->configs['path'] = $this->getPath();
        }
        if ($this->configs['autoload_driver']){
            $this->loadDriver($driver);
        }

    }

    public function loadDriver($driver = null){
        if (is_null($driver)){
            $driver = $this->configs['storage'] == '' || $this->configs['storage'] == 'auto'? $this->getAutoClass() : $this->configs['storage'];
        }
        $this->_loadDriver($driver);
    }

    public function setDriver(AbstractDriver $driver){
        $this->_driver = $driver;
    }

    public function setTemp(array $temp){
        $this->temp = $temp;
    }

    public function setConfigs(array $configs, $merge=true){
        $this->configs = $merge ? array_merge($this->configs, $configs) : $configs;
    }

    protected function _loadDriver($driver){
        if ($this->isExistingDriver($driver)){
            $class_name = '\\Core\\Cache\\Driver\\'.ucfirst(strtolower($driver));
            $this->_driver = new $class_name($this->configs);
        }else{
            $class_name = '\\Core\\Cache\\Driver\\'.ucfirst($this->getAutoClass());
            $this->_driver = new $class_name($this->configs);
        }
        return true;

    }

    public function set($keyword, $value = "", $time = 0, $option = array() ) {
        if((Int)$time <= 0) {
            $time = 3600*24*365*5;
        }
        $object = array(
            "value" => $value,
            "write_time"  => @date("U"),
            "expired_in"  => $time,
            "expired_time"  => @date("U") + (Int)$time,
        );
        return $this->_driver->driver_set($keyword,$object,$time,$option);
    }

    public function get($keyword, $option = array()) {
        $object = $this->_driver->driver_get($keyword,$option);
        if($object == null) {
            return null;
        }
        return isset($option['all_keys']) && $option['all_keys'] ? $object : $object['value'];
    }

    public function getInfo($keyword, $option = array()) {
        $object = $this->_driver->driver_get($keyword,$option);
        if($object == null) {
            return null;
        }
        return $object;
    }

    public function delete($keyword, $option = array()) {
        return $this->_driver->driver_delete($keyword,$option);
    }

    public function stats($option = array()) {
        return $this->_driver->driver_stats($option);
    }

    public function clean($option = array()) {
        return $this->_driver->driver_clean($option);
    }

    public function isExisting($keyword) {
        if(method_exists($this->_driver,"driver_isExisting")) {
            return $this->_driver->driver_isExisting($keyword);
        }
        $data = $this->get($keyword);
        if($data == null) {
            return false;
        } else {
            return true;
        }
    }

    public function search($query) {
        if(method_exists($this->_driver,"driver_search")) {
            return $this->_driver->driver_search($query);
        }
        throw new \Exception('Search method is not supported by this driver.');
    }

    public function increment($keyword, $step = 1 , $option = array()) {
        $object = $this->get($keyword, array('all_keys' => true));
        if($object == null) {
            return false;
        } else {
            $value = (Int)$object['value'] + (Int)$step;
            $time = $object['expired_time'] - @date("U");
            $this->set($keyword,$value, $time, $option);
            return true;
        }
    }

    public function decrement($keyword, $step = 1 , $option = array()) {
        $object = $this->get($keyword, array('all_keys' => true));
        if($object == null) {
            return false;
        } else {
            $value = (Int)$object['value'] - (Int)$step;
            $time = $object['expired_time'] - @date("U");
            $this->set($keyword,$value, $time, $option);
            return true;
        }
    }

    public function touch($keyword, $time = 300, $option = array()) {
        $object = $this->get($keyword, array('all_keys' => true));
        if($object == null) {
            return false;
        } else {
            $value = $object['value'];
            $time = $object['expired_time'] - @date("U") + $time;
            $this->set($keyword, $value,$time, $option);
            return true;
        }
    }

    public function setMulti($list = array()) {
        foreach($list as $array) {
            $this->set($array[0], isset($array[1]) ? $array[1] : 0, isset($array[2]) ? $array[2] : array());
        }
    }

    public function getMulti($list = array()) {
        $res = array();
        foreach($list as $array) {
            $name = $array[0];
            $res[$name] = $this->get($name, isset($array[1]) ? $array[1] : array());
        }
        return $res;
    }

    public function getInfoMulti($list = array()) {
        $res = array();
        foreach($list as $array) {
            $name = $array[0];
            $res[$name] = $this->getInfo($name, isset($array[1]) ? $array[1] : array());
        }
        return $res;
    }

    public function deleteMulti($list = array()) {
        foreach($list as $array) {
            $this->delete($array[0], isset($array[1]) ? $array[1] : array());
        }
    }

    public function isExistingMulti($list = array()) {
        $res = array();
        foreach($list as $array) {
            $name = $array[0];
            $res[$name] = $this->isExisting($name);
        }
        return $res;
    }

    public function incrementMulti($list = array()) {
        $res = array();
        foreach($list as $array) {
            $name = $array[0];
            $res[$name] = $this->increment($name, $array[1], isset($array[2]) ? $array[2] : array());
        }
        return $res;
    }

    public function decrementMulti($list = array()) {
        $res = array();
        foreach($list as $array) {
            $name = $array[0];
            $res[$name] = $this->decrement($name, $array[1], isset($array[2]) ? $array[2] : array());
        }
        return $res;
    }

    public function touchMulti($list = array()) {
        $res = array();
        foreach($list as $array) {
            $name = $array[0];
            $res[$name] = $this->touch($name, $array[1], isset($array[2]) ? $array[2] : array());
        }
        return $res;
    }

    protected function isExistingDriver($type) {
        return class_exists('\\Core\\Cache\\Driver\\'.ucfirst(strtolower($type)));
    }

    public function getAutoClass() {
        $path = $this->getPath(false);
        if(is_writeable($path)) {
            $driver = "file";
        }else if(extension_loaded('apc') && ini_get('apc.enabled') && strpos(PHP_SAPI,"CGI") === false) {
            $driver = "apc";
        }else if(class_exists("memcached")) {
            $driver = "memcached";
        }elseif(extension_loaded('wincache') && function_exists("wincache_ucache_set")) {
            $driver = "wincache";
        }elseif(extension_loaded('xcache') && function_exists("xcache_get")) {
            $driver = "xcache";
        }else if(function_exists("memcache_connect")) {
            $driver = "memcache";
        }else if(class_exists("Redis")) {
            $driver = "redis";
        }else {
            $driver = "file";
        }
        return $driver;
    }

    public function getPath($skip_create_path = false, $path_is_tmp_dir=false) {
        $securityKey = $this->securityKey();
        if ($path_is_tmp_dir){
            $path = ini_get('upload_tmp_dir') ? ini_get('upload_tmp_dir') : sys_get_temp_dir();
            $path .= DIRECTORY_SEPARATOR . $securityKey . DIRECTORY_SEPARATOR;
        }
        elseif ($this->configs['path'] == '' || $this->configs['path'] == 'auto'){
            $pathDirs = array('_cache', $securityKey);
            $path = QS_path($pathDirs, true);
            if(!file_exists($path)){
                $elementCount = count($pathDirs);
                $checkedPath = array();
                for($i=0;$i<$elementCount;$i++){
                    $checkedPath[] = array_shift($pathDirs);
                    $tmp = QS_path($checkedPath, false);
                    if (true || !file_exists($tmp)){
                        var_dump($tmp, $this->_chmod());
                        $e = mkdir($tmp, $this->_chmod(), true);
                        var_dump($e);
                        $e = chmod($tmp, $this->_chmod());
                        var_dump($e);
                    }
                }
            }

        } else {
            $path = rtrim($this->configs['path'], DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
        }

        $pathx = md5($path);
        if($skip_create_path  == false && !isset($this->temp[$pathx])) {

            if(!file_exists($path) || !is_writable($path)) {
                if(!file_exists($path)) {
                    mkdir($path,$this->_chmod(),true);
                }
                if(!is_writable($path)) {
                    chmod($path,$this->_chmod(), true);
                }
                if(!file_exists($path) || !is_writable($path)) {
                    die("Sorry, Please create ".$path." and SET Mode 0777 or any Writable Permission!");
                }
            }
            $this->temp[$pathx] = true;
            $this->buildHtaccess($path, $this->configs['htaccess']);
        }
        return $path;
    }

    protected function securityKey($key=null){
        $securityKey = $this->configs['security_key'];
        if (is_null($key)){
            if($securityKey == "" || $securityKey == "auto"){
                $securityKey = isset($_SERVER['HTTP_HOST']) ? ltrim(strtolower($_SERVER['HTTP_HOST']),"www.") : "default";
                $securityKey = preg_replace("/[^a-zA-Z0-9]+/","",$securityKey);
                $this->configs['security_key'] = md5($securityKey);
            }
            return $securityKey;
        }else{
            $this->configs['security_key'] = $key;
            return $securityKey;
        }
    }

    protected function getOS() {
        $os = array(
            "os" => PHP_OS,
            "php" => PHP_SAPI,
            "system"    => php_uname(),
            "unique"    => md5(php_uname().PHP_OS.PHP_SAPI)
        );
        return $os;
    }

    public function isPHPModule() {
        if(PHP_SAPI == "apache2handler") {
            return true;
        } else {
            if(strpos(PHP_SAPI,"handler") !== false) {
                return true;
            }
        }
        return false;
    }

    public function _chmod($value=null){
        $current = $this->configs['chmod'] == "" || is_null($this->configs['chmod']) ? 0777 : $this->configs['chmod'];
        if (!is_null($value)){
            $this->configs['chmod'] = $value;
        }
        return $current;
    }

    protected function buildHtaccess($path, $create = true) {
        if($create == true) {
            if(!is_writeable($path)) {
                try {
                    chmod($path,0777);
                }
                catch(\Exception $e) {
                    die("FOR CREATING .htaccess NEED WRITEABLE ".$path);
                }
            }
            if(!file_exists($path.".htaccess")) {
                $html = "order deny, allow \r\ndeny from all \r\nallow from 127.0.0.1";
                $f = @fopen($path.".htaccess","w+");
                if(!$f) {
                    die(" CANT CREATE .htaccess TO PROTECT FOLDER - PLZ CHMOD 0777 FOR ".$path);
                }
                fwrite($f,$html);
                fclose($f);
            }
        }
    }

    public function setup($name,$value = "") {
        if(is_array($name)) {
            $this->configs = $name;
        } else {
            $this->configs[$name] = $value;
        }
    }

    public function storage($new=null){
        $current = $this->configs['storage'];
        if (!is_null($new)){
            $this->configs['storage'] = $new;
            $this->loadDriver($this->configs['storage']);
        }
        return $current;

    }

    public static function __set_state(array $array){
        $array['configs']['autoload_driver'] = false;
        $a = new self($array['configs']);
        $a->setDriver($array['_driver']);
        $a->setTemp($array['temp']);
        return $a;
    }
}