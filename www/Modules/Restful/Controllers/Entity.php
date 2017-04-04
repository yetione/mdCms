<?php
namespace Modules\Restful\Controllers;


use Core\DataBase\Exception\StatementExecuteError;
use Core\Module\Base\Controller;
use Modules\Kernel\Kernel;
use Modules\Users\Users;

class Entity extends Controller{

    /**
     * @var \Modules\Restful\Restful
     */
    protected $module;

    /**
     * @var string
     */
    protected $entityName;


    /**
     * @param string $action
     * @return bool
     * @throws \Core\Session\Exception\StateError
     */
    protected function checkUser($action){
        $user = $this->module->getCore()->getSession()->get(Users::CURRENT_USER_KEY);
        $entAccessFlag = $this->module->getCore()->getEntityManager()->getEntityMetadata($this->entityName)->getAccessFlags();
        foreach ($entAccessFlag[$action] as $af){
            if (!$user->hasFlag($af)){
                return false;
            }
        }
        return true;
    }

    public function execute($action, array $data=array()){
        if (method_exists($this, $action)){
            $this->$action($data);
        }else{
            $this->unknown($action, $data);
        }
    }

    /**
     * @return \Core\DataBase\Model\EntityQuery
     */
    protected function getQuery(){
        $em = $this->module->getCore()->getEntityManager();
        $input = $this->module->getCore()->getInput();
        $query = $em->getEntityQuery($this->entityName);

        $params = $input->get('params', null, TYPE_JSON);
        if ($params instanceof \stdClass && !empty(get_object_vars($params))){
            $query = $this->buildQuery($params, $query);
        }
        return $query;
    }

    public function getList(array $data){
        if (!$this->checkUser(Kernel::ENTITY_GET)){
            $view = $this->module->view('AccessDenied');
            $view->render();
            return;
        }
        $query = $this->getQuery();
        $loadRelationships = $this->module->getCore()->getInput()->get('LoadRelationships', 0, TYPE_INT);
        try{
            $items = $query->load((bool) $loadRelationships, true);
            $view = $this->module->view('EntitiesList');
            $view->render($items);
        }catch (StatementExecuteError $e){
            $view = $this->module->view('Error');
            $view->render(array('code'=>1,'message'=>"Error when try to load {$this->entityName} list.",'info'=>$e->getErrorData()));
        }

    }

    public function getItem(array $data){
        if (!$this->checkUser(Kernel::ENTITY_GET)){
            $view = $this->module->view('AccessDenied');
            $view->render();
            return;
        }
        $query = $this->getQuery();
        $loadRelationships = $this->module->getCore()->getInput()->get('LoadRelationships', 1, TYPE_INT);
        try{
            $result = $query->loadOne((bool) $loadRelationships, true);
            if (is_null($result)){
                $view = $this->module->view('Error');
                $view->render(array('code'=>2,'message'=>'Entity not found'));
                return;
            }
            $view = $this->module->view('Entity');
            $view->render($result);
        }catch (StatementExecuteError $e){
            $view = $this->module->view('Error');
            $view->render(array('code'=>1,'message'=>"Error when try to load {$this->entityName} item.", 'info'=>$e->getErrorData()));
        }
    }

    public function getEmpty(array $data){
        if (!$this->checkUser(Kernel::ENTITY_GET)){
            $view = $this->module->view('AccessDenied');
            $view->render();
            return;
        }
        $em = $this->module->getCore()->getEntityManager();
        $entity = $em->getEntity($this->entityName);
        $view = $this->module->view('Entity');
        $view->render($entity);
    }

    public function saveItem(array $data){
        if (!$this->checkUser(Kernel::ENTITY_UPDATE)){
            $view = $this->module->view('AccessDenied');
            $view->render();
            return;
        }
        $params = json_decode(trim(file_get_contents('php://input')), true);
        $em = $this->module->getCore()->getEntityManager();

        $ent = $em->getEntity($this->entityName);
        $ent->fromArray($params['entity']);
        if (!$ent->getId()){
            $ent->setIsNew(true);
        }
        try{
            $result = $em->getEntityQuery($this->entityName)->save($ent, true);
            $view = $this->module->view('Entity');
            $view->render($result);
        }catch (StatementExecuteError $e){
            $status = $e->getErrorData();
            $view = $this->module->view('Error');
            $view->render($status);
        }
    }

    public function deleteItem(array $data){
        if (!$this->checkUser(Kernel::ENTITY_DELETE)){
            $view = $this->module->view('AccessDenied');
            $view->render();
            return;
        }
        $input = $this->module->getCore()->getInput();
        $params = $input->get('params', null, TYPE_JSON);
        if ($params instanceof \stdClass && !empty(get_object_vars($params))){

        }
        $pId = $input->get('id', 0, TYPE_STRING);
        try{
            $em = $this->module->getCore()->getEntityManager();
            $query = $em->getEntityQuery($this->entityName);
            $query->findById($pId);
            $result = $query->delete(true, true);
            $view = $this->module->view('ItemDelete');
            $view->render($result);
        }catch (StatementExecuteError $e){
            $status = $e->getErrorData();
            $view = $this->module->view('Error');
            $view->render($status);
        }

    }

} 