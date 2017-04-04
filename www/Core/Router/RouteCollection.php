<?php
namespace Core\Router;


class RouteCollection{
    /**
     * Массив с роутерами
     * @var Route[]
     */
    protected $data = array();

    protected $_hash = array();


    public function __construct(array $data = array()){
        $this->setData($data);
    }

    public function setData(array $data){
        $this->data = $data;
        $this->calcHash();
    }

    public function __sleep(){
        return array('data');
    }

    public function __wakeup(){
        $this->calcHash();
    }

    public function calcHash(){
        $this->_hash = array();
        foreach ($this->data as $route){
            $this->_hash[] = $route->getHash();
        }
    }

    /**
     * Attach a Route to the collection.
     *
     * @param Route $attachObject
     * @return bool
     */
    public function attachRoute(Route $attachObject)
    {
        $object_hash = $attachObject->getHash();
        if (in_array($object_hash, $this->_hash)){
            return false;
        }
        if ($attachObject->isParametric()){
            $this->data[] = $attachObject;
            $this->_hash[] = $object_hash;
        }else{
            array_unshift($this->data, $attachObject);
            array_unshift($this->_hash, $object_hash);
        }
        return true;

    }

    public function remove($name){
        $count = 0;
        foreach ($this->data as $k=> $route){
            if ((!is_object($name) && $route->getName() === $name) || $name == $route){
                $count++;
                unset($this->data[$k]);
                unset($this->_hash[$k]);
            }
        }
        return $count;
    }
    /**
     * Fetch all routers stored on this collection of router
     * and return it.
     *
     * @return Route[]
     */
    public function all()
    {
        return $this->data;
    }
} 