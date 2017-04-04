<?php
namespace Core\Module\Base;


use Core\Event\EventVar;
use Core\Module\Exception\NotFound;
use Core\Module\ModuleManager;
use Core\Response\Response;

class Module {

    protected $moduleName;

    /**
     * @var ModuleManager
     */
    protected $manager;

    /**
     * @var \Core\Response\Response
     */
    protected $response;

    /**
     * @var \Core\Core
     */
    protected $core;

    protected $controllers = array();
    protected $models = array();
    protected $views = array();

    /**
     * @param array $configs
     * @param array $dependency
     * @param ModuleManager $manager
     * @throws NotFound
     * @throws \Core\Module\Exception\DisabledModule
     * @throws \Core\Module\Exception\InvalidModule
     * @internal param \Core\Input $
     */
    final public function __construct(array $configs=array(), array $dependency=array(), ModuleManager $manager){
        $this->manager = $manager;
        //$this->response = $manager->getModule('Core/Response');
        $this->core = $manager->getModule('Core');

        array_unshift($dependency, $configs);
        call_user_func_array(array($this, 'init'), $dependency);

        $this->core->getEventManager()->hook('Application.buildResponse', array($this, 'onBuildResponse'));
    }

    public function onBuildResponse(EventVar $ev){
        $this->response = $ev->get('response');
        return EVENT_CONTINUE;
    }

    /**
     * @return mixed
     */
    public function getModuleName(){
        return $this->moduleName;
    }

    /**
     * @param \Core\Response\Response $response
     */
    public function setResponse(Response $response){
        $this->response = $response;
    }

    /**
     * @return ModuleManager
     */
    public function getManager(){
        return $this->manager;
    }


    protected function init(array $configs){}

    /**
     * @return \Core\Core
     */
    public function getCore(){
        return $this->core;
    }

    /**
     * Функция распаковки конфигов в свойства объекта
     * @param array $configs
     */
    protected function unpackConfigs(array $configs){
        foreach($configs as $key => $value){
            $this->$key = $value;
        }
    }

    /**
     * Устанавливает значение свойству модуля
     * @param string $option
     * @param mixed $value
     * @return mixed
     */
    public function set($option, $value){
        if (isset($this->$option)){
            $previous = $this->$option;
            $this->$option = $value;
        }
        return isset($previous) ? $previous : null;
    }

    /**
     * Возвращает значение свойства модуля
     * @param string $option
     * @param mixed|null $default
     * @return mixed
     */
    public function get($option, $default=null){
        return isset($this->$option) ? $this->$option : $default;
    }

    /**
     * @param $name
     * @throws NotFound
     * @return Controller
     */
    public function controller($name){
        if (!isset($this->controllers[$name])){
            $class_name = 'Modules\\'.$this->moduleName.'\\Controllers\\'.$name;

            $this->checkClass($class_name);
            $this->controllers[$name] = new $class_name($this);
        }
        return $this->controllers[$name];
    }

    /**
     * @param $name
     * @param bool $new
     * @throws NotFound
     * @return Model
     */
    public function model($name, $new=false){
        if (!isset($this->models[$name]) || $new){
            $class_name = 'Modules\\'.$this->moduleName.'\\Models\\'.$name;
            $this->checkClass($class_name);
            $this->models[$name] = new $class_name($this);
        }
        return $this->models[$name];
    }

    /**
     * @param $name
     * @throws NotFound
     * @return View
     */
    public function view($name){
        if (!isset($this->views[$name])){
            $class_name = 'Modules\\'.$this->moduleName.'\\Views\\'.$name;
            $this->checkClass($class_name);
            $this->views[$name] = new $class_name($this);
        }
        return $this->views[$name];
    }


    /**
     * @param string $className
     * @return bool
     * @throws NotFound
     */
    protected function checkClass($className){

        if (!class_exists($className)){
            throw new NotFound(__CLASS__.': class: '.$className.' not found');
        }
        return true;
    }

    /**
     * @return \Core\Response\Response
     */
    public function getResponse(){
        return $this->response;
    }

    /**
     * @return array
     */
    public function getModuleTabs(){return [];}

} 