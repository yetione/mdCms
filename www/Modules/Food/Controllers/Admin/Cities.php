<?php
namespace Modules\Food\Controllers\Admin;


use Core\Module\Base\Controller;

class Cities extends Controller{

    public function getList(array $data){
        $em = $this->module->getCore()->getEntityManager();

        $query = $em->getEntityQuery('City');
        $categories = $query->load();

        $view = $this->module->view('Admin\\EntitiesList');
        $view->render($categories);
    }

} 