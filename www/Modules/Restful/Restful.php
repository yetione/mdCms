<?php
namespace Modules\Restful;

use Core\Event\EventVar;
use Core\Module\Base\Module;
use Core\Module\Exception\NotFound;

class Restful extends Module{

    protected $moduleName = 'Restful';

    protected function init(array $configs){
        $this->core->getEventManager()->hook('Application.Load', array($this, 'onAppLoad'));
        $this->core->getEventManager()->hook('Application.Close', array($this, 'onAppClose'));
        $em = $this->getCore()->getEntityManager();

        $entsList = $em->getEntitiesList();
        foreach ($entsList as $entity){

            try{
                $this->controller($entity);
            }catch (NotFound $e){
                $this->createEntityController($entity);
            }

        }
    }

    public function onAppClose(EventVar $ev){

    }


    public function onAppLoad(EventVar $ev){


    }

    protected function createEntityController($name){
        $templatePath = QS_path(array('Modules', 'Restful', 'templates', 'entity_controller.txt'), false);
        $controllerPath = QS_path(array('Modules', 'Restful', 'Controllers', $name.'.php'), false);
        $template = file_get_contents($templatePath);

        $namespace  = 'Modules\\Restful\\Controllers';
        $useClasses = array();
        $useSection = implode("\n", array_map(function($item){
            return "use {$item};";
        }, $useClasses));
        $className = $name;
        $extendClass = 'Entity';
        $entityName = $name;
        if (false == file_put_contents($controllerPath, str_replace(
                array(
                    '%NAMESPACE%', '%USE_SECTION%', '%CLASS_NAME%', '%EXTEND_CLASS%', '%ENTITY_NAME%'
                ),
                array(
                    $namespace, $useSection, $className, $extendClass, $entityName),
                $template
            ))){
            return false;
        }
        return true;
    }

} 