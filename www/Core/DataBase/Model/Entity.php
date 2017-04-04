<?php
namespace Core\DataBase\Model;


use Core\DataBase\EntityManager;
use Core\DataBase\Exception\StatementExecuteError;
use Core\Debugger;

abstract class Entity {

    /**
     * @var string
     */
    protected $name;

    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var EntityMetadata
     */
    protected $metadata;

    /**
     * @var array
     */
    public $properties = array();

    protected $isNew = true;

    protected $virtualFields = array();

    protected $throwNotFoundExceptions = false;

    public function __construct(EntityMetadata $metadata, EntityManager $em){
        $this->entityManager = $em;
        $this->metadata = $metadata;
        $this->name = $metadata->getName();
        $this->buildFields();
        $this->isNew = true;
        $this->init();
    }

    public function __sleep(){
        return array('name', 'properties');
    }

    abstract protected function init();


    protected function addVirtualField($name,callable $getCallback=null,callable $setCallback=null){
        if (!isset($this->virtualFields[$name])){
            $this->virtualFields[$name] = array(
                'get'=>$getCallback,
                'set'=>$setCallback
            );
        }
    }

    protected function buildFields(){
        $fieldsData = $this->metadata->getFieldsMapping();
        foreach ($fieldsData as $data){
            $this->properties[$data['phpName']] = $data['defaultValue'];
        }

        foreach ($this->metadata->getRelationships() as $name => $relation) {
            $this->properties[$name] = null ;
        }
    }


    /**
     * @param $methodName
     * @param $methodArguments
     * @return mixed|$this
     */
    public function __call($methodName, $methodArguments){
        $methodsString = 'get|set|add|count';
        $matches = array();
        if (preg_match("/^({$methodsString})(.+)$/", $methodName, $matches)){
            if (count($matches) == 3){
                $method = $matches[1];
                if (!method_exists($this, $method)){
                    throw new \BadMethodCallException(__CLASS__.': Method: '.$methodName.' not found.');
                }
                $field = $matches[2];
                array_unshift($methodArguments, $field);
                return call_user_func_array(array($this, $method), $methodArguments);
            }
        }
        throw new \BadMethodCallException(__CLASS__.': Method: '.$methodName.' not found.');
    }

    /**
     * @return boolean
     */
    public function isNew(){
        return $this->isNew;
    }

    protected function count($field){
        if(!is_null($relationship = $this->metadata->getRelationship($field))){
            switch ($relationship->getType()){
                case Relationship::MANY_MANY:
                    $count = 'COUNT(*)';
                    $table = $this->metadata->quote($relationship->getMiddleTable());
                    $foreignCols = $relationship->getParentMiddleColumns(true);
                    $parentCols = $relationship->getParentColumns();
                    break;
                case Relationship::MANY_ONE:
                case Relationship::ONE_ONE:
                    return 1;
                case Relationship::ONE_MANY:
                    $count = 'COUNT(`id`)';
                    $table = $this->metadata->quote($this->entityManager->getEntityMetadata($relationship->getForeignEntity())->getTableName());
                    $foreignMetadata = $this->entityManager->getEntityMetadata($relationship->getForeignEntity());
                    $foreignCols = array_map(function($e) use($foreignMetadata){
                        return $foreignMetadata->getColumnName($e);
                    },$relationship->getForeignColumns());

                    $parentCols = $relationship->getParentColumns();

                    break;
                default:
                    throw new \RuntimeException(__CLASS__.': Unknown relation type '.$relationship->getType());
            }
            $params = array();
            $where = array();
            for($i=0;$i<count($parentCols);$i++){
                $fieldData = $this->metadata->getFieldByType($parentCols[$i],'sqlName');
                $paramName = ':'.$this->metadata->getName().'_'.$parentCols[$i];
                $where[] = $this->metadata->quote($foreignCols[$i]).' = '.$paramName;
                $params[$paramName] =$this->get($fieldData['phpName']);
            }

            $sql = "SELECT {$count} FROM {$table} WHERE ".implode(' AND ',$where);

            $stm = $this->entityManager->getCore()->getDb()->prepare($sql);
            $stm->execute($params);

            $result = $stm->fetch(\PDO::FETCH_NUM);
            return (int) $result[0];
        }
        return null;
    }

    /**
     * @param boolean $isNew
     */
    public function setIsNew($isNew){
        $this->isNew = (bool) $isNew;
    }

