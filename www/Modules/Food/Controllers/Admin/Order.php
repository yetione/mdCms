<?php
namespace Modules\Food\Controllers\Admin;


use Core\DataBase\Exception\StatementExecuteError;
use Core\Module\Base\Controller;
use Modules\Food\Food;
use Modules\Kernel\Kernel;
use Modules\Users\Users;

class Order extends Controller{

    /**
     * @var Food
     */
    protected $module;

    protected $entityName = 'Order';


    public function getOrderData(array $data){
        if (!$this->checkUser(Kernel::ENTITY_GET, 'OrderDay')){
            $view = $this->module->view('AccessDenied');
            $view->render();
            return;
        }
        $input = $this->module->getCore()->getInput();

        $orderId = $input->get('OrderId', 0, TYPE_INT);
        if (!$orderId){
            $view = $this->module->view('Admin\\Error');
            $view->render(['message'=>'Id заказа должен быть больше 0', 'code'=>1]);
            return;
        }
        $em = $this->module->getCore()->getEntityManager();
        $query = $em->getEntityQuery('OrderDay');

        $query->findByOrderId($orderId);
        try{
            $orderDays = $query->load(false, true);
            foreach ($orderDays as $od){
                $od->setPrice((float) $od->getPrice());
            }
        }catch (StatementExecuteError $e){
            $view = $this->module->view('Admin\\Error');
            $view->render($e->getErrorData());
            return;
        }
        foreach ($orderDays as $orderDay){
            $orderDay->getProducts();
            foreach($orderDay->getProducts() as $product){
                $product->setPrice((float) $product->getPrice());
                $product->getProduct();
            }
        }
        $view = $this->module->view('Admin\\EntitiesList');
        $view->render($orderDays);
    }

    public function saveOrderDayProduct(array $data){
        if (!$this->checkUser(Kernel::ENTITY_UPDATE, 'OrderDayProduct')){
            $view = $this->module->view('AccessDenied');
            $view->render();
            return;
        }
        $params = json_decode(trim(file_get_contents('php://input')), true);

        $orderDayData = $params['Product'];

        $em = $this->module->getCore()->getEntityManager();
        $ent = $em->getEntity('OrderDayProduct');
        $ent->fromArray($orderDayData);

        $query = $em->getEntityQuery('OrderDayProduct');
        try{
            $query->save($ent, true);

            $orderDayQuery = $em->getEntityQuery('OrderDay');
            $orderDay = $orderDayQuery->findById($ent->getOrderDayId())->loadOne(false, true);
            $products = $em->getEntityQuery('OrderDayProduct')->findByOrderDayId($ent->getOrderDayId())->load(false, true);
            $dayPrice = 0;
            foreach($products as $product){
                $dayPrice += $product->getAmount()*$product->getPrice();
            }
            $orderDay->setPrice($dayPrice);
            $orderDayQuery->save($orderDay);
            $this->module->recountOrder($orderDay->getOrderId());
            $view = $this->module->view('Admin\\Simple');
            $view->render($orderDay->toArray());
        }catch (StatementExecuteError $e){
            $view = $this->module->view('Admin\\Error');
            $view->render($e->getErrorData());
        }

    }

    public function deleteOrderDayProduct(array $data){
        if (!$this->checkUser(Kernel::ENTITY_DELETE, 'OrderDayProduct')){
            $view = $this->module->view('AccessDenied');
            $view->render();
            return;
        }
        $input = $this->module->getCore()->getInput();

        $id = $input->get('Id', 0, TYPE_INT);
        if ($id <= 0){
            //TODO: error
            return;
        }
        $em = $this->module->getCore()->getEntityManager();
        $query = $em->getEntityQuery('OrderDayProduct');

        try{
            $ent = $query->findById($id)->loadOne(false, true);
            if (is_null($ent)){
                $view = $this->module->view('Admin\\Error');
                $view->render(['code'=>404,'message'=>'Order day not found']);
                return;
            }
            $orderDayId = $ent->getOrderDayId();
            $query->findById($id)->delete(true, true);


            $orderDayQuery = $em->getEntityQuery('OrderDay');
            $orderDay = $orderDayQuery->findById($orderDayId)->loadOne(false, true);
            $products = $em->getEntityQuery('OrderDayProduct')->findByOrderDayId($orderDayId)->load(false, true);
            $dayPrice = 0;
            foreach($products as $product){
                $dayPrice += $product->getAmount()*$product->getPrice();
            }
            $orderDay->setPrice($dayPrice);
            $orderDayQuery->save($orderDay);
            $this->module->recountOrder($orderDay->getOrderId());
            $view = $this->module->view('Admin\\Simple');
            $view->render($orderDay->toArray());
        } catch(StatementExecuteError $e){
            $view = $this->module->view('Admin\\Error');
            $view->render($e->getErrorData());
        }
    }

