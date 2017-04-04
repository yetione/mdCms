<?php
namespace Core\DataBase;


use Core\DataBase\Exception\RelationshipUnknownType;
use Core\DataBase\Model\Relationship;

class DataFetcher {

    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var array
     */
    protected $loadedEntities = array();


    public function __construct(EntityManager $em){
        $this->entityManager = $em;
    }

    public function fetch($parentEntity, array $data){
        $result = array();
        $parentMetadata = $this->entityManager->getEntityMetadata($parentEntity);
        foreach ($data as $row){
            $rowEntities = array();
            foreach ($row as $colName=>$value){
                list($entName, $fieldName) = explode('_', $colName, 2);
                if (!isset($rowEntities[$entName])){
                    $rowEntities[$entName] = array();
                }
                $rowEntities[$entName][$fieldName] = $value;
            }
            $parentEntityObj = $this->addEntity($parentEntity, $rowEntities[$parentEntity]);
            unset($rowEntities[$parentEntity]);
            //$objs = array();
            foreach ($rowEntities as $entName=>$data){
                $relation = $parentMetadata->getRelationshipWithEntity($entName);
                $obj = $this->addEntity($entName, $data);
                switch ($relation->getType()){
                    case Relationship::ONE_ONE:
                    case Relationship::MANY_ONE:
                        $methodName = 'set'.$relation->getName();
                        break;
                    case Relationship::ONE_MANY:
                    case Relationship::MANY_MANY:
                        $methodName = 'add'.$relation->getName();
                        break;
                    default:
                        throw new RelationshipUnknownType(__CLASS__.': Unknown relationship type: '.$relation->getType());
                }

                $parentEntityObj->$methodName($obj);

            }
            $result[] = $parentEntityObj;
        }
        return $result;
    }


    public function addEntity($entName, array $data){

        if (!isset($this->loadedEntities[$entName])){
            $this->loadedEntities[$entName] = array();
        }
        if (!isset($this->loadedEntities[$entName][$data['Id']])){
            $entityObj = $this->entityManager->getEntity($entName);
            $entityObj->fromArray($data);
            $entityObj->setIsNew(false);
            $this->loadedEntities[$entName][$data['Id']] = $entityObj;

        }
        return $this->loadedEntities[$entName][$data['Id']];
    }


} 