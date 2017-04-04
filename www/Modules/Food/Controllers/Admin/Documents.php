<?php
namespace Modules\Food\Controllers\Admin;


use Core\DataBase\Exception\StatementExecuteError;
use Core\Module\Base\Controller;
use Core\Response\Response;
use Dompdf\Dompdf;
use Dompdf\Exception;
use Modules\Documents\PDF;
use Modules\Kernel\Kernel;

class Documents extends Controller{


    public function getActiveOrderDays(){
        if (!$this->checkUser(Kernel::ENTITY_GET, 'OrderDay') || !$this->checkUser(Kernel::ENTITY_GET, 'Order')){
            $view = $this->module->view('AccessDenied');
            $view->render();
            return;
        }

        $input = $this->module->getCore()->getInput();
        $cityId = $input->get('CityId', null, TYPE_INT);
        $minDate = $input->get('MinDate', null, TYPE_STRING);
        $maxDate = $input->get('MaxDate', null, TYPE_STRING);
        $statuses = json_decode($input->get('Status', null, TYPE_RAW));

        if (is_null($cityId) || $cityId < 1){
            $view = $this->module->view('Admin\\Error');
            $view->render(['code'=>500, 'message'=>'Invalid city id']);
            return;
        }
        $query = "SELECT `od`.`delivery_date` AS `Date`, COUNT(`od`.`id`) AS `OrdersCount` FROM `order_day` `od` INNER JOIN `order` `o` ON `od`.`order_id`=`o`.`id` WHERE `od`.`delivery_date` BETWEEN ? AND ? AND `o`.`city_id`=?";
        $args = [$minDate, $maxDate, $cityId];
        if (count($statuses) > 0){
            $str = implode(', ', array_map(function($item){
                return '?';
            }, $statuses));
            $query .= ' AND `od`.`status` IN ('.$str.')';
            $args = array_merge($args, $statuses);
        }
        $query .= ' GROUP BY `od`.`delivery_date`';
        $db = $this->module->getCore()->getDb();
        $stm = $db->prepare($query);
        if (!$stm->execute($args)){
            $view = $this->module->view('Admin\\Error');
            $view->render($stm->errorInfo());
            return;
        }
        $result = $stm->fetchAll(\PDO::FETCH_ASSOC);
        $view = $this->module->view('Admin\\Simple');
        $view->render($result);
        return;


        /*
        $em = $this->module->getCore()->getEntityManager();
        $query = $em->getEntityQuery('OrderDay');
        try{
            $query->findByOrder()->findByCityId($cityId);
            $query->findByDeliveryDate($minDate, '>=')->findByDeliveryDate($maxDate, '<=');
            $query->findByStatus($statuses, 'IN');
            $result = $query->load(false, true);
            $view = $this->module->view('Admin\\EntitiesList');
            $view->render($result);
        } catch(StatementExecuteError $e){
            $view = $this->module->view('Admin\\Error');
            $view->render($e->getErrorData());
            return;
        }
        */
    }

    public function generateTrackList(array $data){
        $input = $this->getInput();
        $em = $this->getEntityManager();

        $orders = $input->get('Orders', null, TYPE_RAW);
        $orders = json_decode($orders, true);
        $orders = $em->getEntityQuery('OrderDay')->findById($orders, 'IN')->load(false,true);

        $courier = $input->get('Courier', null, TYPE_INT);
        $courier = $em->getEntityQuery('Courier')->findById($courier)->loadOne(false, true)->toArray();

        $city = $input->get('City', null, TYPE_RAW);
        $city = $em->getEntityQuery('City')->findById($city)->loadOne(false, true)->toArray();

        $date = $input->get('Date', null, TYPE_STRING);


        $templatePath = QS_path(['Modules', 'Food', 'templates', 'courier_list.php'], false);

        //var_dump($orders, $courier);
        ob_start();
        require $templatePath;
        $content = ob_get_clean();
        $filename = uniqid('order_', true).'.html';

        $path = QS_path(['_data','couriers_list'], false);
        if (!file_exists($path)){
            mkdir($path, 0777, true);
            chmod($path, 0777);
        }
        file_put_contents(QS_path([$path, $filename], false, false, false), $content);
        $view = $this->module->view('Admin\\Simple');
        $view->render(['Path'=>QS_path(['_data', 'couriers_list', $filename], false, false, false)]);
        //var_dump(QS_path(['_data', 'couriers_list', $filename], false, false, false), $content);
        //var_dump($this->generateOrderDayCheck($orders[0]->getId()));
    }

