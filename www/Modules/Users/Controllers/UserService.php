<?php
namespace Modules\Users\Controllers;


use Core\DataBase\Exception\StatementExecuteError;
use Core\Module\Base\Controller;
use Modules\Users\Users;

class UserService extends Controller{

    /**
     * @var Users
     */
    protected $module;

    protected $vkRedirectUri = 'http://sektafood.ru/login?service=vk';

    protected $fbRedirectUri = 'http://sektafood.ru/login?service=fb';



    public function getVkLoginLink(array $data){
        $this->module->view('Simple')->render([
            'vk'=>$this->module->getVkApi()->generateAuthUrl($this->vkRedirectUri),
            'fb'=>$this->module->getFbApi()->generateAuthUrl($this->fbRedirectUri),
        ]);
    }

    public function getCurrentUser(array $data){
        //$currentUser = $this->module->getCurrentUser();
        $view = $this->module->view('CurrentUser');
        $view->render($this->module->getJSCurrentUser());
    }

    public function getCurrentUserAddresses(array $data){
        $user = $this->module->getCurrentUser();
        $addresses = $user->getAddress();
        $view = $this->module->view('Simple');
        $view->render($addresses->toArray());
    }

    public function deleteCurrentUserAddress(array $data){
        $em = $this->getEntityManager();
        $ent = $em->getEntity('UserAddress');
        $params = json_decode(trim($this->getInput()->get('data', null, TYPE_RAW)), true);
        unset($params['User']);
        unset($params['IsEdit']);
        $ent->fromArray($params);
        $currentUser = $this->module->getCurrentUser();
        if ($ent->getUserId() != $currentUser->getId()){
            $view = $this->module->view('Error');
            $view->render(['error'=>'User id is no valid', 'code'=>1]);
            return;
        }if (!$ent->getId()){
            $view = $this->module->view('Error');
            $view->render(['error'=>'Entity id is not set', 'code'=>2]);
            return;
        }
        $query = $em->getEntityQuery('UserAddress');
        $query->findById($ent->getId());
        try{
            $query->delete(true, true);
            $cU = $em->getEntityQuery('User')->findById($currentUser->getId())->loadOne();
            $this->module->setCurrentUser($cU);
            $view = $this->module->view('Simple');
            $view->render($cU->getAddress()->toArray());
            return;
        }catch (StatementExecuteError $e){
            $view = $this->module->view('Error');
            $view->render(['error'=>$e->getErrorData(), 'code'=>3]);
            return;
        }

    }

    public function saveCurrentUserAddress(array $data){
        $em = $this->module->getCore()->getEntityManager();
        $ent = $em->getEntity('UserAddress');
        $params = json_decode(trim($this->getInput()->get('data', null, TYPE_RAW)), true);
        unset($params['User']);
        unset($params['IsEdit']);
        //var_dump($params);
        $ent->fromArray($params);
        //$params = json_decode(trim(file_get_contents('php://input')), true);
        //$ent->fromArray($params['data']);
        //var_dump($ent->toArray());
        $currentUser = $this->module->getCurrentUser();
        //var_dump($params);
        if ($ent->getUserId() != $currentUser->getId()){
            $view = $this->module->view('Simple');
            $view->render(['error'=>'User id is no valid']);
            return;
        }
        if (!$ent->getId()){
            $ent->setIsNew(true);
        }

        $result = $em->getEntityQuery('UserAddress')->save($ent);
        $cU = $em->getEntityQuery('User')->findById($currentUser->getId())->loadOne();
        $this->module->setCurrentUser($cU);
        $view = $this->module->view('Simple');
        //$r = array_merge_recursive($this->module->getJSCurrentUser(), array('Addresses'=>[count($cU->getAddress())]));
        //var_dump($r);
        //var_dump($cU->getAddress());

        //var_dump(method_exists($a[0], 'toArray'));
        $view->render($cU->getAddress()->toArray());
    }

    public function getUserOrders(array $data){
        $cU = $this->module->getCurrentUser();
        $em = $this->getEntityManager();
        if (!$cU->getId()){
            $view = $this->module->view('Simple');
            $view->render([]);
            return;
        }
        $query = $em->getEntityQuery('Order');
        try{
            $r = $query->findByUserId($cU->getId())->load(false, true);
            $view = $this->module->view('Simple');
            $view->render($r->toArray());
        }catch (StatementExecuteError $e){
            $view = $this->module->view('Error');
            $view->render($e->getErrorData());
        }

    }

    public function updateData(array $data){
        $input = $this->getInput();
        $cU = $this->module->getCurrentUser();
        if (!$cU->getId()){
            $view = $this->module->view('Error');
            $view->render(['code'=>1, 'message'=>'Current user not found']);
            return;
        }
        $em = $this->getEntityManager();
        $query = $em->getEntityQuery('User');
        $data = $input->get('Data', null, TYPE_RAW);
        if (is_null($data)){
            $view = $this->module->view('Error');
            $view->render(['code'=>2, 'message'=>'Data is not valid']);
            return;
        }
        $data = \json_decode($data, true);
        if (QS_validate($data['Email'], TYPE_EMAIL)){
            $cU->setEmailt($data['Email']);
        }else{
            $view = $this->module->view('Error');
            $view->render(['code'=>3, 'message'=>'Email is not valid']);
            return;
        }
        if (preg_match('(\d{10})', $data['Phone']) === 1){
            $cU->setPhone($data['Phone']);
        }else{
            $view = $this->module->view('Error');
            $view->render(['code'=>4, 'message'=>'Phone is not valid']);
            return;
        }
        if (!empty(trim($data['Name']))){
            $cU->setName(trim($data['Name']));
            $cU->setSurname(trim($data['Surname']));
            $cU->setPatronymic(trim($data['Patronymic']));
        }else{
            $view = $this->module->view('Error');
            $view->render(['code'=>5, 'message'=>'Name is not valid']);
            return;
        }
        try{
            $query->save($cU, true);
        }catch (StatementExecuteError $e){
            $view = $this->module->view('Error');
            $view->render(['code'=>6, 'message'=>'Cant save user', 'data'=>$e->getErrorData()]);
            return;
        }
        $view = $this->module->view('CurrentUser');
        $view->render($this->module->getJSCurrentUser());
        return;


    }
} 