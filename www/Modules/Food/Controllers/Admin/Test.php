<?php
/**
 * Created by PhpStorm.
 * User: yeti
 * Date: 15.12.2016
 * Time: 23:18
 */

namespace Modules\Food\Controllers\Admin;


use Core\DataBase\Exception\StatementExecuteError;
use Core\Module\Base\Controller;
use Modules\Email\Templates\Basic;
use Modules\Kernel\Kernel;

class Test extends Controller
{

    public function sendMail(array  $data){
        $em = $this->getEntityManager();
        $input = $this->getInput();
        $query = $em->getEntityQuery('Order');
        $query->findById($input->get('OrderId', 12, TYPE_INT));
        $result = $query->loadOne(true, true);
        foreach ($result->getProducts() as $product){

            foreach ($product->getProducts() as $p){
                $p->getProduct();
            }
        }
        //$result->recount(false, false);
        $result->getCity();
        //$result->getUser();
        $mail = new Basic();
        $mail->setSMTP(['Auth'=>true, 'Username'=>'knuzz_1', 'Password'=>'6EniU8fYZJ']);
        //$mail = Basic::getSMTPMailer(['Auth'=>true, 'Username'=>'knuzz_1', 'Password'=>'6EniU8fYZJ']);
        $mail->addAddress('jub45@yandex.ru');
        $mail->setSubject('Информация о заказе');
        $mail->setVar('header', 'СПАСИБО! ВАШ ЗАКАЗ ПРИНЯТ');
        $mail->render(QS_path(['templates', 'emails','order','success.php'], false), ['order'=>$result]);
        $r = $mail->send();
        $mail->clear();
        $view = $this->module->view('Admin\\Simple');
        $view->render($result->toArray());
        //$view->render($result->toArray());
    }

    public function recountOrder(array $data){
        $em = $this->getEntityManager();
        $input = $this->getInput();
        //$orderId = $input->get('OrderId', 0, TYPE_INT);
        $params = json_decode(trim(file_get_contents('php://input')), true);
        $orderId = intval($params['OrderId']);
        if ($orderId < 1){
            $view = $this->module->view('Admin\\Error');
            $view->render(['code'=>1, 'message'=>'Order id is not valid']);
            return false;
        }
        $query = $em->getEntityQuery('Order');
        $query->findById($orderId);
        try{
            /**
             * @var \Modules\Food\Entities\Order $order
             */
            $order = $query->loadOne(true, true);
            $order->recount(true, true);
            $view = $this->module->view('Admin\\Simple');

            //$view->render(filter_var($input->_json['Order']));
            $view->render($input->json('Order'));
            //$view->render($order->toArray());
            return true;
        }catch (StatementExecuteError $e){
            $view = $this->module->view('Admin\\Error');
            $view->render(['code'=>2, 'message'=>'Order execute error', 'data'=>$e->getErrorData()]);
            return false;
        }
    }

