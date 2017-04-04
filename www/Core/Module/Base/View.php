<?php
namespace Core\Module\Base;


use Core\DataBase\Model\Collection;
use Core\Module\Exception\UnsupportedRenderMode;

abstract class View {
    /**
     * @var Module
     */
    protected $module;

    /**
     * @var \Core\Response\Response
     */
    protected $response;

    public function __construct(Module $module){
        $this->module = $module;
        $this->response = $module->getResponse();
    }

    final public function render(){
        $methodName = 'render'.$this->response->getFormat();
        if (method_exists($this, $methodName)){
            return call_user_func_array(array($this, $methodName), func_get_args());
        }
        throw new UnsupportedRenderMode(__CLASS__.'unsupported render mode: '.$methodName);
    }

    /**
     * @param \Core\DataBase\Model\Entity[] $items
     * @return array
     */
    protected function entitiesToArray($items){
        if ($items instanceof Collection){
            $items = $items->getData();
        }
        return is_array($items) ? array_map(function($el){
            return $el->toArray();
        }, $items) : [];
    }

    /**
     * @return \Core\Input
     */
    protected function getInput(){
        return $this->module->getCore()->getInput();
    }

} 