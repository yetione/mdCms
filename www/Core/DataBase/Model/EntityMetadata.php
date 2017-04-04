<?php
namespace Core\DataBase\Model;


use Core\DataBase\EntityManager;
use Core\DataBase\Utils\SetOptions;
use Core\IExportable;

class EntityMetadata implements IExportable{

    use SetOptions;


    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $tableName;

    /**
     * [
     * 'sqlName'=>$sqlName,
     * 'phpName'=>$phpName,
     * 'defaultValue'=>$defaultValue,
     * 'beforeGet'=>$beforeGet,
     * 'beforeSet'=>$beforeSet,
     * 'type'=>$type
     * ]
     * @var array
     */
    protected $fieldsMapping = array();

    /**
     * @var array
     */
    protected $sqlFields = array();

    /**
     * @var Relationship[]
     */
    protected $relationships = array();

    /**
     * @var string
     */
    protected $entityClassName;

    /**
     * @var string
     */
    protected $queryClassName;

    /**
     * @var string
     */
    protected $quoteChar = '`';

    /**
     * @var EntityManager
     */
    protected $entityManager = null;

    /**
     * @var string
     */
    protected $description;

    /**
     * @var array
     */
    protected $accessFlags = [];

    /**
     * @var bool
     */
    protected $truncateOnCreate = false;


    public function __construct($name){
        $this->name = $name;
    }


    /**
     * @return string
     */
    public function getTableName(){
        return $this->tableName;
    }

    /**
     * @param string $tableName
     */
    public function setTableName($tableName){
        $this->tableName = $tableName;
    }

    /**
     * Этот статический метод вызывается для тех классов,
     * которые экспортируются функцией var_export() начиная с PHP 5.1.0.
     * Параметр этого метода должен содержать массив,
     * состоящий из экспортируемых свойств в виде array('property' => value, ...).
     * @see http://php.net/manual/ru/language.oop5.magic.php#object.set-state
     * @param array $array
     * @return mixed
     */
    public static function __set_state(array $array){
        $obj = new self($array['name']);
        $obj->setOptions($array);
        return $obj;
    }

    /**
     * @param array $fieldsMapping
     */
    public function setFieldsMapping($fieldsMapping){
        $this->fieldsMapping = $fieldsMapping;
    }

    /**
     * @param array $relationships
     */
    public function setRelationships($relationships){
        $this->relationships = $relationships;
    }


    /**
     * @return string
     */
    public function getName(){
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name){
        $this->name = $name;
    }

