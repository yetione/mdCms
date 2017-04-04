<?php
class Autoloader {

    protected $loaded_classes = array();

    public function __construct(){
        spl_autoload_register(array($this,'loader'));
    }

    public function loader($className){
        if (!in_array($className, $this->loaded_classes)){
            $path = $this->getFile($className);
            //$path = explode('\\', $className.'.php');
            if (file_exists($path)){
                require_once $path;
                array_push($this->loaded_classes, $className);
            }

        }
    }

    public function getLoadedClasses(){
        return $this->loaded_classes;
    }

    public function getFile($className){
        $path = explode('\\', $className.'.php');
        return QS_path($path, false);
    }

} 