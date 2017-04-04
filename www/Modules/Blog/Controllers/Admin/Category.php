<?php
namespace Modules\Blog\Controllers\Admin;


use Core\DataBase\Exception\StatementExecuteError;
use Core\Module\Base\Controller;
use Core\Router\Route;
use Modules\Kernel\Kernel;
use Modules\Users\Users;
use Utils\NameGenerator;

class Category extends Controller{

    public function getList(array $data){
        if (!$this->checkUser(Kernel::ENTITY_GET, 'BlogCategory')){
            $view = $this->module->view('AccessDenied');
            $view->render();
            return;
        }
        $query = $this->getQueryFromInput('BlogCategory', 'params');
        $loadRelationships = $this->module->getCore()->getInput()->get('LoadRelationships', 0, TYPE_INT);
        try{
            $items = $query->load((bool) $loadRelationships, true);
            $view = $this->module->view('EntitiesList');
            $view->render($items);
        }catch (StatementExecuteError $e){
            $view = $this->module->view('Error');
            $view->render(array('code'=>1,'message'=>'Error in load entities list.','info'=>$e->getErrorData()));
        }
    }

    public function getItem(array $data){
        if (!$this->checkUser(Kernel::ENTITY_GET, 'BlogCategory')){
            $view = $this->module->view('AccessDenied');
            $view->render();
            return;
        }
        $loadRelationships = $this->module->getCore()->getInput()->get('LoadRelationships', 1, TYPE_INT);

        $query = $this->getQueryFromInput('BlogCategory', 'params');
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
            $view->render(array('code'=>1,'message'=>'Error in load entity item.','info'=>$e->getErrorData()));
        }
    }

    public function saveCategory(array  $data){
        if (!$this->checkUser(Kernel::ENTITY_UPDATE, 'BlogCategory')){
            $view = $this->module->view('AccessDenied');
            $view->render();
            return;
        }

        //$input = $this->getInput();
        //$category = json_decode(trim($input->get('data', null, TYPE_RAW)), true);

        $category = json_decode(trim(file_get_contents('php://input')), true)['data'];
        $currentUser = $this->module->getCore()->getSession()->get(Users::CURRENT_USER_KEY);
        $em = $this->getEntityManager();
        $ent = $em->getEntity('BlogCategory');
        $ent->fromArray($category);
        if (!$ent->getName()){
            $view = $this->module->view('Error');
            $view->render(['code'=>1, 'message'=>'Не заполнены обязателные поля.']);
            return;
        }
        if (!$ent->getId()){
            $old = $em->getEntity('BlogCategory');
            $ent->setIsNew(true);
            $ent->setCreationDate(time());
            $ent->setCreatorId($currentUser->getId());
            $ent->setPostsCount(0);
            $ent->setPublicPostsCount(0);
        }else{
            $old = $em->getEntityQuery('BlogCategory')->findById($ent->getId())->loadOne();
            if (!$old){
                $view = $this->module->view('Error');
                $view->render(['code'=>2, 'message'=>'Категория не найдена.']);
                return;
            }
        }
        $ent->setUpdateDate(time());
        $ent->setUpdaterId($currentUser->getId());
        if (!$ent->getUrl()){
            $ent->setUrl(NameGenerator::transliterate($ent->getName()));
        }

        if (!$ent->getParentId()){
            $ent->setParentId(0);
        }
        if (!$ent->getHtmlTitle()){
            $ent->setHtmlTitle($ent->getName());
        }

        try{
            $ent = $em->getEntityQuery('BlogCategory')->save($ent, true);
        }catch (StatementExecuteError $e){
            $view = $this->module->view('Error');
            $view->render($e->getErrorData());
            return;
        }
        $newUrl = $ent->getUrl();
        if ($old->getUrl() !== $newUrl){
            $router = $this->module->getCore()->getRouter();
            if ($old->getUrl()){
                $router->remove('CategoryPage'.$old->getUrl());
                $router->remove('PostPage'.$old->getUrl());
            }
            $route = new Route($newUrl, array('module'=>'Blog', 'controller'=>'Category', 'action'=>'showCategory', 'methods'=>array('GET')));
            $route->setName('CategoryPage'.$newUrl);
            $router->add($route);

            $route = new Route($newUrl.'/:slug', array('module'=>'Blog', 'controller'=>'Post', 'action'=>'showPost', 'methods'=>array('GET')));
            $route->setName('PostPage'.$newUrl);
            $router->add($route);
        }
        $view = $this->module->view('Entity');
        $view->render($ent);
    }

    public function deleteItem(array  $data){
        if (!$this->checkUser(Kernel::ENTITY_DELETE, 'BlogCategory')){
            $view = $this->module->view('AccessDenied');
            $view->render();
            return;
        }
        $input = $this->getInput();
        $id = $input->get('Id', null, TYPE_INT);
        if (is_null($id) || $id < 1){
            $view = $this->module->view('Error');
            $view->render(['code'=>1, 'message'=>'Id должен быть больше нуля.']);
            return;
        }
        $em = $this->getEntityManager();
        $query = $em->getEntityQuery('BlogCategory');
        try{
            $ent = $query->findById($id)->loadOne(false, true);
            if (is_null($ent)){
                throw new StatementExecuteError(['code'=>2, 'message'=>'Категория не найдена']);
            }
            $router = $this->module->getCore()->getRouter();
            $cPage = $router->remove('CategoryPage'.$ent->getUrl());
            $pPage = $router->remove('PostPage'.$ent->getUrl());
        }catch (StatementExecuteError $e){
            $view = $this->module->view('Error');
            $view->render($e->getErrorData());
            return;
        }
        try{
            $query->findById($id)->delete(true, true);
        }catch (StatementExecuteError $e){
            $view = $this->module->view('Error');
            $view->render($e->getErrorData());
            return;
        }
        $view = $this->module->view('Simple');
        $view->render(['code'=>0, 'message'=>'Категория удалена.', 'cPage'=>$cPage, 'pPage'=>$pPage]);
    }
} 