<?php
namespace Core\DataBase;


use Core\DataBase\Generator\EntitiesGenerator;
use Core\DataBase\Generator\QueriesGenerator;
use Core\DataBase\Model\Entity;
use Core\DataBase\Model\EntityConstructor;
use Core\DataBase\Model\EntityMetadata;
use Core\DataBase\Model\EntityQuery;
use Core\Core;
use Core\DataBase\Model\Relationship;
use Core\PhpDumper;

class EntityManager {

    const STORE_PHP = 1;
    const STORE_SERIALIZE = 2;

    /**
     * @var EntitiesGenerator
     */
    protected $entityGenerator;

    /**
     * @var QueriesGenerator
     */
    protected $queriesGenerator;

    /**
     * @var string
     */
    protected $entitiesMetadataPath;

    /**
     * @var string
     */
    protected $entityMetadataSerializePath;

    /**
     * @var PhpDumper
     */
    protected $dumper;

    /**
     * @var EntityMetadata[]
     */
    protected $entitiesMetadata = array();

    /**
     * @var bool
     */
    protected $entitiesMetadataChanged;

    /**
     * @var string
     */
    protected $entitiesPath;

    /**
     * @var string
     */
    protected $queriesPath;

    /**
     * @var string
     */
    protected $entitiesNamespace;

    /**
     * @var string
     */
    protected $queriesNamespace;

    protected $databaseBuilder;

    protected $entitiesMetadataStoreMethod;

    /**
     * @var DataFetcher
     */
    protected $dataFetcher;

    /**
     * @var Core
     */
    protected $core;

    /**
     * @var string
     */
    protected $dumperKey = 'EntityManager.entitiesMetadata';

    public function __construct(Core $core, $entitiesMetadataStorageMethod=self::STORE_SERIALIZE){
        $this->entitiesMetadataPath = QS_path(array('_data', 'entities_data'), false, true, true);
        $this->entityMetadataSerializePath = QS_path(array($this->entitiesMetadataPath, 'entities_metadata.dat'), false, false, false);
        $this->dumper = $core->getPhpDumper();
        //$this->dumper = new QSPhpDumper($this->entitiesMetadataPath);
        $this->entitiesPath = QS_path(array('Core', 'DataBase', 'Entities'));
        $this->queriesPath = QS_path(array('Core', 'DataBase', 'Queries'));
        $this->entitiesNamespace = 'Core\\DataBase\\Entities';
        $this->queriesNamespace = 'Core\\DataBase\\Queries';
        $this->core = $core;
        $this->dataFetcher = new DataFetcher($this);

        $this->databaseBuilder = new DatabaseBuilder($this);
        $this->setEntitiesMetadataStoreMethod($entitiesMetadataStorageMethod);
        $this->load();


    }

    public function resetDatabaseBuilder(){
        $this->databaseBuilder = new DatabaseBuilder($this);
    }

    public function load(){
        if ($this->getEntitiesMetadataStoreMethod() === self::STORE_PHP){
            $this->loadEntitiesMetadata();
        }elseif ($this->getEntitiesMetadataStoreMethod() === self::STORE_SERIALIZE){
            $this->unserializeEntitiesMetadata();
        }
    }

    public function __destruct(){
        $this->save();
    }

    public function save(){
        if ($this->getEntitiesMetadataStoreMethod() === self::STORE_PHP){
            $this->saveEntitiesMetadata();
        }elseif ($this->getEntitiesMetadataStoreMethod() === self::STORE_SERIALIZE){
            $this->serializeEntitiesMetadata();
        }
    }

    /**
     * Удаляет инфу о метаданных сущностей
     * @return bool
     */
    public function cleanEntitiesData(){
        $this->entitiesMetadata = array();
        $this->entitiesMetadataChanged = true;
        return $this->dumper->remove($this->dumperKey) && unlink($this->entityMetadataSerializePath);
    }

    /**
     * @return int
     */
    public function getEntitiesMetadataStoreMethod(){
        return $this->entitiesMetadataStoreMethod;
    }

    /**
     * @param int $entitiesMetadataStoreMethod
     */
    public function setEntitiesMetadataStoreMethod($entitiesMetadataStoreMethod){
        if (!in_array($entitiesMetadataStoreMethod, array(self::STORE_PHP, self::STORE_SERIALIZE))){
            throw new \InvalidArgumentException(__CLASS__.': entities metadata storageMethod is not valid');
        }
        $this->entitiesMetadataStoreMethod = $entitiesMetadataStoreMethod;
    }

    protected function loadEntitiesMetadata(){
        /*$meter = new QSMeter('Загрузка метадаты с помощью phpDumper');
        $meter->dir(array('logs','entity_tests', 'load'))->run();*/
        $data = $this->dumper->load($this->dumperKey, array());
        array_walk($data, function(&$item, $key){
            $item->setEntityManager($this);
        });
        $this->entitiesMetadata = $data;
        $this->entitiesMetadataChanged = false;

        /*$meter->msg('Элементов: '.count($this->entitiesMetadata));
        $meter->end();*/
    }

