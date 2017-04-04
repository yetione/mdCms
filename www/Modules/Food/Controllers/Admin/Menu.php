<?php
namespace Modules\Food\Controllers\Admin;


use Core\DataBase\Exception\StatementExecuteError;
use Core\Meter;
use Core\Module\Base\Controller;

class Menu extends Controller{

    /**
     * @var \Modules\Food\Food
     */
    protected $module;

    public function getList(array $data){
        $input = $this->module->getCore()->getInput();
        $startDate = $input->get('startDate', null, TYPE_STRING);
        $endDate = $input->get('endDate', null, TYPE_STRING);
        $cityId = $this->module->getCore()->getInput()->get('cityId', 0, TYPE_INT);

        $em = $this->module->getCore()->getEntityManager();
        $query = $em->getEntityQuery('Menu');
        if (!is_null($startDate)) $query->findByDate($startDate, '>=');
        if (!is_null($endDate)) $query->findByDate($endDate, '<=');
        if ($cityId > 0) $query->findByCityId($cityId);
        $query->orderByDate('ASC');
        $result = $query->load();
        $view = $this->module->view('Admin\\EntitiesList');
        $view->render($result);
    }

    public function saveItem(array $data){

        $params = json_decode(trim(file_get_contents('php://input')), true);
        $em = $this->module->getCore()->getEntityManager();
        $ent = $em->getEntity('Menu');
        $ent->fromArray($params['data']);
        if (!$ent->getId() || $ent->getId() == 0){
            $ent->setIsNew(true);
        }

        try{
            $result = $em->getEntityQuery('Menu')->save($ent, true);
            $view = $this->module->view('Admin\\Simple');
            $view->render($result->toArray());
        }catch (StatementExecuteError $e){
            $view = $this->module->view('Admin\\Error');
            $view->render(array_merge($e->getErrorData(), ['p'=>$params]));
        }



    }

    public function saveItems(array $data){
        $params = json_decode(trim(file_get_contents('php://input')), true);
        $em = $this->module->getCore()->getEntityManager();
        $ent = $em->getEntity('Menu');
        foreach ($params['items'] as $item){
            $ent->fromArray($item);
            if (!$ent->getId() || $ent->getId() == 0){
                $ent->setIsNew(true);
            }
        }
        $ent->fromArray($params['item']);
        if (!$ent->getId() || $ent->getId() == 0){
            $ent->setIsNew(true);
        }

        $result = $em->getEntityQuery('Menu')->save($ent);

        $view = $this->module->view('Admin\\ProductSave');
        $view->render($params, $result);
    }

    public function getItem(array $data){
        $input = $this->module->getCore()->getInput();
        $date = $input->get('date', null, TYPE_STRING);
        $cityId = $input->get('cityId', null, TYPE_INT);
        $id = $input->get('id', null, TYPE_INT);
        $em = $this->module->getCore()->getEntityManager();
        $query = $em->getEntityQuery('Menu');
        if (!is_null($date)) $query->findByDate($date);
        if (!is_null($cityId)) $query->findByCityId($cityId);
        if (!is_null($id)) $query->findById($id);
        $query->orderByDate('ASC');

        $view = $this->module->view('Admin\\Item');
        $view->render($query->loadOne());

    }


    public function getToDay(array $data){
        $date = $this->module->getCore()->getInput()->get('date', null, TYPE_STRING);
        $cityId = $this->module->getCore()->getInput()->get('cityId', 0, TYPE_INT);
        if ($cityId < 1){
            $cityId = $this->module->getCityData()->getId();
        }
        if (!is_null($date)){
            $em = $this->module->getCore()->getEntityManager();

            $query = $em->getEntityQuery('Menu');
            $query->findByDate($date)->findByCity()->findById($cityId);
            $items = $query->load(false);
            $productIds = array_map(function($item){
                return $item->getProductId();
            }, $items);
            //var_dump($productIds);

            $productsQ = $em->getEntityQuery('Product');
            $productsQ->findById('('.join(',',$productIds).')', 'IN');
            $products = $productsQ->load();


            $view = $this->module->view('Admin\\MenuToDate');
            $view->render($date, $products);
        }
    }

    public function fixDB(array $data){
        $meter = new Meter('Реформат БД.');
        $meter->dir(array('logs','fix_menu'))->run();
        $em = $this->getEntityManager();
        $query = $em->getEntityQuery('Menu');
        $productsQuery = $em->getEntityQuery('Product');
        $result = $query->load();
        $productsPool = [];
        foreach ($result as $ent){
            $oldData = json_decode($ent->getData(), true);
            $newData = [];
            $categoryIndexes = [];
            foreach ($oldData['products'] as $pId){
                $poolKey = 'product_'.$pId;
                if (!isset($productsPool[$poolKey])){
                    $product = $productsQuery->findById($pId)->loadOne();
                    if (!$product){
                        continue;
                    }
                    $productsPool[$poolKey] = $product;
                }
                $product = $productsPool[$poolKey];
                $categoryId = $product->getCategoryId();
                $cKey = 'category_'.$categoryId;
                $cIndex = isset($categoryIndexes[$cKey]) ? $categoryIndexes[$cKey] : -1;
                if ($cIndex == -1){
                    $newData[] = ['CategoryId'=>$categoryId, 'Products'=>[]];
                    $categoryIndexes[$cKey] = count($newData) - 1;
                    $cIndex = $categoryIndexes[$cKey];

                    //$newData[$categoryId] = [];
                }
                if (!in_array($product->getId(), $newData[$cIndex]['Products'])){
                    $newData[$cIndex]['Products'][] = $product->getId();
                }
            }
            $ent->setData(json_encode($newData));
            $ent = $query->save($ent);
        }
        $view = $this->module->view('Admin\\Simple');
        $view->render($ent->toArray());
        $meter->end(true);
    }

    public function fixDbNew(array $data){
        $em = $this->getEntityManager();
        $query = $em->getEntityQuery('Menu');
        $productsQuery = $em->getEntityQuery('Product');
        $result = $query->load();
        $productsPool = [];
        foreach ($result as $ent){
            $oldData = json_decode($ent->getData(), true);
            $newData = [];
            var_dump($oldData);
            foreach ($oldData as $cId => $products){
                $newData[] = ['CategoryId'=>$cId, 'Products'=>$products];
            }
            $ent->setData(json_encode($newData));
            $ent = $query->save($ent);
        }
        $view = $this->module->view('Admin\\Simple');
        $view->render($ent->toArray());
    }


}