    public function addProduct(array $data){
        if (!$this->checkUser(Kernel::ENTITY_UPDATE, 'OrderDayProduct')){
            $view = $this->module->view('AccessDenied');
            $view->render();
            return;
        }
        $input = $this->module->getCore()->getInput();

        $productId = $input->get('ProductId', 0, TYPE_INT);
        $orderDayId = $input->get('OrderDayId', 0, TYPE_INT);
        $amount = $input->get('Amount', 0, TYPE_INT);
        $cityId = $input->get('CityId', 0, TYPE_INT);

        if ($productId <= 0 || $orderDayId <= 0 || $amount <= 0 || $cityId <= 0){
            //TODO:error
            return;
        }

        $em = $this->module->getCore()->getEntityManager();
        try{
            $orderDayQuery = $em->getEntityQuery('OrderDay');
            $orderDayProductQuery = $em->getEntityQuery('OrderDayProduct');
            $cityQuery = $em->getEntityQuery('City');
            $productQuery = $em->getEntityQuery('Product');

            $city = $cityQuery->findById($cityId)->loadOne(false, true);
            $orderDay = $orderDayQuery->findById($orderDayId)->loadOne(false, true);

            $product = $productQuery->findById($productId)->loadOne(false, true);

            $methodName = 'getPrice'.ucfirst($city->getMachine());
            $price = (float) $product->$methodName();


            $orderDayProduct = $em->getEntity('OrderDayProduct');
            $orderDayProduct->setOrderDayId($orderDayId);
            $orderDayProduct->setProductId($productId);
            $orderDayProduct->setAmount($amount);
            $orderDayProduct->setPrice($price);
            $orderDayProduct->setIsNew(true);
            $orderDayProductQuery->save($orderDayProduct, true);

            $products = $orderDayProductQuery->findByOrderDayId($orderDayId)->load(false, true);
            $dayPrice = 0;
            foreach($products as $product){
                $dayPrice += $product->getAmount()*$product->getPrice();
            }
            $orderDay->setPrice($dayPrice);
            $orderDayQuery->save($orderDay);

            $result = ['OrderDayProduct'=>$orderDayProduct->toArray(), 'OrderDay'=>$orderDay->toArray()];
            $this->module->recountOrder($orderDay->getOrderId());
            $view = $this->module->view('Admin\\Simple');
            $view->render($result);
        } catch(StatementExecuteError $e){
            $view = $this->module->view('Admin\\Error');
            $view->render($e->getErrorData());
        }
    }

    public function saveOrder(array $data){
        if (!$this->checkUser(Kernel::ENTITY_UPDATE, 'Order') ||
            !$this->checkUser(Kernel::ENTITY_UPDATE, 'OrderDay') ){
            $view = $this->module->view('AccessDenied');
            $view->render();
            return;
        }
        $params = json_decode(trim(file_get_contents('php://input')), true);

        $order = $params['Order'];
        $orderDays = $params['OrderDays'];

        $em = $this->module->getCore()->getEntityManager();

        try{
            $orderEnt = $em->getEntity('Order');
            $orderEnt->fromArray($order);
            $orderEnt = $em->getEntityQuery('Order')->save($orderEnt, true);
            $ent = $em->getEntity('OrderDay');
            foreach ($orderDays as $od){
                $ent->fromArray($od);
                $em->getEntityQuery('OrderDay')->save($ent, true);
            }
            $this->module->recountOrder($orderEnt->getId());
            $view = $this->module->view('Admin\\Simple');
            $view->render($orderEnt->toArray());
        }catch (StatementExecuteError $e){
            $view = $this->module->view('Admin\\Error');
            $view->render($e->getErrorData());
        }
    }

    public function save(array $data){

    }

