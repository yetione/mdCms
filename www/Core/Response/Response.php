<?php
namespace Core\Response;


use Core\Core;

abstract class Response {

    /**
     * @var string
     */
    protected $format;

    /**
     * @var ResponseBlock[]
     */
    protected $blocks = array();

    const FORMAT_HTML = 'HTML';
    const FORMAT_JSON = 'JSON';
    const FORMAT_PDF = 'PDF';

    protected $supportFormats = array('HTML', 'PLAIN', 'JSON', 'XML');

    protected $referer = null;


    const RESPONSE_FULL = 10;
    const RESPONSE_PLAIN = 11;
    const RESPONSE_JSON = 12;
    const RESPONSE_XML = 13;

    const REFERER_HOST = 'host';
    const REFERER_SCHEME = 'scheme';
    const REFERER_PORT = 'port';
    const REFERER_USER = 'user';
    const REFERER_PASS = 'pass';
    const REFERER_PATH = 'path';
    const REFERER_QUERY = 'query';
    const REFERER_FRAGMENT = 'fragment';

    protected $resultCode = 200;

    /**
     * @var Core
     */
    protected $core;


    public function setCore(Core $core){
        $this->core = $core;
    }

    public function getReferer(){
        if (is_null($this->referer)){
            $this->referer = parse_url($_SERVER['HTTP_REFERER']);
            $this->referer = array_merge(array('scheme'=>'','host'=>'','port'=>'','user'=>'','pass'=>'','path'=>'','query'=>'','fragment'=>''),$this->referer);
            if (isset($this->referer['query'])){
                $query = array();
                parse_str($this->referer['query'], $query);
                $this->referer['query'] = $query;
            }
            //$this->referer = substr($_SERVER['HTTP_REFERER'], strpos($_SERVER['HTTP_REFERER'], $_SERVER['SERVER_NAME'])+strlen($_SERVER['SERVER_NAME']));
        }
        return $this->referer;
    }

    /**
     * @param array $query
     * @param null $fragment
     * @param bool $toMainIfNotFromSelf
     */
    public function back($query=array(), $fragment=null, $toMainIfNotFromSelf=true){
        if ($this->getReferer()['host'] == $_SERVER['SERVER_NAME']){
            $data = $this->getReferer();
            $data['query'] = http_build_query(array_merge($data['query'], $query), '', ini_get('arg_separator.output'), PHP_QUERY_RFC3986);
            $result = $this->buildUrl($data);
            $this->redirect($result);
        }elseif ($toMainIfNotFromSelf){
            $this->redirect(BASE_URL);
        }else{
            throw new \RuntimeException('Response: Cant go back, because refer is not a self host');
        }
    }

    public function currentUrl($newParams=array()){
        $newParams = array_merge($_GET, $newParams);
        $queryStr = http_build_query($newParams, '', ini_get('arg_separator.output'), PHP_QUERY_RFC3986);
        return $_REQUEST['_request'].'?'.$queryStr;

    }

    public function setRefererPart($part, $value){
        if (isset($this->getReferer()[$part])){
            $this->referer[$part] = $value;
        }
    }

    public function getRefererPart($part, $default=null){
        return isset($this->getReferer()[$part]) ? $this->referer[$part] : $default;
    }

    public function addToQueryString(array $data, $mergeWithExists=false){
        if ($this->getReferer()['host'] == $_SERVER['SERVER_NAME'] && $mergeWithExists){
            $this->setRefererPart(self::REFERER_QUERY, array_merge($this->getRefererPart(self::REFERER_QUERY), $data));
        }else{
            $this->setRefererPart(self::REFERER_QUERY, $data);
        }
    }

    protected function buildUrl($data){
        $scheme   = isset($data['scheme']) && !empty($data['scheme']) ? $data['scheme'] . '://' : '';
        $host     = isset($data['host']) && !empty($data['host']) ? $data['host'] : '';
        $port     = isset($data['port']) && !empty($data['port']) ? ':' . $data['port'] : '';
        $user     = isset($data['user']) && !empty($data['user']) ? $data['user'] : '';
        $pass     = isset($data['pass']) && !empty($data['pass']) ? ':' . $data['pass']  : '';
        $pass     = ($user || $pass) ? "$pass@" : '';
        $path     = isset($data['path']) && !empty($data['path']) ? $data['path'] : '';
        $query    = isset($data['query']) && !empty($data['query']) ? '?' . $data['query'] : '';
        $fragment = isset($data['fragment']) && !empty($data['fragment']) ? '#' . $data['fragment'] : '';
        return "$scheme$user$pass$host$port$path$query$fragment";
    }

    public function getCore(){
        return $this->core;
    }

    public function setResultCode($code){
        return http_response_code($code);
    }

    public function createBlock($name, $template){
        $name = strval($name);
        if (!isset($this->blocks[$name])){
            $this->blocks[$name] = new ResponseBlock($template, $this->core);
            return $this->blocks[$name];
        }
        return false;
    }

    public function getBlock($name){
        return isset($this->blocks[$name]) ? $this->blocks[$name] : false;
    }

    public function setBlock($name, ResponseBlock $block){
        $this->blocks[$name] = $block;
    }

    public function getFormat(){
        return $this->format;
    }

    public function redirect($url, $queryString='', $fragment=''){
        header('Location: '.$url);
        exit();
    }

    public function error($code=500, $message=''){
        $this->setResultCode($code);
        $this->onError($code, $message);
    }

    abstract protected function onError($code, $message);

    /**
     * @return string
     */
    abstract public function render();
} 