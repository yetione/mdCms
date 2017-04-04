<?php
namespace Core\DataBase\Model;


use Core\DataBase\EntityManager;
use Core\DataBase\Exception\StatementExecuteError;
use Core\DataBase\Exception\UnsafeDelete;
use Core\Debugger;
use \RuntimeException;


class EntityQuery {

    /**
     * @var EntityMetadata
     */
    protected $metadata;

    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var array
     */
    protected $methods = array();

    protected $queryData = array();

    protected $queryDataProcessed = false;

    protected $where = array();

    protected $queries = array();

    protected $joins = array();

    protected $columns = array();

    protected $loadedRelationships = array();

    protected $orderBy = array();

    protected $groupBy = array();

    protected $offset = null;

    protected $count = null;

    /**
     * @var QueryCondition
     */
    protected $whereCondition;

    /**
     * @var QueryCondition
     */
    protected $currentWhereCondition;

    public function __construct(EntityMetadata $metadata, EntityManager $entityManager){
        $this->metadata = $metadata;
        $this->entityManager = $entityManager;
        $this->methods = array('findBy', 'orderBy', 'groupBy');
        $this->createQueryData();
        $this->reset();

    }

    public function reset(){
        $this->whereCondition = new QueryCondition('AND', $this->metadata, $this->entityManager);
        $this->currentWhereCondition = $this->whereCondition;
        $this->queryDataProcessed = false;
        $this->where = array();
        $this->queries = array();
        $this->joins = array();
        $this->columns = array();
        $this->loadedRelationships = array();
        $this->orderBy = array();
        $this->groupBy = array();
        $this->offset = null;
        $this->count = null;
    }

