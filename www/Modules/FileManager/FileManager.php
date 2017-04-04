<?php
namespace Modules\FileManager;


use Core\Module\Base\Module;

class FileManager extends Module{
    /**
     * @var string
     */
    protected $moduleName = 'FileManager';

    /**
     * @var string
     */
    protected $dirName = '';

    protected function init(array $configs){
        $this->setDirName('uploads');
    }

    /**
     * @return string
     */
    public function getFullPath(){
        $args = func_get_args();
        return QS_path(array_merge([$this->getDirName()], $args), false);
    }

    /**
     * @return string
     */
    public function getDirName(){
        return $this->dirName;
    }

    /**
     * @param string $dirName
     */
    public function setDirName($dirName){
        $this->dirName = $dirName;
    }


}