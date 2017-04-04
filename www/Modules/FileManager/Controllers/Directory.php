<?php
namespace Modules\FileManager\Controllers;


use Core\Module\Base\Controller;
use Modules\FileManager\FileManager;

class Directory extends Controller{

    /**
     * @var FileManager
     */
    protected $module;

    public function getDirectory(array $data){
        $input = $this->getInput();
        $directory = trim($input->get('Directory', null, TYPE_STRING));
        if (is_null($directory)){
            $this->module->view('Error')->render(['code'=>1, 'message'=>'Директория не передана.']);
            return;
        }
        $a = new \Modules\FileManager\Classes\Directory($this->module->getFullPath());
        var_dump($a->getChildren(\Modules\FileManager\Classes\Directory::TYPE_FILE)[0]->getObjectName());

    }
}