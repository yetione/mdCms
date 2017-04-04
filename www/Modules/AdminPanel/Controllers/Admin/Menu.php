<?php
namespace Modules\AdminPanel\Controllers\Admin;


use Core\Module\Base\Controller;

class Menu extends Controller{

    /**
     * @var \Modules\AdminPanel\AdminPanel
     */
    protected $module;

    public function getItems(array $data){
        $items = $this->module->getAdminMenuItems();
        $view = $this->module->view('MenuItems');
        $view->render($items);
    }
} 