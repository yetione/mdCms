<?php
namespace Modules\Kernel;


use Core\DataBase\Exception\StatementExecuteError;
use Core\DataBase\Model\EntityConstructor;
use Core\Debugger;
use Core\Event\EventVar;
use Core\Module\Base\Module;

class Kernel extends Module{

    protected $moduleName = 'Kernel';

    const ENTITY_GET = 'get';
    const ENTITY_DELETE = 'delete';
    const ENTITY_UPDATE = 'update';


    protected function init(array $configs){
        $this->core->getEventManager()->hook('Application.DatabaseBuild', array($this, 'onDatabaseBuild'));
    }

    public function onDatabaseBuild(EventVar $ev){
        /**
         * @var EntityConstructor[] $entities
         */
        $entities = $ev->get('entities', []);
        $em = $this->getCore()->getEntityManager();
        $entDataQuery = $em->getEntityQuery('EntityData');
        foreach ($entities as $ent){
            $metadata = $em->getEntityMetadata($ent->getName());
            $entData = $em->getEntity('EntityData');
            $entData->setName($metadata->getName());
            $entData->setTableName($metadata->getTableName());
            $entData->setFields(json_encode($metadata->getFieldsMapping()));
            $entData->setRelationships(json_encode($metadata->getRelationships()));
            $entData->setDescription($metadata->getDescription());
            try{
                $entDataQuery->save($entData, true);
                foreach ($metadata->getAccessFlags() as $action=>$flags){
                    foreach ($flags as $flag){
                        $this->addAccessFlagToEntity($metadata->getName(), $flag, $action);
                    }
                }
            }catch (StatementExecuteError $e){
                Debugger::log('Kernel::onDatabaseBuild: Error: '.implode(', ', $e->getErrorData()));
            }
        }
    }

    /**
     * @param string $entityName
     * @param string $flag
     * @param string $action
     * @return bool|\Core\DataBase\Model\Entity
     */
    public function addAccessFlagToEntity($entityName, $flag, $action){
        if (!$this->getCore()->getEntityManager()->getEntityMetadata($entityName)){
            Debugger::log('Kernel::addAccessFlagToEntity: Cant find entity: '.$entityName);
            return false;
        }
        try{
            $flagEnt = $this->getCore()->getEntityManager()->getEntityQuery('AccessFlag')->findByFlag($flag)->loadOne(false, true);
            $entityEnt = $this->getCore()->getEntityManager()->getEntityQuery('EntityData')->findByName($entityName)->loadOne(false, true);
        }catch (StatementExecuteError $e){
            Debugger::log('Kernel::addAccessFlagToEntity: Cant find entities:'.$flag.' or '.$entityName.'. Error: '.implode(', ', $e->getErrorData()));
            return false;
        }
        $entityFlag = $this->getCore()->getEntityManager()->getEntity('EntityAccessFlag');
        $entityFlag->setEntityId($entityEnt->getId())->setFlagId($flagEnt->getId())->setAction($action)->setIsNew(true);
        try{
            $this->getCore()->getEntityManager()->getEntityQuery('EntityAccessFlag')->save($entityFlag, true);
            return $entityFlag;
        }catch (StatementExecuteError $e){
            Debugger::log('Kernel::addAccessFlagToEntity: Cant add flag: '.$flag.' to entity: '.$entityName.'. '. 'Error: '.implode(', ', $e->getErrorData()));
            return false;
        }
    }
} 