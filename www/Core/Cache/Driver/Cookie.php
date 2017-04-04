<?php
namespace Core\Cache\Driver;


class Cookie extends AbstractDriver{

    protected $prefix = 'qscookiecache_';

    public function __construct($config = array()){
        $this->setup($config);
        if(!$this->checkdriver() && !isset($config['skipError'])) {
            $this->fallback = true;
        }
    }

    public function checkdriver(){
        if(function_exists("setcookie")) {
            return true;
        }
        $this->fallback = true;
        return false;
    }

    public function driver_set($keyword, $value = "", $time = 300, $option = array()){
        $keyword = $this->prefix.$keyword;
        return @setcookie($keyword, $this->encode($value), $time, "/");
    }

    public function driver_get($keyword, $option = array()){
        $keyword = $this->prefix.$keyword;
        $x = isset($_COOKIE[$keyword]) ? $this->decode($_COOKIE[$keyword]) : false;
        if($x == false) {
            return null;
        } else {
            return $x;
        }
    }

    public function driver_stats($option = array()){
        $res = array(
            "info"  => "",
            "size"  =>  "",
            "data"  => $_COOKIE
        );
        return $res;
    }

    public function driver_delete($keyword, $option = array()){
        $keyword = $this->prefix.$keyword;
        @setcookie($keyword,null,-10);
        $_COOKIE[$keyword] = null;
    }

    public function driver_clean($option = array()){
        foreach($_COOKIE as $keyword=>$value) {
            if(strpos($keyword, $this->prefix) !== false) {
                @setcookie($keyword,null,-10);
                $_COOKIE[$keyword] = null;
            }
        }
    }

    public function driver_isExisting($keyword) {
        $x = $this->driver_get($keyword);
        if($x == null) {
            return false;
        } else {
            return true;
        }
    }

    public static function __set_state(array $data)
    {
        return new self($data['config']);
    }
}