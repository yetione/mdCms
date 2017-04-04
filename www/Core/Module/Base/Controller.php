<?php
namespace Core\Module\Base;


use Core\DataBase\Exception\StatementExecuteError;
use Core\DataBase\Model\EntityQuery;
use Core\Debugger;
use Modules\Users\Users;

abstract class Controller {

    /**
     * @var Module
     */
    protected $module;

    public function __construct(Module $module){
        $this->module = $module;
        $this->init();
    }

    protected function init(){

    }

    /**
     * @return \Core\Input
     */
    protected function getInput(){
        return $this->module->getCore()->getInput();
    }

    /**
     * @return \Core\DataBase\EntityManager
     */
    protected function getEntityManager(){
        return $this->module->getCore()->getEntityManager();
    }

    /**
     * @param string $action
     * @param string $entityName
     * @throws \Core\Session\Exception\StateError
     * @return bool
     */
    protected function checkUser($action, $entityName){
        $user = $this->module->getCore()->getSession()->get(Users::CURRENT_USER_KEY);
        $entAccessFlag = $this->module->getCore()->getEntityManager()->getEntityMetadata($entityName)->getAccessFlags();
        foreach ($entAccessFlag[$action] as $af){
            if (!$user->hasFlag($af)){
                return false;
            }
        }
        return true;
    }

    /**
     * @param string $entity
     * @param array $fields
     * @throws StatementExecuteError
     * @return array
     */
    protected function selectFields($entity, $fields){
        $em = $this->module->getCore()->getEntityManager();

        $metadata = $em->getEntityMetadata($entity);

        $tableName = $metadata->getTableName();
        $sqlFields = array();
        $t = null;
        foreach ($fields as $field){
            $sqlFields[] = $metadata->quote($metadata->getFieldFullSqlName($field));
        }

        $sql = "SELECT ".implode(', ', $sqlFields).' FROM '.$metadata->quote($tableName);
        $stm = $this->module->getCore()->getDb()->prepare($sql);
        if (!$stm->execute()){
            throw new StatementExecuteError($stm->errorInfo());
        }

        $result = [];
        while ($row = $stm->fetch(\PDO::FETCH_NUM)){
            $result[] = $this->rowAsArray($row, $fields);
        }
        return $result;
    }

    protected function rowAsArray($row, $fields){
        $result = array();
        for($i=0;$i<count($fields);$i++){
            $result[$fields[$i]] = $row[$i];
        }
        return $result;
    }

    /**
     * $data = [
     * _orderBy=>[[<PhpName>, <Direction>], ...],
     * _groupBy=>[<PhpName>, ...],
     * <PhpName>=>[<value>, {operator (default "=")}] ||,
     * <PhpName>=><value>,
     * <Relationship>=><sameObject>
     * ]
     * @param \stdClass $data
     * @param EntityQuery $query
     * @return EntityQuery
     */
    protected function buildQuery($data, $query){
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
                            $query->$methodName($stm[1]);
                        }
                        break;
                    case 'groupBy':
                        if (!is_array($value)){
                            Debugger::log(__CLASS__.'::buildQuery: groupBy value must be an array. Giving: '.gettype($value));
                            break;
                        }
                        foreach ($value as $stm){
                            $methodName = 'groupBy'.$stm;
                            $query->$methodName();
                        }
                        break;
                    case 'count':
                        /*if (!ctype_digit($value)){
                            Debugger::log(__CLASS__.'::buildQuery: count value must be an integer. Giving: '.$value);
                            break;
                        }*/
                        $query->limit($value);
                        break;
                    default:
                        Debugger::log(__CLASS__.'::buildQuery: unsupported special method: '.$m);
                        break;
                }
                continue;
            }
            $methodName = 'findBy'.$key;
            if (!is_null($query->getMetadata()->getRelationship($key)) && is_object($value)){
                $this->buildQuery($value, $query->$methodName());
                continue;
            }else{
                if (is_array($value)){
                    //Если 2 эл-та в массиве то 1-ый значение, а второй оператор.
                    //Если нет, то предполагаем, что там только один элемент и оператор - это равно
                    if (count($value) == 2){
                        $query->$methodName($value[0], $value[1]);
                    }else{
                        $query->$methodName($value[0]);
                    }

                }elseif (is_scalar($value)){
                    $query->$methodName($value);
                }
                continue;
            }
        }
        return $query;
    }

    public function execute($action, array $data=array()){
        if (method_exists($this, $action)){
            $this->$action($data);
        }else{
            $this->unknown($action, $data);
        }
    }

    public function unknown($action, array $data){
        return false;
    }

    /**
     * @param string $entityName
     * @param string $inputVariable
     * @return EntityQuery
     */
    protected function getQueryFromInput($entityName, $inputVariable){
        $em = $this->module->getCore()->getEntityManager();
        $input = $this->module->getCore()->getInput();
        $query = $em->getEntityQuery($entityName);

        $params = $input->get($inputVariable, null, TYPE_JSON);
        if ($params instanceof \stdClass && !empty(get_object_vars($params))){
            $query = $this->buildQuery($params, $query);
        }
        return $query;
    }
}