    /**
     * @param string $field
     * @param mixed|null $default
     * @param bool $useCallback
     * @return mixed|null
     */
    protected function get($field, $default=null, $useCallback=false){
        if (isset($this->virtualFields[$field])){

            return call_user_func($this->virtualFields[$field]['get']);
        }
        if (is_null($this->metadata)){
            return isset($this->properties[$field]) ? $this->properties[$field] : null;
        }
        if (!isset($this->properties[$field]) && !is_null($relation = $this->metadata->getRelationship($field))){
            $query = $this->entityManager->getEntityQuery($relation->getForeignEntity());
            if ($relation->getType() === Relationship::MANY_ONE){
                $foreignMetadata = $this->entityManager->getEntityMetadata($relation->getForeignEntity());
                $foreignCols = $relation->getForeignColumns();
                $parentCols = $relation->getParentColumns();

                for($i=0;$i<count($parentCols);$i++){
                    $fFData = $foreignMetadata->getFieldByType($foreignCols[$i], 'sqlName');
                    $pFData = $this->metadata->getFieldByType($parentCols[$i], 'sqlName');
                    $methodName = 'findBy'.$fFData['phpName'];
                    $query->$methodName($this->get($pFData['phpName'], null, true));
                }
                $this->properties[$field] = $query->loadOne();
            }else{
                $foreignMetadata = $this->entityManager->getEntityMetadata($relation->getForeignEntity());
                $foreignCols = $relation->getForeignColumns();
                $parentCols = $relation->getParentColumns();
                for($i=0;$i<count($foreignCols);$i++){
                    $fFData = $foreignMetadata->getFieldByType($foreignCols[$i], 'sqlName');
                    $pFData = $this->metadata->getFieldByType($parentCols[$i], 'sqlName');
                    $fMethodName = 'findBy'.$fFData['phpName'];
                    $pMethodName = 'get'.$pFData['phpName'];
                    $query->$fMethodName($this->$pMethodName(), '=');
                }
                if ($relation->getType() === Relationship::ONE_ONE){
                    $this->properties[$field] = $query->loadOne();
                }else{
                    $this->properties[$field] = $query->load();
                }
            }
            return $this->properties[$field];
        }
        if (is_null($this->metadata) && isset($this->properties[$field])){
            return $this->properties[$field];
        }
        $fieldData = $this->metadata->getFieldByType($field, 'phpName');
        return isset($this->properties[$field]) ? ($useCallback && is_callable($fieldData['beforeSet']) ?  $fieldData['beforeGet']($this->properties[$field]) : $this->properties[$field]) : $default;
    }

    /**
     * @param $field
     * @param $value
     * @return $this
     */
    protected function set($field, $value){
        if (array_key_exists($field, $this->properties)){
            if (!is_null($relation = $this->metadata->getRelationship($field))){
                $foreignEntityMetadata = $this->entityManager->getEntityMetadata($relation->getForeignEntity());
                if (!is_a($value, $foreignEntityMetadata->getEntityClassName())) {
                    throw new \InvalidArgumentException(__CLASS__.': Invalid value for '.$field. '. Excepted '.$foreignEntityMetadata->getEntityClassName());
                }
                $localColumns = $relation->getParentColumns();
                $foreignColumns = $relation->getForeignColumns();
                for($i=0;$i<count($localColumns);$i++){
                    $localMethod = 'set'.$this->metadata->getPhpName($localColumns[$i]);
                    $foreignMethod = 'get'.$foreignEntityMetadata->getPhpName($foreignColumns[$i]);
                    $this->$localMethod($value->$foreignMethod());
                }


            }else{
                $fData = $this->metadata->getFieldByType($field, 'phpName');
                if ($fData['type'] !== TYPE_CLEAR){
                    $value = QS_validate($value, $fData['type'], $fData['defaultValue']);
                }
            }
            $this->properties[$field] = $value;
        }
        return $this;
    }

    protected function add($field, $value){
        if (isset($this->properties[$field])){
            if(!is_null($relation = $this->metadata->getRelationship($field))){
                if (in_array($relation->getType(), array(Relationship::MANY_MANY, Relationship::ONE_MANY))){
                    if (!is_array($this->properties[$field])){
                        $this->properties[$field] = array();
                    }
                    if (is_a($value, $this->entityManager->getEntityMetadata($relation->getForeignEntity())->getEntityClassName())){
                        $this->properties[$field][] = $value;
                    }
                }
            }
        }
    }

