<?php
namespace Modules\Food\Controllers\Admin;


use Core\Module\Base\Controller;

class ProductTypes extends Controller{


    public function getItem(array $data){
        $input = $this->module->getCore()->getInput();
        $id = $input->get('typeId', 0, TYPE_INT);
        $em = $this->module->getCore()->getEntityManager();

        $query = $em->getEntityQuery('ProductType');
        $query->findById($id);
        $type = $query->loadOne();

        $view = $this->module->view('Admin\\Item');
        $view->render($type);
    }

    public function getList(array $data){
        $input = $this->module->getCore()->getInput();
        $id = $input->get('categoryId', 0, TYPE_INT);
        $em = $this->module->getCore()->getEntityManager();
        $query = $em->getEntityQuery('ProductType');
        if ($id > 0){
            $query->findByCategoryId($id);
        }
        $types = $query->load();
        $view = $this->module->view('Admin\\EntitiesList');
        $view->render($types);
    }

    public function saveItem(array $data){
        $params = json_decode(trim(file_get_contents('php://input')), true);
        $em = $this->module->getCore()->getEntityManager();
        $ent = $em->getEntity('ProductType');
        $ent->fromArray($params['type']);

        if (!$ent->getId()){
            $ent->setIsNew(true);
        }

        $result = $em->getEntityQuery('ProductType')->save($ent);

        $view = $this->module->view('Admin\\ProductSave');
        $view->render($params, $result);
    }

    public function deleteItem(array $data){
        $input = $this->module->getCore()->getInput();
        $id = $input->get('typeId', 0, TYPE_INT);

        $em = $this->module->getCore()->getEntityManager();
        $query = $em->getEntityQuery('Product');
        $query->findByTypeId($id);
        $query->delete();
        $query = $em->getEntityQuery('ProductType');
        $query->findById($id);
        $result = $query->delete();
        $view = $this->module->view('Admin\\ProductDelete');
        $view->render($result);
    }
} 