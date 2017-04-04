<?php
namespace Modules\FileManager;


use Core\DataBase\Model\Relationship;
use Core\Router\Route;
use Modules\Kernel\Kernel;

class Installer extends \Core\Module\Base\Installer{

    public function install(){
        $this->createRoutes();
        $this->createEntities();
    }

    protected function createRoutes(){
        $router = $this->getCore()->getRouter();
    }

    protected function createEntities(){
        $builder = $this->getCore()->getEntityManager()->getDatabaseBuilder();
    }

}