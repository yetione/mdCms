<?php
namespace Core\DataBase\Model;

use Core\DataBase\EntityManager;
use Core\DataBase\Utils\SetOptions;
use Core\IExportable;

class Relationship implements IExportable{

    use SetOptions;

    const ONE_ONE = 1;
    const ONE_MANY = 2;
    const MANY_ONE = 3;
    const MANY_MANY = 4;

    const INNER_JOIN = 5;
    const LEFT_JOIN = 6;
    const RIGHT_JOIN = 7;

    const BEHAVIOUR_NOTHING = 9;
    const BEHAVIOUR_SET_NULL = 10;
    const BEHAVIOUR_DELETE = 11;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var int
     */
    protected $type;

    /**
     * @var int
     */
    protected $joinType;

    /**
     * @var int
     */
    protected $onParentDelete;

    /**
     * @var int
     */
    protected $onForeignDelete;

    /**
     * @var string
     */
    protected $parentEntity;

    /**
     * @var string[]
     */
    protected $parentColumns;

    /**
     * @var string
     */
    protected $foreignEntity;

    /**
     * @var string[]
     */
    protected $foreignColumns;

    /**
     * @var string
     */
    protected $middleTable;

    /**
     * @var string[]
     */
    protected $parentMiddleColumns = array();

    /**
     * @var string[]
     */
    protected $foreignMiddleColumns = array();

    /**
     * @var bool
     */
    protected $loadOnFind = false;

    /**
     * @var EntityMetadata
     */
    protected $parentEntityMetadata;

    /**
     * @var EntityMetadata
     */
    protected $foreignEntityMetadata;

    /**
     * @var string
     */
    protected $joinOperator;

    public function __construct($name, array $options=array()){
        $this->setName($name);
        $this->setOptions($options);
        $this->setDefaults();
    }

