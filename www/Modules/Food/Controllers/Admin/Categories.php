<?php
namespace Modules\Food\Controllers\Admin;


use Core\Module\Base\Controller;

class Categories extends Controller{


    public function getList(array $data){
        $em = $this->module->getCore()->getEntityManager();

        $query = $em->getEntityQuery('Category');
        $categories = $query->load();

        //$view = $this->module->view('Admin\\CategoriesList');
        $view = $this->module->view('Admin\\EntitiesList');
        $view->render($categories);
    }

    public function getCategoryData(array $data){
        $catId = $this->module->getCore()->getInput()->get('catId', 0, TYPE_INT);
        $em = $this->module->getCore()->getEntityManager();

        $query = $em->getEntityQuery('Category');
        $query->findById($catId);
        $category = $query->loadOne();


        $query = $em->getEntityQuery('Product');
        $query->findByCategory()->findById($catId);
        $products = $query->load();


        $query = $em->getEntityQuery('ProductType');
        $productType = $query->load();

        $view = $this->module->view('Admin\\CategoryItem');
        $view->render($category, $products, $productType);
    }
} 