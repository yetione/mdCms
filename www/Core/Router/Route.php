<?php
namespace Core\Router;


class Route {

    /**
     * Строка роутера
     * @var string
     */
    protected $url;

    /**
     * Массив с методами для роутера
     * @var array
     */
    protected $methods = array('GET', 'POST', 'PUT', 'DELETE');

    /**
     * @var mixed
     */
    protected $target;

    /**
     * Имя роутера
     * @var string
     */
    protected $name;

    /**
     *
     * @var array
     */
    protected $filters = array();

    /**
     * Массив с параметрами
     * @var array
     */
    protected $parameters = array();

    protected $hash;

    /**
     * Set named parameters to target method
     * @example [ [0] => [ ["link_id"] => "12312" ] ]
     * @var bool
     */
    protected $parametersByName;

    /**
     * @var array
     */
    protected $config;

    public function __construct($url, array $config=array()){
        $this->url = $url;
        $this->config = $config;
        $this->methods = isset($config['methods']) ? $config['methods'] : array('GET', 'POST', 'PUT', 'DELETE');
        $this->target  = isset($config['target'])  ? $config['target']  : null;

        $this->hash = md5($url.implode(',', $this->methods));
    }

    /**
     * @return string
     */
    public function getUrl(){
        return $this->url;
    }

    public function getHash(){
        return $this->hash;
    }

    /**
     * @return bool
     */
    public function isParametric(){
        return strpos($this->url, ':') !== false;
    }

    /**
     * @param string $url
     */
    public function setUrl($url){
        $url = (string)$url;
        // если URL не заканчивается на слэш, то добавим сами
        if (substr($url, -1) !== '/') {
            $url .= '/';
        }
        $this->url = $url;
        $this->hash = md5($url);
    }

    /**
     * @return mixed
     */
    public function getTarget()
    {
        return $this->target;
    }

    public function getConfig(){
        return $this->config;
    }

    /**
     * @param mixed $target
     */
    public function setTarget($target)
    {
        $this->target = $target;
    }

    /**
     * @return array
     */
    public function getMethods()
    {
        return $this->methods;
    }

    /**
     * @param array $methods
     */
    public function setMethods(array $methods)
    {
        $this->methods = $methods;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = (string)$name;
    }

    /**
     * @param array $filters
     * @param bool $parametersByName
     */
    public function setFilters(array $filters, $parametersByName = false)
    {
        $this->filters = $filters;

        if ($parametersByName) {
            $this->parametersByName = true;
        }
    }

    public function getRegex()
    {
        return preg_replace_callback("/(:\w+)/", array(&$this, 'substituteFilter'), $this->url);
    }
    private function substituteFilter($matches)
    {
        if (isset($matches[1]) && isset($this->filters[$matches[1]])) {
            return $this->filters[$matches[1]];
        }
        return "([\w-%]+)";
    }
    public function getParameters()
    {
        return $this->parameters;
    }

    public function setParameters(array $parameters)
    {
        $this->parameters = $parameters;
    }
}