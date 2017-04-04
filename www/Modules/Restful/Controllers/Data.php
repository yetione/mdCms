<?php
namespace Modules\Restful\Controllers;

use Core\DataBase\Exception\StatementExecuteError;
use Core\Module\Base\Controller;

class Data extends Controller{


    public function data(array $d){
        $entityName = $d['entity'];
        $em = $this->module->getCore()->getEntityManager();
        try{
            $input = $this->module->getCore()->getInput();
            $query = $em->getEntityQuery($entityName);

            $params = $input->get('params', null, TYPE_RAW);
            $params = is_null($params) ? new \stdClass() : json_decode($params);

            $query->buildQueryFromObject($params);

            $loadIds = $input->get('load_ids', null, TYPE_JSON);

            if (!is_null($loadIds)){
                $query->findById($loadIds, 'IN');
            }

            try{
                $result = $query->load(false, true);
                $view = $this->module->view('EntitiesList');
                $view->render($result);
            }catch (StatementExecuteError $e){
                $view = $this->module->view('Error');
                $view->render(array('message'=>$e->getErrorData()));
            }
        }catch (\RuntimeException $e){
            $view = $this->module->view('Error');
            $view->render(array('message'=>'Entity name invalid'));
        }
    }
} 