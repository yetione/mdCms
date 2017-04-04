<?php
namespace Core\DataBase\Model;


use Core\DataBase\Utils\SetOptions;
use Modules\Kernel\Kernel;

class EntityConstructor {

    use SetOptions;

    /**
    * Название сущности
    * @var string
    */
    protected $name;

    /**
     * Название таблицы в БД
     * @var string
     */
    protected $tableName;

    /**
     * Массив с полями сущности
     * @var array
     */
    protected $fields = array();

    /**
     * @var Relationship[]
     */
    protected $relationships = array();

    /**
     * @var string
     */
    protected $entityClass;

    /**
     * @var string
     */
    protected $queryClass;

    /**
     * @var string
     */
    protected $description = '';

    /**
     * @var array
     */
    protected $values = [];

    /**
     * @var array
     */
    protected $accessFlags = [
        Kernel::ENTITY_GET=>[],
        Kernel::ENTITY_DELETE=>[],
        Kernel::ENTITY_UPDATE=>[]
    ];

    /**
     * @var bool
     */
    protected $truncateOnCreate = false;



    public function __construct($tableName, array $options=array()){
        $this->setTableName($tableName);
        $this->setOptions($options);
    }

    /**
     * @return string
     */
    public function getTableName(){
        return $this->tableName;
    }

    /**
     * @param string $tableName
     * @return $this
     */
    public function setTableName($tableName){
        $this->tableName = $tableName;
        return $this;
    }

    /**
     * Метод добавляет поле сущности.
     * @param string $sqlName
     * @param mixed|null $defaultValue значение поля по умолчанию,
     * @param int $type тип поля см. definitions.php
     * @param string|null $phpName имя поля, используемое в PHP
     * @param callable $beforeGet callback-функция вызываемая до возвращения значения поля, перед записью в БД
     * @param callable $beforeSet callback-функция, вызываемая перед установкой значения полю из БД
     * @return $this
     */
    public function addField($sqlName, $defaultValue=null, $type=TYPE_CLEAR, $phpName=null, callable $beforeGet=null, callable $beforeSet=null){
        $this->fields[] = array(
            'sqlName'=>$sqlName,
            'phpName'=>$phpName,
            'defaultValue'=>$defaultValue,
            'type'=>$type,
            'beforeGet'=>$beforeGet,
            'beforeSet'=>$beforeSet
        );
        return $this;
    }

    /**
     * @return string
     */
    public function getName(){
        return $this->name;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setName($name){
        $this->name = $name;
        return $this;
    }

    /**
     * @return array
     */
    public function getFields(){
        return $this->fields;
    }

    /**
     * @param string $name
     * @return Relationship
     */
    public function createRelationship($name){
        $relationship = new Relationship($name);
        $this->relationships[] = $relationship;
        return $relationship;
    }

    /**
     * @return Relationship[]
     */
    public function getRelationships(){
        return $this->relationships;
    }

    /**
     * @return string
     */
    public function getEntityClass(){
        return $this->entityClass;
    }

    /**
     * @param string $entityClass
     * @return $this
     */
    public function setEntityClass($entityClass){
        $this->entityClass = $entityClass;
        return $this;
    }

    /**
     * @return string
     */
    public function getQueryClass(){
        return $this->queryClass;
    }

    /**
     * @param string $queryClass
     * @return $this
     */
    public function setQueryClass($queryClass){
        $this->queryClass = $queryClass;
        return $this;
    }

    /**
     * @return string
     */
    public function getDescription(){
        return $this->description;
    }

    /**
     * @param string $description
     * @return $this
     */
    public function setDescription($description){
        $this->description = $description;
        return $this;
    }

    /**
     * @return array
     */
    public function getValues(){
        return $this->values;
    }

    /**
     * @param array $defaultValues
     * @return $this
     */
    public function setValues($defaultValues){
        $this->values = $defaultValues;
        return $this;
    }

    /**
     * @param array $value
     * @return $this
     */
    public function addValue($value){
        $this->values[] = $value;
        return $this;
    }

    /**
     * @return array
     */
    public function getAccessFlags(){
        return $this->accessFlags;
    }

    /**
     * @param array $accessFlags
     * @return $this
     */
    public function setAccessFlags($accessFlags){
        $this->accessFlags = $accessFlags;
        return $this;
    }

    /**
     * @param string $action
     * @param string $flag
     * @return $this
     */
    public function addAccessFlag($action, $flag){
        $this->accessFlags[$action][] = $flag;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isTruncateOnCreate(){
        return $this->truncateOnCreate;
    }

    /**
     * @param boolean $truncateOnCreate
     * @return $this
     */
    public function setTruncateOnCreate($truncateOnCreate){
        $this->truncateOnCreate = $truncateOnCreate;
        return $this;
    }
} 