    public function generateOrderDayCheck(array $data){
        $input = $this->getInput();
        $orderDayId = $input->get('Order', 0, TYPE_INT);
        if ($orderDayId <= 0){
            die();
        }
        $em = $this->getEntityManager();
        $orderDay = $em->getEntityQuery('OrderDay')->findById($orderDayId)->loadOne(true,true);
        $orderDay->getOrder();
        $orderDay->getProducts();
        array_map(function($item){
            $item->getProduct();
            return $item;
        },$orderDay->getProducts());
        $orderDay->getProducts()[0]->getProduct();
        $deliveryDate = \DateTime::createFromFormat('Y-m-d', $orderDay->getDeliveryDate());
        $templatePath = QS_path(['templates', 'documents', 'order', 'day_voucher.php'], false);
        //$templatePath = QS_path(['Modules', 'Food', 'templates', 'order_check.php'], false);
        $order = $orderDay->getOrder();
        $weekDays = ['Воскресенье', 'Понедельник', 'Вторник', 'Среда', 'Четверг', 'Пятница', 'Суббота'];
        $wdStr = $weekDays[(int) $deliveryDate->format('w')];

        ob_start();
        require $templatePath;
        $content = ob_get_clean();
        //return $content;

        $dompdf = new Dompdf();
        $dompdf->loadHtml($content);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $dompdf->stream('order-voucher-'.$orderDay->getId(),  array("Attachment"=>0));
    }

    public function generateOrderVoucher(){
        $input = $this->getInput();
        $orderId = $input->get('Order', 0, TYPE_INT);
        if ($orderId <= 0){
            die();
        }
        $em = $this->getEntityManager();
        $order = $em->getEntityQuery('Order')->findById($orderId)->loadOne(true,true);
        $order->setPromoCodeData(json_decode($order->getPromoCodeData(), true));

    }

    public function generateKitchenReports(array $data){
        $em = $this->getEntityManager();
        $input = $this->getInput();
        $cityId = $input->get('City', null, TYPE_INT);
        $city = $em->getEntityQuery('City')->findById($cityId)->loadOne(false, true);

        $date = $input->get('Date', null, TYPE_STRING);
        $odQuery = $em->getEntityQuery('OrderDay');
        $odQuery->findByOrder()->findByCityId($cityId);
        $odQuery->findByDeliveryDate($date);
        $orderDays = $odQuery->load(false, true);
        $ordersReport = $this->generateKitchenOrders($city, $date, $orderDays);
        $productsReport = $this->generateKitchenProducts($city, $date, $orderDays);

        $odrFilename = uniqid('odr_', true).'.html';
        $odrPath = QS_path(['_data','od_reports'], false);
        if (!file_exists($odrPath)){
            mkdir($odrPath, 0777, true);
            chmod($odrPath, 0777);
        }

        $productsFileName = uniqid('products_', true).'.html';
        $productsPath = QS_path(['_data','products_reports'], false);
        if (!file_exists($productsPath)){
            mkdir($productsPath, 0777, true);
            chmod($productsPath, 0777);
        }

        file_put_contents(QS_path([$odrPath, $odrFilename], false, false, false), $ordersReport);
        file_put_contents(QS_path([$productsPath, $productsFileName], false, false, false), $productsReport);

        $view = $this->module->view('Admin\\Simple');
        $view->render(['ODRPath'=>QS_path(['_data', 'od_reports', $odrFilename], false, false, false), 'ProductsPath'=>QS_path(['_data', 'products_reports', $productsFileName], false, false, false)]);
        /*var_dump($report);
        var_dump($orderDays->toArray()[0]['Id']);*/
    }

