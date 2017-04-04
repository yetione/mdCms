<?php
namespace Core\DataBase;


use Core\DataBase\Exception\StatementExecuteError;
use Core\DataBase\Generator\EntitiesGenerator;
use Core\DataBase\Generator\QueriesGenerator;
use Core\DataBase\Model\EntityConstructor;
use Core\DataBase\Model\EntityMetadata;
use Core\DataBase\Model\Relationship;
use Core\DataBase\Utils\PhpNameGenerator;
use Core\Debugger;

class DatabaseBuilder {

    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var EntityConstructor[]
     */
    protected $entities = array();

    /**
     * @var Relationship[]
     */
    protected $relationships = array();

    /**
     * @var PhpNameGenerator
     */
    protected $nameGenerator;

    /**
     * @var EntitiesGenerator
     */
    protected $entitiesGenerator;

    /**
     * @var QueriesGenerator
     */
    protected $queriesGenerator;

    public function __construct(EntityManager $entityManager){
        $this->entityManager = $entityManager;
        $this->nameGenerator = new PhpNameGenerator();
        $this->entitiesGenerator = new EntitiesGenerator(array(
            'filesDirectory'=>$entityManager->getEntitiesPath(),
            'namespace'=>$entityManager->getEntitiesNamespace(),
        ));
        $this->entitiesGenerator->useClass('\\Core\\DataBase\\Model\\Entity');
        $this->entitiesGenerator->setExtendsClass('Entity');

        $this->queriesGenerator = new QueriesGenerator(array(
            'filesDirectory'=>$entityManager->getQueriesPath(),
            'namespace'=>$entityManager->getQueriesNamespace(),
        ));
        $this->queriesGenerator->useClass('\\Core\\DataBase\\Model\\EntityQuery');
        $this->queriesGenerator->setExtendsClass('EntityQuery');


    }

    /**
     * @param string $tblName
     * @return EntityConstructor
     */
    public function createEntity($tblName){
        $entityConstructor = new EntityConstructor($tblName);
        $this->entities[] = $entityConstructor;
        return $entityConstructor;
    }

    /**
     * @param $name
     * @return Relationship
     */
    public function createRelationship($name){
        $relationship = new Relationship($name);
        $this->relationships[] = $relationship;
        return $relationship;
    }

    /**
     * @return EntityMetadata[]
     */
    public function build(){
        $event = $this->entityManager->getCore()->getEventManager()->event('Application.DatabaseBuild');
        $event->set('start_time', microtime(1));
        $event->preFire();
        if ($event->isHandled()){
            Debugger::log('DatabaseBuilder::build: Application.DatabaseBuild event handled');
            return false;
        }
        foreach($this->entities as $entity){
            $metadata = $this->createMetadata($entity);
            $this->entityManager->addEntityMetadata($metadata);
            $query = $this->entityManager->getEntityQuery($metadata->getName());
            if ($metadata->isTruncateOnCreate()){
                $query->clear();
            }
            foreach ($entity->getValues() as $val){
                $ent = $this->entityManager->getEntity($metadata->getName());
                $ent->fromArray($val);
                $ent->setIsNew(true);
                try{
                    $query->save($ent, true);
                }catch (StatementExecuteError $e){
                    Debugger::log('DatabaseBuilder::build: cant add default value to entity: '.$metadata->getName().'. Error: '.implode(', ', $e->getErrorData()));
                }
            }
        }
        $this->entityManager->save();
        $event->set('end_time', microtime(1))->set('entities', $this->entities);
        $event->postFire();
        return true;
    }

    /**
     * @param EntityConstructor $entity
     * @return EntityMetadata
     */
    protected function createMetadata(EntityConstructor $entity){
        if (is_null($name = $entity->getName())){
            $name = $this->generateName($entity->getTableName());
            $entity->setName($name);
        }
        $fields = $entity->getFields();
        for($i=0;$i<count($fields);$i++){
            $phpName = $fields[$i]['phpName'];
            if (is_null($phpName)){
                $fields[$i]['phpName'] = $this->generateName($fields[$i]['sqlName']);
            }
        }
        $relationships = array();
        foreach($entity->getRelationships() as $relationship){
            $relationshipName = $this->generateName($relationship->getName());
            $relationship->setName($relationshipName);
            $relationships[$relationshipName] = $relationship;
        }
        $metadata = new EntityMetadata($name);
        $metadata->setDescription($entity->getDescription());
        $metadata->setTableName($entity->getTableName());
        $metadata->setFieldsMapping($fields);
        $metadata->setRelationships($relationships);
        $metadata->setAccessFlags($entity->getAccessFlags());
        $metadata->setTruncateOnCreate($entity->isTruncateOnCreate());

        if (is_null($entity->getEntityClass())){
            $metadata->setEntityClassName($this->entityManager->getEntitiesNamespace()."\\{$name}");
            $this->entitiesGenerator->setMetadata($metadata);
            $this->entitiesGenerator->generate();
        }else{
            $metadata->setEntityClassName($entity->getEntityClass());
        }

        if (is_null($entity->getQueryClass())){
            $metadata->setQueryClassName($this->entityManager->getQueriesNamespace()."\\{$name}");
            $this->queriesGenerator->setMetadata($metadata);
            $this->queriesGenerator->generate();
        }else{
            $metadata->setQueryClassName($entity->getQueryClass());
        }

        return $metadata;
    }

    /**
     * @param string $name
     * @return string
     */
    protected function generateName($name){
        return $this->nameGenerator->generateName(array($name, PhpNameGenerator::CONV_METHOD_UNDERSCORE));
    }




} 