<?php
namespace Core\DataBase\Model;


use Core\DataBase\EntityManager;

class QueryCondition {

    protected $glue = 'AND';

    protected $elements = array();

    /**
     * @var QueryCondition
     */
    protected $parent = null;

    /**
     * @var EntityMetadata
     */
    protected $metadata;

    protected $parameters = array();

    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var int
     */
    protected $index;


    public function __construct($glue, EntityMetadata $metadata, EntityManager $entityManager, QueryCondition $parent=null){
        $this->setGlue($glue);
        $this->parent = $parent;
        $this->metadata = $metadata;
        $this->index = is_null($parent) ? 0 : $parent->getIndex() + 1;
        $this->entityManager = $entityManager;
    }

    public function addChild($glue){
        $obj = new QueryCondition($glue,$this->metadata, $this->entityManager, $this);
        $this->elements[] = $obj;
        return $obj;
    }

    /**
     * @return QueryCondition
     */
    public function getParent(){
        return $this->parent;
    }

    public function addCondition($field, $operator, $value){
        if (!is_null($value) && !is_a($value, '\\Core\\DataBase\\Model\\EntityQuery') && !is_a($value, '\\Core\\DataBase\\Model\\Entity')){
            $value = $this->addParameter($field, $value);
        }
        $this->elements[] = array($field, $operator, $value);
        return $this;
    }

    /**
     * @return string
     */
    public function __toString(){
        return $this->getString();
    }

    /**
     * @return string
     */
    public function getString(){
        $elements = array_map(function($e){
            if(is_array($e) && is_a($e[2], '\\Core\\DataBase\\Model\\EntityQuery')){
                $e = $e[2]->getWhereCondition();
            }
            if (is_a($e, __CLASS__)){
                $str = $e->getString();
                $this->parameters = array_merge($this->parameters, $e->getParameters());
                return $str;
            }elseif(is_a($e[2], '\\Core\\DataBase\\Model\\Entity')){
                $relation = $this->metadata->getRelationship($e[0]);
                $foreignMetadata = $this->entityManager->getEntityMetadata($relation->getForeignEntity());
                $foreignColumns = $relation->getForeignColumns();
                //$foreignColumns = $foreignMetadata->getFullColumnsName($relation->getForeignColumns());
                $fields = array();
                foreach($foreignColumns as $column){
                    $fieldData = $foreignMetadata->getFieldByType($column, 'sqlName');
                    $methodName = 'get'.$fieldData['phpName'];
                    $name = $this->addParameter($column, $e[2]->$methodName());
                    $fields[] = $foreignMetadata->quote($foreignMetadata->getColumnName($fieldData['sqlName'])).'='.$name;
                }
                return $fields = implode(' AND ', $fields);
                //$foreignMetadata->getFullColumnsName();
            }

            //return $this->metadata->quote($this->metadata->getSqlName($e[0])).$e[1].$e[2];
            if ($e[1] == 'IN'){
                $params = array();

                $values = $this->parameters[$e[2]];
                foreach($values as $item){
                    $params[] = $this->addParameter('', $item);
                }
                $result = $this->metadata->getFullQuotedSqlName($e[0]).$e[1].' ('.implode(', ',$params).')';
                unset($this->parameters[$e[2]]);
                return $result;
            }elseif ($e[1] == 'BETWEEN'){
                $values = $this->parameters[$e[2]];
                $result = $this->metadata->getFullQuotedSqlName($e[0]).$e[1].' '.$this->addParameter('', $values[0]).' AND '.$this->addParameter('', $values[1]);
                unset($this->parameters[$e[2]]);
                return $result;
            }
            return $this->metadata->getFullQuotedSqlName($e[0]).$e[1].' '.$e[2];
        }, $this->elements);
        if (count($elements) == 1){
            return $elements[0];
        }
        return '('.implode(' '.$this->glue.' ', $elements).')';
    }

    public function addParameter($name, $value){
        $name = $this->getParameterName($name);
        $this->parameters[$name] = $value;
        return $name;
    }

    public function getParameterName($field){
        return ':'.uniqid();
    }

    /**
     * @return string
     */
    public function getGlue(){
        return $this->glue;
    }

    /**
     * @param string $glue
     */
    public function setGlue($glue){
        $glue = strtoupper($glue);
        if (!in_array($glue, array('AND', 'OR'))){
            throw new \InvalidArgumentException(__CLASS__.': Invalid glue value: '.$glue.'. Expected: AND, OR');
        }
        $this->glue = $glue;
    }

    /**
     * @param QueryCondition $parent
     */
    public function setParent(QueryCondition $parent){
        $this->parent = $parent;
    }

    /**
     * @return EntityMetadata
     */
    public function getMetadata(){
        return $this->metadata;
    }

    /**
     * @param EntityMetadata $metadata
     */
    public function setMetadata(EntityMetadata $metadata){
        $this->metadata = $metadata;
    }

    /**
     * @return int
     */
    public function getIndex(){
        return $this->index;
    }

    /**
     * @param int $index
     */
    public function setIndex($index){
        $this->index = $index;
    }

    /**
     * @return array
     */
    public function getParameters(){
        return $this->parameters;
    }
}