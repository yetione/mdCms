<?php
namespace Core\Response;


class Layout {

    /**
     * Путь к файлу с макетом
     * @var string
     */
    protected $filePath;

    public function __construct($filePath){
        $this->setFilePath($filePath);
    }


    public function render(){}


    /**
     * @return string
     */
    public function getFilePath(){
        return $this->filePath;
    }

    /**
     * @param string $filePath
     */
    public function setFilePath($filePath){
        $this->filePath = $filePath;
    }
} 