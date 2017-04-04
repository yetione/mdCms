<?php
namespace Modules\Restful\Controllers\Admin;

use Core\DataBase\Model\EntityQuery;
use Core\Module\Base\Controller;

class Entity extends Controller{

    /**
     * @param string $name
     * @return bool
     */
    protected function isValidEntity($name){
        return !is_null($this->module->getCore()->getEntityManager()->getEntityMetadata($name));
    }



    /**
     * @param $data
     * @param EntityQuery $query
     * @return \Core\DataBase\Model\EntityQuery
     */
    protected function buildQuery($data, $query){
        foreach ($data as $key=>$value){
            $methodName = 'findBy'.$key;
            if (!is_null($query->getMetadata()->getRelationship($key)) && is_object($value)){
                $relationshipQuery = $query->$methodName();
                $this->buildQuery($value, $relationshipQuery);
            }else{
                if (is_array($value)){
                    if (count($value) == 2){

                    }
                    //Если 2 эл-та в массиве то 1-ый значение, а второй оператор. Если нет, то предполагаем, что там только один элемент и оператор - это равно
                    call_user_func_array([$query, $methodName], [count($value) == 2 ? $value : [$value[0], '=']]);
                }elseif (is_scalar($value)){
                    $query->$methodName($value);
                }
                continue;
            }
            throw new \RuntimeException(__CLASS__.'::buildQuery: can not build query condition for key: '.$key);
        }
        return $query;
    }

    public function getItem(array $data){
        $input = $this->module->getCore()->getInput();
        $entityName = $input->get('entity', null, TYPE_STRING);
        try{
            $em = $this->module->getCore()->getEntityManager();
            $query = $em->getEntityQuery($entityName);
        } catch (\RuntimeException $e){
            $view = $this->module->view('Admin\\Error');
            $view->render('Invalid entity name: '.$entityName, 404);
            return;
        }

        $params = $input->get('params', null, TYPE_RAW);
        $params = is_null($params) ? new \stdClass() : json_decode($params);
        //print_r($params);
        try{
            $query = $this->buildQuery($params, $query);
        }catch (\Exception $e){
            $view = $this->module->view('Admin\\Error');
            $view->render($e->getMessage(), 404);
            return;
        }


        /*

        foreach ($params as $key => $value){
            print_r($key);
            try{
                $methodName = 'findBy'.$key;
                $query->$methodName($value);
            }catch (\Exception $e){
                $view = $this->module->view('Admin\\Error');
                $view->render($e->getMessage(), 404);
                return;
            }
        }
        */
        $item = $query->loadOne(false);

        $view = $this->module->view('Admin\\EntityItem');
        $view->render($item);
    }

    public function getList(array $data){
        $input = $this->module->getCore()->getInput();
        $entityName = $input->get('entity', null, TYPE_STRING);
        try{
            $em = $this->module->getCore()->getEntityManager();
            $query = $em->getEntityQuery($entityName);
        } catch (\RuntimeException $e){
            $view = $this->module->view('Admin\\Error');
            $view->render('Invalid entity name: '.$entityName, 404);
            return;
        }

        $params = $input->get('params', null, TYPE_RAW);
        $params = is_null($params) ? [] : json_decode($params, true);
        foreach ($params as $key => $value){
            try{
                $methodName = 'findBy'.$key;
                $query->$methodName($value);
            }catch (\Exception $e){
                $view = $this->module->view('Admin\\Error');
                $view->render($e->getMessage(), 404);
                return;
            }
        }
        $item = $query->loadOne();

        $view = $this->module->view('Admin\\EntityItem');
        $view->render($item);
    }

    public function getEmptyEntity(array $data){
        $input = $this->module->getCore()->getInput();
        $entityName = $input->get('entity', null, TYPE_STRING);

        $em = $this->module->getCore()->getEntityManager();
        try{
            $ent = $em->getEntity($entityName);
            $view = $this->module->view('Admin\\EntityItem');
            $view->render($ent);
        }catch (\Exception $e){
            $view = $this->module->view('Admin\\Error');
            $view->render($e->getMessage(), 404);
        }
    }


    public function getNewEntity(array $data){

    }
} 