<?php
namespace Modules\Users\Controllers;


use Core\Module\Base\Controller;
use Modules\Users\Users;

class Profile extends Controller{

    /**
     * @var Users
     */
    protected $module;

    public function page(array $data){
        if (!$this->module->getCurrentUser()->getId()){
            $this->module->getResponse()->redirect('login');
        }
        $this->module->getResponse()->setTitle('Личный кабинет');
        $view = $this->module->view('Profile');
        $view->render($this->module->getVkApi()->generateAuthUrl($this->vkRedirectUri));
    }
} 