    /**
     * @return array
     */
    public function getFieldsMapping(){
        return $this->fieldsMapping;
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
    public function getEntityClassName(){
        return $this->entityClassName;
    }

    /**
     * @param string $entityClassName
     */
    public function setEntityClassName($entityClassName){
        $this->entityClassName = $entityClassName;
    }

    /**
     * @return string
     */
    public function getQueryClassName(){
        return $this->queryClassName;
    }

    /**
     * @param string $queryClassName
     */
    public function setQueryClassName($queryClassName){
        $this->queryClassName = $queryClassName;
    }

    /**
     * @param $name
     * @return Relationship|null
     */
    public function getRelationship($name){
        if (isset($this->relationships[$name])){
            $this->relationships[$name]->setParentEntityMetadata($this);
            $this->relationships[$name]->setForeignEntityMetadata($this->getEntityManager()->getEntityMetadata($this->relationships[$name]->getForeignEntity()));
            return $this->relationships[$name];
        }
        return null;
        //return isset($this->relationships[$name]) ? $this->relationships[$name]: null;
    }

    /**
     * @param $entName
     * @return Relationship|null
     */
    public function getRelationshipWithEntity($entName){
        foreach ($this->getRelationships() as $relation) {
            if ($relation->getForeignEntity() == $entName){
                return $relation;
            }
        }
        return null;
    }

    public function quote($string){
        return preg_replace_callback('/^(\w+)(.(\w+))?$/', function($array){
            return $this->quoteChar.$array[1].$this->quoteChar.
            (isset($array[3]) ? '.'.$this->quoteChar.$array[3].$this->quoteChar : '');
        }, $string, -1, $count);
    }

    public function quoteArray(array $arr){
        return array_map(array($this, 'quote'), $arr);
    }

    /**
     * @param $column
     * @return string
     */
    public function getColumnName($column){
        return $this->getTableName().'.'.$column;
    }

    /**
     * @param $phpName
     * @return string
     */
    public function getFieldAlias($phpName){
        return $this->getName().'_'.$phpName;
    }

    /**
     * @param $sqlName
     * @return string|null
     */
    public function getPhpName($sqlName){
        $field = $this->getFieldByType($sqlName, 'sqlName');
        return is_null($field) ? null : $field['phpName'];
    }

    /**
     * @param $phpName
     * @return string|null
     */
    public function getSqlName($phpName){
        $field = $this->getFieldByType($phpName, 'phpName');
        return is_null($field) ? null : $field['sqlName'];
    }

    /**
     * @param $phpName
     * @return string
     */
    public function getFullQuotedSqlName($phpName){
        $field = $this->getFieldByType($phpName, 'phpName');
        return $this->quote($this->getColumnName($field['sqlName']));
    }

    /**
     * @param string $name
     * @param string $type
     * @return string
     */
    public function getFieldFullSqlName($name, $type='phpName'){
        $field = $this->getFieldByType($name, $type);
        if (is_null($field)){
            throw new \RuntimeException(__CLASS__.': Unknown name for sqlField: '.$name);
        }
        return $this->getColumnName($field['sqlName']);
    }

    /**
     * @param string $name
     * @return bool
     */
    public function isPhpName($name){
        return !is_null($this->getFieldByType($name, 'phpName'));
    }

    /**
     * @param string $name
     * @return bool
     */
    public function isSqlName($name){
        return !is_null($this->getFieldByType($name, 'sqlName'));
    }

    /**
     * @param string $name
     * @param string $type
     * @return array|null
     */
    public function getFieldByType($name, $type){
        if (!in_array($type, array('phpName', 'sqlName'))){
            throw new \InvalidArgumentException(__CLASS__.': getFieldByType: Unknown type: '.$type);
        }
        foreach ($this->getFieldsMapping() as $field) {
            if ($name == $field[$type]){
                return $field;
            }
        }
        return null;
    }

    /**
     * @param string|null $tableAlias
     * @return \string[]
     */
    public function getSqlColumns($tableAlias=null){
        $result = array();
        foreach ($this->getFieldsMapping() as $fieldData){
            $result[] = array(
                'name'=>is_null($tableAlias) ? $this->quote($this->getColumnName($fieldData['sqlName'])) : $this->quote($tableAlias.'.'.$fieldData['sqlName']),
                'alias'=>$this->getFieldAlias($fieldData['phpName'])
            );
        }
        return $result;
    }

    public function getSqlFields($addAlias=false){
        $result = array();
        foreach ($this->getFieldsMapping() as $fieldData){
            $result[] = array($this->getColumnName($fieldData['sqlName']), ($addAlias ? $this->getFieldAlias($fieldData['phpName']) : null));
        }
        return $result;
    }

    public function getSelectFields(){
        $result = array();
        foreach ($this->getFieldsMapping() as $fieldData){
            $result[] = $this->getColumnName($fieldData['sqlName']);
        }
        return $result;
    }

    public function getSelectData(EntityManager $em=null){
        $fields = $this->getSelectFields();
        $joins = array();
        foreach ($this->getRelationships() as $rel){
            if ($rel->isLoadOnFind()){
                if (is_null($em)){
                    throw new \InvalidArgumentException(__CLASS__.': EntityManger not received for entity '.$this->getName());
                }
                $join = $rel->getJoin($em);
                if ($rel->getType() === Relationship::MANY_MANY){
                    if (count($join) != 2){
                        throw new \RuntimeException(__CLASS__.': Invalid join for Many to Many relationship');
                    }
                    $joins[] = $join[0];
                    $joins[] = $join[1];
                }else{
                    $joins[] = $join;
                }
                $metadata = $em->getEntityMetadata($rel->getForeignEntity());
                list($fieldRel, $joinsRel) = $metadata->getSelectData($em);
                $fields = array_merge($fields, $fieldRel);
                $joins = array_merge($joins, $joinsRel);
            }
        }
        return array($fields, $joins);
    }

    /**
     * @param EntityManager $em
     * @return array
     */
    public function getQueryData(EntityManager $em){
        $fields = $this->getSqlColumns();
        $joins = array();
        foreach ($this->getRelationships() as $name => $relation){
            if ($relation->isLoadOnFind()){
                $foreignEntityMetadata = $em->getEntityMetadata($relation->getForeignEntity());
                $joins = array_merge($joins, $this->getRelationshipJoin($name, $em));
                list($foreignFields, $foreignJoins) = $foreignEntityMetadata->getQueryData($em);
                $fields = array_merge($fields, $foreignFields);
                $joins = array_merge($joins, $foreignJoins);
            }
        }
        return array($fields, $joins);
    }

    public function getRelationshipJoin($name, EntityManager $em){
        $relation = $this->getRelationship($name);
        $joins = array();
        $foreignEntityMetadata = $em->getEntityMetadata($relation->getForeignEntity());

        if ($relation->getType() === Relationship::MANY_MANY){
            $joins[] = array(
                $relation->getJoinType(),
                $relation->getMiddleTable(),
                $this->getFullColumnsName($relation->getParentColumns()),
                $relation->getParentMiddleColumns(true)
            );
            $joins[] = array(
                $relation->getJoinType(),
                $foreignEntityMetadata->getTableName(),
                $relation->getForeignMiddleColumns(true),
                $foreignEntityMetadata->getFullColumnsName($relation->getForeignColumns())
            );
        }else{
            $joins[] = array(
                $relation->getJoinType(),
                $foreignEntityMetadata->getTableName(),
                $this->getFullColumnsName($relation->getParentColumns()),
                $foreignEntityMetadata->getFullColumnsName($relation->getForeignColumns())
            );
        }
        return $joins;
    }


    public function __sleep(){
        $result = get_object_vars($this);
        unset($result['entityManager']);
        return array_keys($result);
    }

    /**
     *
     * @return string[]
     */
    public function getFullColumnsName(){
        $args = func_get_args();
        if (count($args) == 1 && is_array($args[0])){
            $args = $args[0];
        }
        for ($i=0;$i<count($args);$i++){
            $args[$i] = $this->quote($this->getColumnName($args[$i]));
        }
        return $args;
    }




    public function getPhpFields(){

    }

    /**
     * @return string
     */
    public function getQuoteChar(){
        return $this->quoteChar;
    }

    /**
     * @param string $quoteChar
     */
    public function setQuoteChar($quoteChar){
        $this->quoteChar = $quoteChar;
    }

    /**
     * @return EntityManager
     */
    public function getEntityManager(){
        return $this->entityManager;
    }

    /**
     * @param EntityManager $entityManager
     */
    public function setEntityManager(EntityManager $entityManager){
        $this->entityManager = $entityManager;
    }

    /**
     * @return string
     */
    public function getDescription(){
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription($description){
        $this->description = $description;
    }

    /**
     * @return array
     */
    public function getAccessFlags(){
        return $this->accessFlags;
    }

    /**
     * @param array $accessFlags
     */
    public function setAccessFlags($accessFlags){
        $this->accessFlags = $accessFlags;
    }

    /**
     * @return boolean
     */
    public function isTruncateOnCreate(){
        return $this->truncateOnCreate;
    }

    /**
     * @param boolean $truncateOnCreate
     */
    public function setTruncateOnCreate($truncateOnCreate){
        $this->truncateOnCreate = $truncateOnCreate;
    }
} 