    protected function generateKitchenOrders($city, $date, $orders){
        $date = \DateTime::createFromFormat('Y-m-d', $date);
        $templatePath = QS_path(['Modules', 'Food', 'templates', 'kitchen_orders.php'], false);
        ob_start();
        require $templatePath;
        $content = ob_get_clean();
        return $content;
    }

    protected function generateKitchenProducts($city, $date, $orders){
        $date = \DateTime::createFromFormat('Y-m-d', $date);
        $result = [];
        foreach ($orders as $order){
            foreach ($order->getProducts() as $product) {
                if (!isset($result[$product->getProduct()->getName()])){
                    $result[$product->getProduct()->getName()] = 0;
                }
                $result[$product->getProduct()->getName()] += $product->getAmount();
            }
        }
        $templatePath = QS_path(['Modules', 'Food', 'templates', 'kitchen_products.php'], false);
        ob_start();
        require $templatePath;
        $content = ob_get_clean();
        return $content;
    }

    public function generateKitchenOrdersPDF(array $data){
        $this->module->getCore()->getApp()->setResponseFormat(Response::FORMAT_PDF);
        $em = $this->getEntityManager();
        $input = $this->getInput();
        $cityId = $input->get('City', null, TYPE_INT);
        $city = $em->getEntityQuery('City')->findById($cityId)->loadOne(false, true);

        $date = $input->get('Date', null, TYPE_STRING);
        $odQuery = $em->getEntityQuery('OrderDay');
        $odQuery->findByOrder()->findByCityId($cityId);
        $odQuery->findByDeliveryDate($date);
        $orderDays = $odQuery->load(false, true);

        $report = $this->generateKitchenOrders($city, $date, $orderDays);

        $dompdf = new Dompdf();
        $dompdf->loadHtml($report);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $dompdf->stream('kitchen-orders-'.$city->getMachine().'-'.$date);
    }

    public function generateKitchenProductsPDF(array $data){
        $this->module->getCore()->getApp()->setResponseFormat(Response::FORMAT_PDF);
        $em = $this->getEntityManager();
        $input = $this->getInput();
        $cityId = $input->get('City', null, TYPE_INT);
        $city = $em->getEntityQuery('City')->findById($cityId)->loadOne(false, true);

        $date = $input->get('Date', null, TYPE_STRING);
        $odQuery = $em->getEntityQuery('OrderDay');
        $odQuery->findByOrder()->findByCityId($cityId);
        $odQuery->findByDeliveryDate($date);
        $orderDays = $odQuery->load(false, true);

        $report = $this->generateKitchenProducts($city, $date, $orderDays);

        $dompdf = new Dompdf();
        $dompdf->loadHtml($report);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $dompdf->stream('kitchen-products-'.$city->getMachine().'-'.$date);
    }

    protected function generateTrackListHTML($orders, $city, $courier, $date){
        $templatePath = QS_path(['Modules', 'Food', 'templates', 'courier_list.php'], false);
        ob_start();
        require $templatePath;
        $content = ob_get_clean();
        return $content;
    }

    public function generateTrackListPDF(array $data){
        $this->module->getCore()->getApp()->setResponseFormat(Response::FORMAT_PDF);
        $input = $this->getInput();
        $em = $this->getEntityManager();

        $orders = $input->get('Orders', null, TYPE_RAW);
        $orders = json_decode($orders, true);
        if (count($orders) == 0){
            return false;
        }
        $orders = $em->getEntityQuery('OrderDay')->findById($orders, 'IN')->load(false,true);

        $courier = $input->get('Courier', null, TYPE_INT);
        $courier = $em->getEntityQuery('Courier')->findById($courier)->loadOne(false, true);

        $city = $input->get('City', null, TYPE_RAW);
        $city = $em->getEntityQuery('City')->findById($city)->loadOne(false, true);

        $date = $input->get('Date', null, TYPE_STRING);

        $content = $this->generateTrackListHTML($orders, $city, $courier, $date);
        $dompdf = new Dompdf();
        $dompdf->loadHtml($content);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $dompdf->stream('courier-list-'.$courier->getName().'-'.$city->getMachine().'-'.$date);
    }

} 