<?php
namespace Core;


class Input {

    protected $headers;

    /**
     * @var array
     */
    public $_json = [];

    public function __construct(){
        $this->headers = apache_request_headers();
        if(isset($_SERVER["CONTENT_TYPE"]) && strpos($_SERVER["CONTENT_TYPE"], "application/json") !== false){
            $this->_json = json_decode(trim(file_get_contents('php://input')), true);
        }

    }


    public function get($var, $default=null, $filter=TYPE_RAW){
        return $this->_get($var,$default,$filter,$_GET);
    }

    public function post($var, $default=null, $filter=TYPE_RAW){
        return $this->_get($var,$default,$filter,$_POST);
    }

    public function request($var, $default=null, $filter=TYPE_RAW){
        return $this->_get($var,$default,$filter,$_REQUEST);
    }

    public function json($var, $default=null, $filter=TYPE_CLEAR){
        return $this->_get($var,$default,$filter,$this->_json);
    }

    public function cookie($var, $default=null, $filter=TYPE_RAW){
        return $this->_get($var,$default,$filter,$_COOKIE);
    }

    public function setCookie($var, $value, $expire = 0, $path = '', $domain = '', $secure = false, $httpOnly = false){
        setcookie($var, $value, $expire, $path, $domain, $secure, $httpOnly);
        $_COOKIE[$var] = $value;
    }

    public function files($var, $default=null){
        return isset($_FILES[$var]) ? $_FILES[$var] : $default;
    }

    public function server($var, $default=null, $filter=TYPE_RAW){
        return $this->_get($var,$default,$filter,$_SERVER);
    }

    public function env($var, $default=null, $filter=TYPE_RAW){
        return $this->_get($var,$default,$filter,$_ENV);
    }

    /**
     * @return null|string
     */
    public function getIp(){
        $keys = array('HTTP_X_FORWARDED_FOR', 'HTTP_CLIENT_IP', 'REMOTE_ADDR', 'HTTP_X_REAL_IP');
        foreach ($keys as $key) {
            $ip = trim(strtok(filter_input(INPUT_SERVER, $key), ','));
            if (!is_null($ip = QS_validate($ip, TYPE_IP_V4, null))) {
                return $ip;
            }
        }
        return null;
    }

    protected function _get($var, $default, $filter, $array){
        if (!isset($array[$var])){
            return $default;
        }elseif ($filter === TYPE_CLEAR){
            return $array[$var];
        }
        return QS_validate($array[$var],$filter, $default);
    }

    public function headers($name, $default=null){
        return isset($this->headers[$name]) ? $this->headers[$name] : $default;
    }

} 