    public function __call($methodName, $methodArguments){
        //var_dump($methodName, $methodArguments);
        $methodsString = implode('|', $this->methods);
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
    public function isQueryDataProcessed(){
        return $this->queryDataProcessed;
    }

    /**
     * @param boolean $queryDataProcessed
     */
    public function setQueryDataProcessed($queryDataProcessed){
        $this->queryDataProcessed = $queryDataProcessed;
    }


    protected function findBy(){
        /*
         * args:
         * 0 - fieldName
         * 1..N - args
         */
        $args = func_get_args();
        $argsCount = count($args);
        if($argsCount == 0){
            throw new \BadMethodCallException(__CLASS__.': Method findBy must receive arguments.');
        }
        $field = array_shift($args);
        if (!is_null($relationship = $this->metadata->getRelationship($field))){
            $relMetadata = $this->entityManager->getEntityMetadata($relationship->getForeignEntity());

            if ($argsCount == 2){
                if (!is_a($args[0], $relMetadata->getEntityClassName())) {
                    throw new \InvalidArgumentException(__CLASS__ . ': Invalid argument for findBy method. Argument is not entity');
                }
                $this->currentWhereCondition->addCondition($field, null, $args[0]);
                $this->where[$field] = $args[0];
                return $this;
            }
            elseif($argsCount == 1){
                $query = $this->entityManager->getEntityQuery($relationship->getForeignEntity());
                $this->queries[] = $query;
                $this->currentWhereCondition->addCondition($field, null, $query);
                //TODO: Не очень правильно так делать. Тут нужно приджоинить только таблицы, но пока так
                $this->initRelationship($relationship);
                return $query;
            }
            throw new \InvalidArgumentException(__CLASS__ . ': Invalid argument for findBy method.');

        }elseif ($this->metadata->isPhpName($field) && $argsCount > 1){
            $this->where[$field] = $args;
            $value = $args[0];
            $operator = !isset($args[1]) || is_null($args[1]) ? '=' : $args[1];
            $this->currentWhereCondition->addCondition($field, $operator, $value);
            return $this;
        }else{
            throw new \InvalidArgumentException(__CLASS__ . ': Invalid argument for findBy method.');
        }
    }

    protected function orderBy(){
        $args = func_get_args();
        $argsCount = count($args);
        if($argsCount == 0){
            throw new \BadMethodCallException(__CLASS__.': Method orderBy must receive arguments.');
        }
        $field = $args[0];
        if (!$this->metadata->isPhpName($field)){
            throw new \InvalidArgumentException(__CLASS__.': orderBy: cant order by: '.$field);
        }
        $direction = isset($args[1]) ? strtoupper($args[1]) : 'ASC';
        if (!in_array($direction, array('ASC', 'DESC'))){
            throw new \InvalidArgumentException(__CLASS__.': orderBy: unknown order direction: '.$direction);
        }
        $fieldData = $this->metadata->getFieldByType($field, 'phpName');
        $this->orderBy[] = array($this->metadata->quote($this->metadata->getColumnName($fieldData['sqlName'])), $direction);
        return $this;
    }

    protected function groupBy(){
        $args = func_get_args();
        $argsCount = count($args);
        if($argsCount == 0){
            throw new \BadMethodCallException(__CLASS__.': Method groupBy must receive arguments.');
        }
        $field = $args[0];
        $direction = isset($args[1]) ? strtoupper($args[1]) : null;
        if (!in_array($direction, array('ASC', 'DESC', null))){
            throw new \InvalidArgumentException(__CLASS__.': groupBy: unknown order direction: '.$direction);
        }
        $this->groupBy[] = array($field, $direction);
        return $this;
    }

    /**
     * @param int $count
     * @param int|null $offset
     * @return $this
     */
    public function limit($count, $offset=null){
        $this->count = (int) $count;
        $this->offset = is_null($offset) ? $offset : (int) $offset;
        return $this;
    }

    public function having(){
        //TODO: Сделать!
        return $this;
    }


    public function group($glue='AND'){
        $this->currentWhereCondition = $this->currentWhereCondition->addChild($glue);
        return $this;
    }

    public function endGroup(){
        if (is_null($this->currentWhereCondition = $this->currentWhereCondition->getParent())){
            throw new RuntimeException(__CLASS__.': Current group not have parent');
        }
        return $this;
    }

    /**
     * @return QueryCondition
     */
    public function getWhereCondition(){
        return $this->whereCondition;
    }

    public function compileGroup(){
        return $this->whereCondition->getString();
    }

//    protected function addWhere($field, $value, )


    /**
     * @param bool $loadRelationships
     * @param bool $throwException
     * @throws StatementExecuteError
     * @throws \Core\DataBase\Exception\RelationshipUnknownType
     * @return Collection
     */
    public function load($loadRelationships=false, $throwException=false){
        $fromTableAlias = 't0';
        $this->columns = $this->metadata->getSqlColumns();
        // Устанавливаем имя таблицы
        $this->queryData['tableName'] = $this->metadata->getTableName();
        // Обрабатываем зависимости
        foreach ($this->metadata->getRelationships() as $name => $relation){
            if ($relation->isLoadOnFind() || $loadRelationships){ // Если зависимость нужно загружать при выборке, то инициализируем ее
                $this->initRelationship($relation);
            }
        }

        $parentTable = $this->metadata->quote($this->metadata->getTableName());
        $whereStr = $this->compileGroup();
        $whereStr = $whereStr == '()' ? '' : 'WHERE '.$whereStr;

        $columnsArray = array_map(function($e){
            return $e['name'].' AS '.$e['alias'];
        }, $this->columns);
        $columnsStr = implode(', ',$columnsArray);

        $joinArray = array();
        $joinTypes = array(
            Relationship::INNER_JOIN => 'INNER JOIN',
            Relationship::LEFT_JOIN => 'LEFT JOIN',
            Relationship::RIGHT_JOIN => 'RIGHT JOIN'
        );
        $tCount = 1;
        foreach ($this->joins as $table=>$data){
            $joinArray[] = $joinTypes[$data['joinType']].' '.$this->metadata->quote($table).' ON '.implode(' AND ', array_map(function($leftColumn, $rightColumn)use($data){
                    return $leftColumn.$data['operator'].$rightColumn;
                }, $data['parentColumns'], $data['foreignColumns']));
            $tCount++;
        }
        $joinStr = implode(' ', $joinArray);


        $groupBy = array_map(function($element){
            return $this->metadata->getFullQuotedSqlName($element[0]). ' '. $element[1];
        }, $this->groupBy);
        $groupByStr = count($groupBy) == 0 ? '' : ' GROUP BY '.implode(',', $groupBy);

        $orderBy = array_map(function($element){
            return $element[0]. ' '. $element[1];
        }, $this->orderBy);
        $orderByStr = count($orderBy) == 0 ? '' : ' ORDER BY '.implode(',', $orderBy);

        $limit = !is_null($this->offset) ? $this->offset.', ' : '';
        $limit .= !is_null($this->count) ? $this->count : '';
        $limitStr = !empty($limit) ? 'LIMIT '.$limit : '';
        $sql = "SELECT {$columnsStr} FROM {$parentTable} {$joinStr} {$whereStr} {$groupByStr} {$orderByStr} {$limitStr}";

        $statement = $this->entityManager->getCore()->getDb()->prepare($sql);

        if (!$statement->execute($this->whereCondition->getParameters())){
            if ($throwException) throw new StatementExecuteError($statement->errorInfo());
            //var_dump($statement->errorInfo());
        }
        //print_r($sql);
        //var_dump($this->whereCondition->getParameters());
        //$result = $this->entityManager->getDataFetcher()->fetch($this->metadata->getName(), $statement->fetchAll(\PDO::FETCH_ASSOC));
        $result = new Collection($this->entityManager->getDataFetcher()->fetch($this->metadata->getName(), $statement->fetchAll(\PDO::FETCH_ASSOC)));
        $this->reset();
        return $result;
    }

    /**
     * @return int|null
     */
    public function count(){
        $whereStr = $this->compileGroup();
        $whereStr = $whereStr == '()' ? ' WHERE 1' : ' WHERE '.$whereStr;
        $sql = 'SELECT COUNT(`id`) FROM '.$this->metadata->quote($this->metadata->getTableName()).$whereStr;
        foreach ($this->metadata->getRelationships() as $name => $relation){
            if ($relation->isLoadOnFind()){ // Если зависимость нужно загружать при выборке, то инициализируем ее
                $this->initRelationship($relation);
            }
        }
        $joinArray = array();
        $joinTypes = array(
            Relationship::INNER_JOIN => 'INNER JOIN',
            Relationship::LEFT_JOIN => 'LEFT JOIN',
            Relationship::RIGHT_JOIN => 'RIGHT JOIN'
        );
        foreach ($this->joins as $table=>$data){
            $joinArray[] = $joinTypes[$data['joinType']].' '.$this->metadata->quote($table).' ON '.implode(' AND ', array_map(function($leftColumn, $rightColumn){
                    return $leftColumn.'='.$rightColumn;
                }, $data['parentColumns'], $data['foreignColumns']));
        }
        $joinStr = implode(' ', $joinArray);
        $parentTable = $this->metadata->quote($this->metadata->getTableName());
        $sql = "SELECT  COUNT({$parentTable}.`id`) FROM {$parentTable} {$joinStr} {$whereStr}";
        $stm = $this->entityManager->getCore()->getDb()->prepare($sql);
        if (!$stm->execute($this->whereCondition->getParameters())){
            throw new \RuntimeException(__CLASS__.': '.$stm->errorCode());
        }
        $result = null;
        $stm->bindColumn(1, $result, \PDO::PARAM_INT);
        $stm->fetch(\PDO::FETCH_NUM);
        return $result;
    }

    public function countAll(){
        $sql = 'SELECT COUNT(`id`) FROM '.$this->metadata->quote($this->metadata->getTableName());
        $stm = $this->entityManager->getCore()->getDb()->prepare($sql);
        if (!$stm->execute()){
            throw new \RuntimeException(__CLASS__.': '.$stm->errorCode());
        }
        $result = null;
        $stm->bindColumn(1, $result, \PDO::PARAM_INT);
        $stm->fetch(\PDO::FETCH_NUM);
        return $result;
    }

    /**
     * Метод инициализирует отношение: добавляет столбцы, присоединяет таблицы
     * @param Relationship $relation
     */
    protected function initRelationship(Relationship $relation){
        if (!in_array($relation->getName(), $this->loadedRelationships)){
            $foreignEntityMetadata = $this->entityManager->getEntityMetadata($relation->getForeignEntity());
            // Добавляем столбцы зависимости для выборки
            //$this->queryData['columns'] = array_merge($this->queryData['columns'], $foreignEntityMetadata->getSqlColumns());
            if ($relation->isLoadOnFind()){
                $this->columns = array_merge($this->columns, $foreignEntityMetadata->getSqlColumns());
            }
            if ($relation->getType() === Relationship::MANY_MANY){
                $this->joinTable($relation->getMiddleTable(), $relation->getJoinType(),
                    $this->metadata->getFullColumnsName($relation->getParentColumns()), $relation->getParentMiddleColumns(true), $relation->getJoinOperator());

                $this->joinTable($foreignEntityMetadata->getTableName(), $relation->getJoinType(),
                    $relation->getForeignMiddleColumns(true), $foreignEntityMetadata->getFullColumnsName($relation->getForeignColumns()), $relation->getJoinOperator());

            }else{
                $this->joinTable($foreignEntityMetadata->getTableName(), $relation->getJoinType(),
                    $this->metadata->getFullColumnsName($relation->getParentColumns()), $foreignEntityMetadata->getFullColumnsName($relation->getForeignColumns()), $relation->getJoinOperator());
            }
            $this->loadedRelationships[] = $relation->getName();
        }
    }

    /**
     * @param string $tableName
     * @param int $joinType
     * @param string[] $parentColumns
     * @param string [] $foreignColumns
     * @param string $operator
     */
    protected function joinTable($tableName, $joinType, $parentColumns, $foreignColumns, $operator){
        if (!isset($this->joins[$tableName])){
            $this->joins[$tableName] = array(
                'joinType' => $joinType,
                'parentColumns' => $parentColumns,
                'foreignColumns' => $foreignColumns,
                'operator'=>$operator
            );
        }
    }

    protected function quoteCols(array $cols){
        $cols = array_map(function($identifier){
            return '`'.str_replace('.', '`.`', $identifier).'` AS '. str_replace('.', '_', ucfirst($identifier));
        }, $cols);
        return $cols;
    }

    protected function quote(){
        $args = func_get_args();
        if (count($args) == 1 && is_array($args[0])){
            $args = $args[0];
        }
        $args = array_map(function($identifier){
            return '`'.str_replace('.', '`.`', $identifier).'`';
        }, $args);
        return count($args) == 1 ? $args[0] : $args;
    }

    /**
     * @param bool $loadRelationships
     * @param bool $throwException
     * @return Entity|null
     */
    public function loadOne($loadRelationships=true, $throwException=false){
        $this->limit(1);
        $result = $this->load($loadRelationships, $throwException);
        return count($result) > 0 ? $result[0] : null;
    }

    /**
     * @param Entity $entity
     * @param bool $throwExceptions
     * @throws StatementExecuteError
     * @return Entity
     */
    public function save(Entity $entity, $throwExceptions=false){
        if (!is_a($entity, $this->metadata->getEntityClassName())){

            throw new \RuntimeException(__CLASS__.': Expected entity of class: '.$this->metadata->getEntityClassName().' Get: '.get_class($entity));
        }
        if ($entity->isNew()){
            return $this->insert($entity, $throwExceptions);
        }else{
            return $this->update($entity, $throwExceptions);
        }
    }

    /**
     * @param Entity $entity
     * @param bool $throwExceptions
     * @throws StatementExecuteError
     * @return Entity
     */
    protected function insert(Entity $entity, $throwExceptions=false){
        $sql = 'INSERT INTO '.$this->metadata->quote($this->metadata->getTableName()).'(';
        $fields = array();
        $params = array();
        foreach ($this->metadata->getFieldsMapping() as $field){
            $methodName = 'get'.$field['phpName'];
            $fields[] = $this->metadata->quote($this->metadata->getColumnName($field['sqlName']));
            $params[':'.$field['phpName']] = $entity->$methodName(null, true);
        }
        $sql .= implode(',', $fields).') VALUES ('.implode(',',array_keys($params)).')';
        $statement = $this->entityManager->getCore()->getDb()->prepare($sql);
        if (!$statement->execute($params)){
            $errorInfo = $statement->errorInfo();

            Debugger::log('DataBase Error: Error in INSERT of entity: '.$entity->_getName(). '('.$sql.') '.$errorInfo[2]);
            if ($throwExceptions) throw new StatementExecuteError($errorInfo);
            return false;
            //throw new \RuntimeException(__CLASS__.': Error in INSERT of entity: '.$entity->_getName().' '.$errorInfo[2]);
        }
        $entity->setId($this->entityManager->getCore()->getDb()->lastInsertId());
        $entity->setIsNew(false);
        return $entity;
    }

    /**
     * @param Entity $entity
     * @param bool $throwExceptions
     * @throws StatementExecuteError
     * @return Entity
     */
    protected function update(Entity $entity, $throwExceptions=false){
        $sql = 'UPDATE '.$this->metadata->quote($this->metadata->getTableName()).' SET ';
        //var_dump($sql);
        $data = array();
        $params = array();
        foreach ($this->metadata->getFieldsMapping() as $field) {
            $methodName = 'get'.$field['phpName'];
            $data[] = $this->metadata->quote($field['sqlName']).' = :'.$field['phpName'];
            $params[':'.$field['phpName']] = $entity->$methodName(null, true);
        }
        $sql .= implode(',', $data).' WHERE `id`='.$entity->getId();
        $statement = $this->entityManager->getCore()->getDb()->prepare($sql);
        if (!$statement->execute($params)){
            $errorInfo = $statement->errorInfo();
            Debugger::log('DataBase Error: Error in UPDATE of entity: '.$entity->_getName(). '('.$sql.') '.$errorInfo[2]);
            if ($throwExceptions) throw new StatementExecuteError($errorInfo);
            return false;
            //throw new \RuntimeException(__CLASS__.': Error in UPDATE of entity: '.$entity->_getName().' '.$statement->errorCode());
        }
        return $entity;
    }

    /**
     * @param Entity $entity
     * @return bool
     */
    public function deleteByEntity(Entity $entity){
        if (!is_a($entity, $this->metadata->getEntityClassName())){
            throw new \RuntimeException(__CLASS__.': Expected entity of class: '.$this->metadata->getEntityClassName().' Get: '.get_class($entity));
        }
        $sql = 'DELETE FROM '.$this->metadata->quote($this->metadata->getTableName()).' WHERE '.$this->metadata->quote($this->metadata->getColumnName('id')).' = :id';
        $statement = $this->entityManager->getCore()->getDb()->prepare($sql);
        if (!$statement->execute(array(':id'=>$entity->getId()))){
            throw new \RuntimeException(__CLASS__.': Error in DELETE of entity: '.$entity->_getName().' '.$statement->errorCode());
        }
        return true;
    }

    /**
     * @param bool $safeDelete
     * @param bool $throwException
     * @throws StatementExecuteError
     * @throws UnsafeDelete
     * @return bool
     */
    public function delete($safeDelete=true, $throwException=false){
        $whereStr = $this->compileGroup();
        $whereStr = $whereStr == '()' ? '' : 'WHERE '.$whereStr;
        if (!$safeDelete || !empty($whereStr)){
            $deps = $this->entityManager->getEntityDependency($this->metadata->getName());
            foreach ($deps as $rel){
                $parentMetadata = $this->entityManager->getEntityMetadata($rel->getParentEntity());
                $parentTableName = $parentMetadata->quote($parentMetadata->getTableName());

                $joins = array();
                if ($rel->getType() === Relationship::MANY_MANY){
                    //Не известно, работает или нет
                    $joins[$rel->getMiddleTable()] = array(
                        'joinType' => $rel->getJoinType(),
                        'parentColumns' => $this->metadata->getFullColumnsName($rel->getForeignColumns()),
                        'foreignColumns' => $rel->getParentMiddleColumns(true),
                        'operator'=>$rel->getJoinOperator()
                    );
                    $joins[$this->metadata->getTableName()] = array(
                        'joinType' => $rel->getJoinType(),
                        'parentColumns' => $rel->getForeignMiddleColumns(true),
                        'foreignColumns' => $this->metadata->getFullColumnsName($rel->getForeignColumns()),
                        'operator'=>$rel->getJoinOperator()
                    );
                    //TODO: Fix it!
                }else{
                    $joins[$this->metadata->getTableName()] = array(
                        'joinType' => $rel->getJoinType(),
                        'parentColumns' => $this->metadata->getFullColumnsName($rel->getForeignColumns()),
                        'foreignColumns' => $parentMetadata->getFullColumnsName($rel->getParentColumns()),
                        'operator'=>$rel->getJoinOperator()
                    );
                }
                switch ($rel->getOnForeignDelete()){
                    case Relationship::BEHAVIOUR_SET_NULL:


                        $stmParams = array();
                        $cols = array();
                        $parentColumns = $rel->getParentColumns();
                        for($i=0;$i<count($parentColumns);$i++){
                            $cols[] = $parentMetadata->quote($parentMetadata->getFieldFullSqlName($parentColumns[$i], 'sqlName')).'=:p'.$i;
                            $stmParams[':p'.$i] = null;
                        }
                        $columnsStr = implode(', ', $cols);

                        $joinArray = array();
                        $joinTypes = array(
                            Relationship::INNER_JOIN => 'INNER JOIN',
                            Relationship::LEFT_JOIN => 'LEFT JOIN',
                            Relationship::RIGHT_JOIN => 'RIGHT JOIN'
                        );
                        foreach ($joins as $table=>$data){
                            $joinArray[] = $joinTypes[$data['joinType']].' '.$this->metadata->quote($table).' ON '.implode(' AND ', array_map(function($leftColumn, $rightColumn)use($data){
                                    return $leftColumn.$data['operator'].$rightColumn;
                                }, $data['parentColumns'], $data['foreignColumns']));
                        }
                        $joinStr = implode(' ', $joinArray);

                        $sql = "UPDATE {$parentTableName} {$joinStr} SET {$columnsStr} {$whereStr}";
                        $stmParams = array_merge($stmParams, $this->whereCondition->getParameters());
                        $statement = $this->entityManager->getCore()->getDb()->prepare($sql);
                        if (!$statement->execute($stmParams)){
                            if ($throwException) throw new StatementExecuteError($statement->errorInfo());
                            return false;
                        }
                        break;
                    case Relationship::BEHAVIOUR_DELETE:

                        //$sql = "DELETE FROM {$parentTableName}"
                        break;
                    case Relationship::BEHAVIOUR_NOTHING:
                    default:
                        break;

                }
                //echo implode(', ', array($rel->getName(), $rel->getParentEntity(), $rel->getOnForeignDelete())).'<br>';
            }



            foreach ($this->metadata->getRelationships() as $rel){

            }

            $sql = 'DELETE FROM '.$this->metadata->quote($this->metadata->getTableName()).$whereStr;
            $statement = $this->entityManager->getCore()->getDb()->prepare($sql);
            if (!$statement->execute($this->whereCondition->getParameters())){
                //if ($throwException) throw new \RuntimeException(__CLASS__.': Error in DELETE of entity by query: '.$statement->errorCode());
                if ($throwException) throw new StatementExecuteError($statement->errorInfo());
                return false;
            }
            $this->reset();
            return true;
        }
        if ($throwException) throw new UnsafeDelete();
        return false;
    }

    /**
     * Очищает таблицу сущности
     * @return bool
     */
    public function clear(){
        $sql = 'TRUNCATE TABLE '.$this->metadata->quote($this->metadata->getTableName());
        $statement = $this->entityManager->getCore()->getDb()->prepare($sql);
        if (!$statement->execute()){
            throw new \RuntimeException(__CLASS__.': Error in TRUNCATE of entity: '.$statement->errorCode());
        }
        return true;
    }

    protected function createQueryData(){
        $this->queryData = array(
            'findBy'=>array(),
            'orderBy'=>array(),
            'groupBy'=>array(),
            'columns'=>array(),
            'tableName'=>null,
            'initializedRelations'=>array(),

            'where'=>array(),
            'join'=>array(),
            'queries'=>array(),

            'limit'=>array('count'=>null, 'offset'=>null)
        );
        $this->setQueryDataProcessed(false);
    }

    public function getMetadata(){
        return $this->metadata;
    }

    /**
     * $data = [
     * _orderBy=>[[<PhpName>, <Direction>], ...],
     * _groupBy=>[<PhpName>, ...],
     * _count=><int>
     * <PhpName>=>[<value>, {operator (default "=")}] ||,
     * <PhpName>=><value>,
     * <Relationship>=><sameObject>
     * ]
     * @param \stdClass $data
     * @return $this
     */
    public function buildQueryFromObject($data){
        foreach ($data as $key=>$value){
            if (substr($key,0,1) == '_'){
                $m = substr($key,1);

                switch ($m){
                    case 'orderBy':
                        if (!is_array($value)){
                            Debugger::log(__CLASS__.'::buildQuery: orderBy value must be an array. Giving: '.gettype($value));
                            break;
                        }
                        foreach ($value as $stm){
                            if (!is_array($stm) || count($stm) != 2){
                                Debugger::log(__CLASS__.'::buildQuery: orderBy value item must be an array and have 2 elements.');
                                continue;
                            }
                            $methodName = 'orderBy'.$stm[0];
                            $this->$methodName($stm[1]);
                        }
                        break;
                    case 'groupBy':
                        if (!is_array($value)){
                            Debugger::log(__CLASS__.'::buildQuery: groupBy value must be an array. Giving: '.gettype($value));
                            break;
                        }
                        foreach ($value as $stm){
                            $methodName = 'groupBy'.$stm;
                            $this->$methodName();
                        }
                        break;
                    case 'count':
                        if (ctype_digit(strval($value))){
                            $this->limit($value);
                        }
                        break;
                    default:
                        Debugger::log(__CLASS__.'::buildQuery: unsupported special method: '.$m);
                        break;
                }
                continue;
            }
            $methodName = 'findBy'.$key;
            if (!is_null($this->getMetadata()->getRelationship($key)) && is_object($value)){
                $this->buildQueryFromObject($value, $this->$methodName());
                continue;
            }else{
                if (is_array($value)){
                    //Если 2 эл-та в массиве то 1-ый значение, а второй оператор.
                    //Если нет, то предполагаем, что там только один элемент и оператор - это равно
                    if (count($value) == 2){
                        $this->$methodName($value[0], $value[1]);
                    }else{
                        $this->$methodName($value[0]);
                    }

                }elseif (is_scalar($value)){
                    $this->$methodName($value);
                }
                continue;
            }
        }
        return $this;
    }
}