    public function saveOrder(array $data){
        if (!$this->checkUser(Kernel::ENTITY_UPDATE, 'Order') ||
            !$this->checkUser(Kernel::ENTITY_UPDATE, 'OrderDay') || !$this->checkUser(Kernel::ENTITY_UPDATE, 'OrderDayProduct') ){
            $view = $this->module->view('AccessDenied');
            $view->render();
            return false;
        }

        $input = $this->getInput();
        $em = $this->getEntityManager();
        if (is_null($orderArray = $input->json('Order')) || is_null($orderDays = $input->json('OrderDays'))){
            $view = $this->module->view('Admin\\Error');
            $view->render(['code'=>1, 'message'=>'Order or OrderDays not passed.']);
            return false;
        }
        /**
         * @var \Modules\Food\Entities\Order $order
         */
        $order = $em->getEntity('Order');
        $orderDayProductQuery = $em->getEntityQuery('OrderDayProduct');
        $order->fromArray($orderArray);

        try{
            $order->save(true);
        }catch (StatementExecuteError $e) {
            $view = $this->module->view('Admin\\Error');
            $view->render(['code' => 2, 'message' => 'Cant save order.']);
            return false;
        }

        foreach ($orderDays as $orderDay){
            $products = $orderDay['Products']; unset($orderDay['Products']);
            $orderDayEnt = $em->getEntity('OrderDay');
            if (is_array($orderDay['DeliveryType']) && isset($orderDay['DeliveryType']['Name'])){
                $orderDay['DeliveryType'] = $orderDay['DeliveryType']['Name'];
            }
            $orderDayEnt->fromArray($orderDay);
            //Получаем список продуктов, затем удаляем ключ, да бы не получилось каскадного обновления. я хз робит оно или нет.
            //$orderDay = $em->getEntity('OrderDay')->fromArray($orderDay);
            try{
                $orderDayEnt->save(true);
            }catch (StatementExecuteError $e){
                $view = $this->module->view('Admin\\Error');
                $view->render(['code' => 3, 'message' => 'Cant save order day.']);
                return false;
            }
            foreach ($products as $product){
                $orderDayProduct = $em->getEntity('OrderDayProduct');
                if ($product['Deleted'] && (int) $product['Id'] > 0){
                    $orderDayProductQuery->findById($product['Id']);
                    try{
                        $orderDayProductQuery->delete(true, true);
                    }catch (StatementExecuteError $e){
                        $view = $this->module->view('Admin\\Error');
                        $view->render(['code' => 4, 'message' => 'Cant delete order day product.']);
                        return false;
                    }
                    $orderDayProductQuery->reset();
                }elseif (!$product['Deleted']){
                    $orderDayProduct->fromArray($product);
                    try{
                        $orderDayProduct->save(true);
                    }catch (StatementExecuteError $e){
                        $view = $this->module->view('Admin\\Error');
                        $view->render(['code' => 5, 'message' => 'Cant save order day product.']);
                        return false;
                    }
                }
            }
        }
        try{
            $order->recount(true, true);
            foreach ($order->getProducts() as $orderDay){
                foreach ($orderDay->getProducts() as $product){
                    $product->getProduct();
                }
            }
        }catch (StatementExecuteError $e){
            $view = $this->module->view('Admin\\Error');
            $view->render(['code' => 6, 'message' => 'Cant recount order.']);
            return false;
        }

        $view = $this->module->view('Admin\\Simple');
        $view->render($order->toArray());
        return true;
    }

    public function createOrderDay(array $data){
        if (!$this->checkUser(Kernel::ENTITY_UPDATE, 'OrderDay')){
            $view = $this->module->view('AccessDenied');
            $view->render();
            return false;
        }
        $em = $this->getEntityManager();
        $input = $this->getInput();
        $orderId = $input->get('OrderId', 0, TYPE_INT);
        if ($orderId < 1){
            $view = $this->module->view('Admin\\Error');
            $view->render(['code'=>1, 'message'=>'Invalid order id']);
            return false;
        }
        $orderDate = $input->get('Date', null, TYPE_STRING);
        if (is_null($orderDate)){
            $view = $this->module->view('Admin\\Error');
            $view->render(['code'=>2, 'message'=>'Date is not recive']);
            return false;
        }
        $orderDay = $em->getEntity('OrderDay');
        $orderDay->setIsNew(true);
        $orderDay->setOrderId($orderId);
        $orderDay->setDeliveryDate($orderDate);
        $orderDay->setDeliveryType('Курьером');
        $orderDay->setDeliveryPrice(150);
        $orderDay->setPaymentType('Оплата курьеру');
        $orderDay->setPrice(0);
        $orderDay->setStatus('Выполняется');
        $orderDay->setDiscountPrice(0);
        $view = $this->module->view('Admin\\Simple');
        $view->render($orderDay->toArray());
        return true;

    }

    public function importBzu(array $data){
        $em = $this->getEntityManager();
        $query = $em->getEntityQuery('Product');
        $csvFile = fopen(QS_path(['_data', 'bzu.csv'], false), 'r');
        $i = 0;
        $j = 0;
        while(($row=fgetcsv($csvFile,0,';')) !== false){
            $i++;
            $name = trim($row[1]);
            if (!$name){
                continue;
            }
            $weight = (float) trim($row[2]);
            $proteins = (float) trim($row[3]);
            $fats = (float) trim($row[4]);
            $carbs = (float) trim($row[5]);
            $calorie = (float) trim($row[6]);

            $query->findByName($name);
            try{
                $products = $query->load(false, true);
            }catch (StatementExecuteError $e){
                var_dump('e1', $e->getErrorData());
                continue;
            }
            foreach ($products as $p){
                $p->setWeight($weight);
                $p->setProteins($proteins);
                $p->setFats($fats);
                $p->setCarbs($carbs);
                $p->setCalorie($calorie);
                try{
                    $query->save($p, true);
                    $j++;
                }catch (StatementExecuteError $e){
                    var_dump('e2', $e->getErrorData());
                    continue;
                }

                //$p->save();
            }
            $query->reset();
        }
        $view = $this->module->view('Admin\\Simple');
        $view->render(['total'=>$i, 'success'=>$j]);
    }
}