    protected function setDefaults(){
        $this->setLoadOnFind(false);
        $this->setJoinType(self::LEFT_JOIN);
        $this->setOnForeignDelete(self::BEHAVIOUR_NOTHING);
        $this->setOnParentDelete(self::BEHAVIOUR_NOTHING);
        $this->setJoinOperator('=');
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
     * @param $type
     * @return bool
     */
    public function isValidType($type){
        return in_array($type, array(self::ONE_ONE, self::ONE_MANY, self::MANY_ONE, self::MANY_MANY));
    }

    /**
     * @param $joinType
     * @return bool
     */
    public function isValidJoinType($joinType){
        return in_array($joinType, array(self::INNER_JOIN, self::LEFT_JOIN, self::RIGHT_JOIN));
    }

    /**
     * @return int
     */
    public function getType(){
        return $this->type;
    }

    /**
     * @param int $type
     * @return $this
     */
    public function setType($type){
        if (!$this->isValidType($type)){
            throw new \InvalidArgumentException(__CLASS__.': Invalid relationship type: '.$type);
        }
        $this->type = $type;
        return $this;
    }

    /**
     * @return int
     */
    public function getJoinType(){
        return $this->joinType;
    }

    /**
     * @param int $joinType
     * @return $this
     */
    public function setJoinType($joinType){
        if (!$this->isValidJoinType($joinType)){
            throw new \InvalidArgumentException(__CLASS__.': Invalid join type: '.$joinType);
        }
        $this->joinType = $joinType;
        return $this;
    }

    /**
     * @return string
     */
    public function getParentEntity(){
        return $this->parentEntity;
    }

    /**
     * @param string $parentEntity
     * @return $this
     */
    public function setParentEntity($parentEntity){
        $this->parentEntity = $parentEntity;
        return $this;
    }

    /**
     * @internal param EntityManager $em
     * @return \string[]
     */
    public function getParentColumns(){
        return $this->parentColumns;
        /*$parentMetadata = $em->getEntityMetadata($this->getParentEntity());
        return array_map(function($col) use($parentMetadata){
            return $parentMetadata->getColumnName($col);
        }, $this->parentColumns);*/
    }

    /**
     * @param \string[] $parentColumns
     * @return $this
     */
    public function setParentColumns(array $parentColumns){
        $this->parentColumns = $parentColumns;
        return $this;
    }

    /**
     * @param string $parentColumn
     * @return $this
     */
    public function addParentColumn($parentColumn){
        $this->parentColumns[] = $parentColumn;
        return $this;
    }

    /**
     * @return string
     */
    public function getForeignEntity(){
        return $this->foreignEntity;
    }

    /**
     * @param string $foreignEntity
     * @return $this
     */
    public function setForeignEntity($foreignEntity){
        $this->foreignEntity = $foreignEntity;
        return $this;
    }

    /**
     * @internal param EntityManager $em
     * @return \string[]
     */
    public function getForeignColumns(){
        return $this->foreignColumns;
    }

    /**
     * @param \string[] $foreignColumns
     * @return $this
     */
    public function setForeignColumns(array $foreignColumns){
        $this->foreignColumns = $foreignColumns;
        return $this;
    }

    /**
     * @param string $foreignColumn
     * @return $this
     */
    public function addForeignColumn($foreignColumn){
        $this->foreignColumns[] = $foreignColumn;
        return $this;
    }

    /**
     * @return string
     */
    public function getMiddleTable(){
        return $this->middleTable;
    }

    /**
     * @param string $middleTable
     * @return $this
     */
    public function setMiddleTable($middleTable){
        $this->middleTable = $middleTable;
        return $this;
    }

    /**
     * @param bool $fullNames
     * @return string[]
     */
    public function getParentMiddleColumns($fullNames=false){
        if ($fullNames){
            return array_map(function($column){
                return $this->getMiddleTable() . '.' . $column;
            }, $this->parentMiddleColumns);
        }
        return $this->parentMiddleColumns;
    }

    /**
     * @param array $parentMiddleColumn
     * @return $this
     */
    public function setParentMiddleColumns(array $parentMiddleColumn){
        $this->parentMiddleColumns = $parentMiddleColumn;
        return $this;
    }

    /**
     * @param string $column
     * @return $this
     */
    public function addParentMiddleColumn($column){
        $this->parentMiddleColumns[] = $column;
        return $this;
    }

    /**
     * @param bool $fullNames
     * @return string[]
     */
    public function getForeignMiddleColumns($fullNames=false){
        if ($fullNames){
            return array_map(function($column){
                return $this->getMiddleTable() . '.' . $column;
            }, $this->foreignMiddleColumns);
        }
        return $this->foreignMiddleColumns;
    }

    /**
     * @param array $foreignMiddleColumn
     * @return $this
     */
    public function setForeignMiddleColumns(array $foreignMiddleColumn){
        $this->foreignMiddleColumns = $foreignMiddleColumn;
        return $this;
    }

    /**
     * @param string $column
     * @return $this
     */
    public function addForeignMiddleColumn($column){
        $this->foreignMiddleColumns[] = $column;
        return $this;
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
        return new self($array['name'], $array);
    }

    /**
     * @return boolean
     */
    public function isLoadOnFind(){
        return $this->loadOnFind;
    }

    /**
     * @param boolean $loadOnFind
     * @return $this
     */
    public function setLoadOnFind($loadOnFind){
        $this->loadOnFind = $loadOnFind;
        return $this;
    }

    public function getJoin(EntityManager $em){
        $foreignMetadata = $em->getEntityMetadata($this->getForeignEntity());
        $a = array();
        if ($this->getType() === self::MANY_MANY){
            $a[] = array($this->getJoinType(), $this->getMiddleTable(), $this->getParentColumns(), $this->getParentMiddleColumns());
            $a[] = array($this->getJoinType(), $foreignMetadata->getTableName(), $this->getForeignMiddleColumns(), $this->getForeignColumns());
        }else{
            $a[] = array($this->getJoinType(), $foreignMetadata->getTableName(), $this->getParentColumns(), $this->getForeignColumns());
        }
        return $a;
    }

    /**
     * @return EntityMetadata
     */
    public function getParentEntityMetadata(){
        return $this->parentEntityMetadata;
    }

    /**
     * @param EntityMetadata $parentEntityMetadata
     */
    public function setParentEntityMetadata(EntityMetadata $parentEntityMetadata){
        $this->parentEntityMetadata = $parentEntityMetadata;
    }


    /**
     * @return EntityMetadata
     */
    public function getForeignEntityMetadata(){
        return $this->foreignEntityMetadata;
    }

    /**
     * @param EntityMetadata $foreignEntityMetadata
     */
    public function setForeignEntityMetadata(EntityMetadata $foreignEntityMetadata){
        $this->foreignEntityMetadata = $foreignEntityMetadata;
    }

    /**
     * @return int
     */
    public function getOnForeignDelete(){
        return $this->onForeignDelete;
    }

    /**
     * @param int $onForeignDelete
     * @return $this
     */
    public function setOnForeignDelete($onForeignDelete){
        if (!$this->isValidBehaviour($onForeignDelete)){
            throw new \InvalidArgumentException(__CLASS__.': Invalid behavior type: '.$onForeignDelete);
        }
        $this->onForeignDelete = $onForeignDelete;
        return $this;
    }

    /**
     * @return int
     */
    public function getOnParentDelete(){
        return $this->onParentDelete;
    }

    /**
     * @param int $onParentDelete
     * @return $this
     */
    public function setOnParentDelete($onParentDelete){
        if (!$this->isValidBehaviour($onParentDelete)){
            throw new \InvalidArgumentException(__CLASS__.': Invalid behavior type: '.$onParentDelete);
        }
        $this->onParentDelete = $onParentDelete;
        return $this;
    }

    /**
     * @param $behaviour
     * @return bool
     */
    public function isValidBehaviour($behaviour){
        return in_array($behaviour, array(self::BEHAVIOUR_DELETE, self::BEHAVIOUR_NOTHING, self::BEHAVIOUR_SET_NULL));
    }

    /**
     * @return string
     */
    public function getJoinOperator(){
        return $this->joinOperator;
    }

    /**
     * @param string $joinOperator
     */
    public function setJoinOperator($joinOperator){
        $this->joinOperator = $joinOperator;
    }

}