    public function deleteOrder(array $data){
        if (!$this->checkUser(Kernel::ENTITY_DELETE, 'Order') ||
            !$this->checkUser(Kernel::ENTITY_DELETE, 'OrderDay') ||
            !$this->checkUser(Kernel::ENTITY_DELETE, 'OrderDayProduct')){
            $view = $this->module->view('AccessDenied');
            $view->render();
            return;
        }
        $input = $this->module->getCore()->getInput();

        $orderId = $input->get('OrderId', 0, TYPE_INT);

        $em = $this->module->getCore()->getEntityManager();
        $orderQuery = $em->getEntityQuery('Order');
        $orderDaysQuery = $em->getEntityQuery('OrderDay');
        $orderDayProductQuery = $em->getEntityQuery('OrderDayProduct');

        try{
            $order = $orderQuery->findById($orderId)->loadOne(false, true);
            $orderDays = $orderDaysQuery->findByOrderId($orderId)->load(false, true);
            foreach ($orderDays as $od){
                $orderDayProductQuery->findByOrderDayId($od->getId())->delete(true, true);
            }
            $orderDaysQuery->findByOrderId($orderId)->delete(true, true);
            $orderQuery->findById($orderId)->delete(true,true);
            $view = $this->module->view('Admin\\Simple');
            $view->render('Success');
        } catch(StatementExecuteError $e){
            $view = $this->module->view('Admin\\Error');
            $view->render($e->getErrorData());
        }
    }



    public function createOrderDay(array $data){
        if (!$this->checkUser(Kernel::ENTITY_UPDATE, 'OrderDay')){
            $view = $this->module->view('AccessDenied');
            $view->render();
            return;
        }
        $input = $this->module->getCore()->getInput();
        $em = $this->module->getCore()->getEntityManager();

        $orderDate = $input->get('Date', null, TYPE_STRING);
        if (is_null($orderDate)){
            $view = $this->module->view('Admin\\Error');
            $view->render(['code'=>404, 'message'=>'Date is not recive']);
            return;
        }
        $orderId = $input->get('OrderId', 0, TYPE_INT);
        if ($orderId <= 0 || is_null($order = $em->getEntityQuery('Order')->findById($orderId)->loadOne(false, false))){
            $view = $this->module->view('Admin\\Error');
            $view->render(['code'=>404, 'message'=>'Invalid order id']);
            return;
        }

        $orderDay = $em->getEntity('OrderDay');
        $orderDay->setIsNew(true);
        $orderDay->setDeliveryDate($orderDate)->setPaymentType('Наличными курьеру')->setPrice(0)->setOrderId($orderId)->setDeliveryType('Самовывоз');

        $orderDaysQuery = $em->getEntityQuery('OrderDay');
        try{
            $orderDay = $orderDaysQuery->save($orderDay, true);

            $view = $this->module->view('Admin\\Item');
            $this->module->recountOrder($orderDay->getOrderId());
            $view->render($orderDay);
        }catch (StatementExecuteError $e){
            $view = $this->module->view('Admin\\Error');
            $view->render($e->getErrorData());
        }


    }

    public function selectOrdersToDay(array $data){
        $input = $this->getInput();
        $date = $input->get('Date', null);
        $cityId = $input->get('CityId', null);
        $em = $this->getEntityManager();
        $query = $em->getEntityQuery('OrderDay');
        $query->findByDeliveryDate($date)->findByCityId($cityId);
        $query->groupById();
        try{
            $entities = $query->load(false. true);
            $result = [];
            foreach ($entities as $od){
                $tmp = $od->toArray();
                $tmp['ClientName'] = $od->getOrder()->getClientName();
                $result[] = $tmp;
            }
            $view = $this->module->view('Admin\\Simple');
            $view->render($result);
        }catch (StatementExecuteError $e){
            $view = $this->module->view('Admin\\Error');
            $view->render($e->getMessage());
        }
        
    }

    public function saveOrderDay(array $data){
        if (!$this->checkUser(Kernel::ENTITY_UPDATE, 'OrderDayProduct') || !$this->checkUser(Kernel::ENTITY_UPDATE, 'OrderDay')){
            $view = $this->module->view('AccessDenied');
            $view->render();
            return;
        }

        $input = $this->getInput();
        $od = \json_decode($input->get('OrderDay', null, TYPE_RAW), true);

        var_dump($od);
    }
} 