    /**
     * @param array $data
     * @param bool $useCallback Использовать ли callback-функцию перед установкой значения свойства.
     *                          Используется для перевода данных из БД в php.
     * @param bool $clearOld Очищать ли старые данные
     * @param bool $reInit Инициализировать сущность заново
     * @return $this
     */
    public function fromArray(array $data, $useCallback=true, $clearOld=true, $reInit=true){
        foreach ($this->properties as $property=>&$value){
            if (isset($data[$property])){
                if (!is_null($relation = $this->metadata->getRelationship($property))){
                    if (is_null($data[$property]) || empty($data[$property])){
                        $value = null;
                    }elseif(is_array($data[$property])){
                        switch ($relation->getType()){
                            case Relationship::ONE_ONE:
                            case Relationship::MANY_ONE:
                                $entity = $this->entityManager->getEntity($relation->getForeignEntity());
                                $entity->fromArray($data[$property]);
                                $value = $entity;
                                break;
                            case Relationship::ONE_MANY:

                                $c = new Collection();
                                foreach($data[$property] as $v){
                                    $entity = $this->entityManager->getEntity($relation->getForeignEntity());
                                    $entity->fromArray($v);
                                    $c[] = $entity;
                                    //$data[$property] = $entity;
                                }
                                $value = $c;

                                break;
                        }

                        /*
                        try{
                            $entity = $this->entityManager->getEntity($relation->getForeignEntity());
                            $entity->fromArray($data[$property]);
                            $value = $entity;
                            var_dump('arr');
                        }catch (\Exception $e){
                            $value = array_map(function($item) use ($relation){
                                $ent =  $this->entityManager->getEntity($relation->getForeignEntity());
                                $ent->fromArray($item);
                                return $ent;
                            }, $data[$property]);
                            var_dump($value);
                        }*/



                    }else{
                        $entity = $this->entityManager->getEntity($relation->getForeignEntity());
                        $entity->fromArray($data[$property]);
                        $value = $entity;
                    }

                }else{

                    $fieldData = $this->metadata->getFieldByType($property, 'phpName');
                    call_user_func_array([$this, 'set'.$property], [$useCallback && is_callable($fieldData['beforeSet']) ?
                        $fieldData['beforeSet']($data[$property]) :
                        $data[$property]]);
                    /*$value = $useCallback && is_callable($fieldData['beforeSet']) ?
                        $fieldData['beforeSet']($data[$property]) :
                        $data[$property];*/

                }
            }elseif($clearOld){
                $this->properties[$property] = null;
            }
        }
        $this->setIsNew(!$this->getId());
        if ($reInit) $this->init();
        return $this;
    }


    /**
     * @param array $fields
     * @param bool $useCallback
     * @return array
     */
    public function toArray($useCallback=true, $fields=array()){

        $result = array();
        $f = array_filter($this->properties, function($value, $prop)use($fields){
            return in_array($prop, $fields) || empty($fields);
        }, ARRAY_FILTER_USE_BOTH);
        foreach ($f as $property=>$value) {
            if (!is_null($relation = $this->metadata->getRelationship($property))){
                if (empty($value)){
                    $result[$property] = null;
                }elseif($value instanceof Collection){
                    $result[$property] = array_map(function($item) use ($useCallback) { return $item->toArray($useCallback);}, $value->getData());
                }else{
                    $result[$property] = $value->toArray($useCallback);
                }


                //$result[$property] = empty($value) ? null : $value->toArray($useCallback);

            }else{
                $fieldData = $this->metadata->getFieldByType($property, 'phpName');
                $result[$property] = $useCallback && is_callable($fieldData['beforeGet']) ? $fieldData['beforeGet']($value) : $value;
            }

        }
        return $result;
    }



    public function _getName(){
        return $this->name;
    }

    public function _setEntityManager(EntityManager $entityManager){
        $this->entityManager = $entityManager;
    }

    public function _setEntityMetadata(EntityMetadata $entityMetadata){
        $this->metadata = $entityMetadata;
    }

    public function setId($value){
        $this->properties['Id'] = (int) $value;
    }

    /**
     * @param string $prop
     * @param mixed $value
     * @param int $type
     * @return $this
     */
    protected function _setTypedValue($prop, $value, $type){
        if (array_key_exists($prop, $this->properties)){
            if (!is_null($value = QS_validate($value, $type))){
                $this->properties[$prop] = $value;
                return $this;
            }
            if ($this->throwNotFoundExceptions) throw new \InvalidArgumentException(__METHOD__.' : value of '.$prop.' is not valid.');
        }
        if ($this->throwNotFoundExceptions) throw new \InvalidArgumentException(__METHOD__.' : property '.$prop.' not found');
        return $this;
    }

    /**
     * @param bool $throwException
     * @return bool
     * @throws StatementExecuteError
     */
    public function save($throwException=false){
        if ($this->entityManager){
            $query = $this->entityManager->getEntityQuery($this->_getName());
            try{
                $new = $query->save($this, true);
                $this->fromArray($new->toArray(false),false, false, false);
            }catch (StatementExecuteError $e){
                Debugger::log(__CLASS__.'::save : Cant save entity '.$this->_getName().'. '.$e->getMessage());
                if ($throwException) throw $e;
                return false;
            }
        }
        return false;

    }


} 