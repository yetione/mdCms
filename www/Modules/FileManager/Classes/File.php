<?php
namespace Modules\FileManager\Classes;


class File extends FSObject {

    public function __construct($path){
        if (is_file($path)){
            $this->setPath($path);
        }else{
            throw new \RuntimeException('File: '.$path.' is no a file.');
        }
    }
}