<?php
namespace Modules\FileManager\Classes;


abstract class FSObject{

    /**
     * @var string
     */
    protected $objectName;

    /**
     * @var string
     */
    protected $path;

    /**
     * @param string $path
     */
    public function setPath($path){
        $this->objectName = substr($path, strrpos($path, DIRECTORY_SEPARATOR)+1);
        $this->path = $path;
    }

    public function getRelativePath($baseDir){
        return substr($this->getPath(), strpos($this->getPath(), $baseDir));
    }

    /**
     * @return string
     */
    public function getPath(){
        return $this->path;
    }

    /**
     * @return string
     */
    public function getObjectName(){
        return $this->objectName;
    }
}