    protected function saveEntitiesMetadata(){
        if($this->entitiesMetadataChanged){
            /*$meter = new QSMeter('Сохранение метадаты с помощью phpDumper. Элементов: '.count($this->entitiesMetadata));
            $meter->dir(array('logs','entity_tests', 'load'))->run();*/

            $this->dumper->save($this->dumperKey, $this->entitiesMetadata);

            //$meter->end();
        }
    }

    protected function unserializeEntitiesMetadata(){
        /*$meter = new QSMeter('Загрузка метадаты с помощью unserialize.');
        $meter->dir(array('logs','entity_tests', 'load'))->run();*/
        $data = unserialize(file_get_contents($this->entityMetadataSerializePath, false));
        if ($data !== false){
            array_walk($data, function(&$item, $key){
                $item->setEntityManager($this);
            });
            $this->entitiesMetadata = $data;

            $this->entitiesMetadataChanged = false;
        }

        /*$meter->msg('Элементов: '.count($this->entitiesMetadata));
        $meter->end();*/
    }

    protected function serializeEntitiesMetadata(){

        if ($this->entitiesMetadataChanged){
            /*$meter = new QSMeter('Сохранение метадаты с помощью serialize. Элементов: '.count($this->entitiesMetadata));
            $meter->dir(array('logs','entity_tests', 'load'))->run();*/

            $sers = serialize($this->entitiesMetadata);
            file_put_contents($this->entityMetadataSerializePath, $sers);

            //$meter->end();
        }

    }



    public function addEntityMetadata(EntityMetadata $metadata, $override=true){
        if (!isset($this->entitiesMetadata[$metadata->getName()]) || $override){
            $event = $this->core->getEventManager()->event('Application.EntityCreated');
            $this->entitiesMetadata[$metadata->getName()] = $metadata;
            $this->entitiesMetadataChanged = true;
            $event->set('metadata', $metadata);
            $event->fire();
        }
    }


    /**
     * @param $name
     * @return Entity
     */
    public function getEntity($name){
        if (is_null($meta = $this->getEntityMetadata($name))){
            throw new \RuntimeException(__CLASS__.': Entity: '.$name.' not found.');
        }
        $className = $meta->getEntityClassName();
        return new $className($meta, $this);
    }

    /**
     * @param $name
     * @return EntityMetadata|null
     */
    public function getEntityMetadata($name){
        if (isset($this->entitiesMetadata[$name])){
            $ent = $this->entitiesMetadata[$name];
            if (is_null($ent->getEntityManager())){
                $ent->setEntityManager($this);
            }
            return $ent;
        }
        return null;
    }

    public function getEntitiesList(){
        return array_keys($this->entitiesMetadata);
    }

    /**
     * @param $name
     * @return EntityQuery
     */
    public function getEntityQuery($name){
        if (is_null($meta = $this->getEntityMetadata($name))){
            throw new \RuntimeException(__CLASS__.': Entity: '.$name.' not found.');
        }
        $className = $meta->getQueryClassName();
        return new $className($meta, $this);
    }

    /**
     * @return string
     */
    public function getEntitiesPath(){
        return $this->entitiesPath;
    }

    /**
     * @param string $entitiesPath
     */
    public function setEntitiesPath($entitiesPath){
        $this->entitiesPath = $entitiesPath;
    }

    /**
     * @return string
     */
    public function getQueriesPath(){
        return $this->queriesPath;
    }

    /**
     * @param string $queriesPath
     */
    public function setQueriesPath($queriesPath){
        $this->queriesPath = $queriesPath;
    }

    /**
     * @return string
     */
    public function getEntitiesNamespace(){
        return $this->entitiesNamespace;
    }

    /**
     * @param string $entitiesNamespace
     */
    public function setEntitiesNamespace($entitiesNamespace){
        $this->entitiesNamespace = $entitiesNamespace;
    }

    /**
     * @return string
     */
    public function getQueriesNamespace(){
        return $this->queriesNamespace;
    }

    /**
     * @param string $queriesNamespace
     */
    public function setQueriesNamespace($queriesNamespace){
        $this->queriesNamespace = $queriesNamespace;
    }

    /**
     * @return DatabaseBuilder
     */
    public function getDatabaseBuilder(){
        return $this->databaseBuilder;
    }

    /**
     * @param DatabaseBuilder $databaseBuilder
     */
    public function setDatabaseBuilder($databaseBuilder){
        $this->databaseBuilder = $databaseBuilder;
    }

    /**
     * @return string
     */
    public function getEntityMetadataSerializePath(){
        return $this->entityMetadataSerializePath;
    }

    /**
     * @param string $entityMetadataSerializePath
     */
    public function setEntityMetadataSerializePath($entityMetadataSerializePath){
        $this->entityMetadataSerializePath = $entityMetadataSerializePath;
    }

    /**
     * @return Core
     */
    public function getCore(){
        return $this->core;
    }

    /**
     * @return DataFetcher
     */
    public function getDataFetcher(){
        return $this->dataFetcher;
    }

    /**
     * @param $name
     * @return Relationship[]
     */
    public function getEntityDependency($name){
        $result = array();
        foreach ($this->getEntitiesList() as $entity) {
            $result += array_filter($this->getEntityMetadata($entity)->getRelationships(), function($item) use ($name){
                return $item->getForeignEntity() == $name;
            });
        }
        return $result;

    }

    //public function
} 