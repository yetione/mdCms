<?php
namespace Modules\FileManager\Classes;


class Directory extends FSObject{

    protected $data;

    /**
     * @var resource
     */
    protected $handler;

    /**
     * @var array
     */
    protected $children;

    const TYPE_DIR = 'dir';
    const TYPE_FILE = 'file';

    public function __construct($path){
        if (is_dir($path)){
            $this->setPath($path);
            //$this->handler = opendir($path);
        }else{
            throw new \RuntimeException('Cant open directory: '.$path);
        }
    }

    /**
     * @param string $type
     * @return FSObject[]
     */
    public function getChildren($type=self::TYPE_DIR){
        $result = [];
        while (($file = readdir($this->getHandler())) !== false){
            $fP = QS_joinPath($this->getPath(), $file);
            if ($file != '.' && $file != '..' && filetype($fP) == $type){
                if ($type === self::TYPE_DIR){
                    $result[] = new Directory($fP);
                }elseif ($type === self::TYPE_FILE){
                    $result[] = new File($fP);
                }else{
                    throw new \RuntimeException('Type: '.$type.' is no valid.');
                }
            }
        }
        return $result;
    }

    public function __destruct(){
        $this->close();
    }

    public function close(){
        if ($this->handler){
            closedir($this->handler);
        }
    }

    /**
     * @return resource
     */
    public function getHandler(){
        if (!$this->handler){
            $this->handler = opendir($this->getPath());
        }
        return $this->handler;
    }
}