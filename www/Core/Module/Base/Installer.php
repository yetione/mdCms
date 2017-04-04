<?php
namespace Core\Module\Base;


use Core\Core;
use Core\Module\ModuleManager;

abstract class Installer{

    /**
     * @var Core
     */
    protected $core;

    /**
     * @var ModuleManager
     */
    protected $moduleManager;

    final public function __construct(Core $core, ModuleManager $moduleManager){
        $this->setCore($core);
        $this->setModuleManager($moduleManager);
    }

    /**
     * @return Core
     */
    public function getCore(){
        return $this->core;
    }

    /**
     * @param Core $core
     */
    public function setCore($core){
        $this->core = $core;
    }

    abstract public function install();

    /**
     * @return ModuleManager
     */
    public function getModuleManager(){
        return $this->moduleManager;
    }

    /**
     * @param ModuleManager $moduleManager
     */
    public function setModuleManager($moduleManager){
        $this->moduleManager = $moduleManager;
    }

}