<?php
namespace Modules\Food\Controllers;


use Core\DataBase\Exception\StatementExecuteError;
use Core\Debugger;
use Core\Mailer;
use Core\Module\Base\Controller;
use Dompdf\Exception;
use Modules\Email\Templates\Basic;
use Modules\Food\Food;
use Modules\GeoLocation\GeoLocation;
use Modules\Users\Users;

class Order extends Controller{

    /**
     * @var Food
     */
    protected $module;

    /**
     * @param $data
     * @param array $requiredFields
     * @return int
     */
    protected function isEmptyOrderData($data, $requiredFields){
        foreach ($data as $field => $value){
            if (in_array($field, $requiredFields) && empty($value)){
                return 1;
            }
        }
        return 0;
    }

    /**
     * @param array $data
     * @param array $keys
     * @return mixed
     */
    protected function trimKeys($data, $keys){
        foreach ($data as $k=>$v){
            if (in_array($k, $keys)){
                $data[$k] = trim($v);
            }
        }
        return $data;
    }

    public function doOrder(array $data){
        $em = $this->module->getCore()->getEntityManager();
        $cart = $this->module->getCart();

        $params = json_decode(trim(file_get_contents('php://input')), true);

        $orderData = $params['Data'];
        $type = $params['Type'];
        /*
        $input = $this->module->getCore()->getInput();
        $orderData = $input->get('Data', null, TYPE_RAW);
        $orderData = json_decode($orderData, true);
        */
        //return;
        $reCaptchaData = $this->loadReCaptchaData($orderData['ReCaptcha']);
        if ($orderData['OrderType'] !== 'ToAllDays' && $orderData['OrderType'] !== 'UniqueAddresses'){
            Debugger::log('Order::doOrder: Invalid order type: ',$orderData['OrderType']);
            $view = $this->module->view('Admin\\Error');
            $view->render(['code'=>1, 'message'=>'Invalid order type.']);
            return;
        }
        if ($orderData['OrderType'] === 'UniqueAddresses' && count($orderData['UniqueAddresses']) !== count($cart->getData())){
            Debugger::log('Order::doOrder: Order days not equal unique address.');
            $view = $this->module->view('Admin\\Error');
            $view->render(['code'=>2, 'message'=>'Invalid UniqueAddress.']);
            return;
        }
        if (!$reCaptchaData['success']){
            $view = $this->module->view('Admin\\Error');
            $view->render(['code'=>3, 'message'=>'Капча не прошла проверку.']);
            return;
        }
        $orderData = $this->trimKeys($orderData, ['Fio', 'Phone', 'Email']);
        if ($this->isEmptyOrderData($orderData, ['Fio', 'Phone', 'Email', 'ReCaptcha'])){
            $view = $this->module->view('Admin\\Error');
            $view->render(['code'=>4, 'message'=>'Не заполнены обязательные поля.']);
            return;
        }
        if ($cart->isIsBlocked()){
            $view = $this->module->view('Admin\\Error');
            $view->render(['code'=>8, 'message'=>'Не возможно сформировать заказ']);
            return;
        }
        $daysData = [];
        $cUser = $this->module->getCore()->getSession()->get(Users::CURRENT_USER_KEY);
        $clientId = (int) $cUser->getId() > 0 ? $cUser->getId() : 0;
        /**
         * @var GeoLocation $geoLocation
         */
        $geoLocation = $this->module->getManager()->getModule('GeoLocation');
        $cityData = $geoLocation->getGeoData();

        $promoCode = $cart->getPromoCode();
        /**
         * @var \Modules\Food\Entities\Order $order
         */
        $order = $em->getEntity('Order');
        $order->setDateCreated(date("Y-m-d H:i:s", time()));
        $order->setStatus('Выполняется');
        $order->setClientName($orderData['Fio']);
        $order->setUserId($clientId);
        $order->setCityId($cityData->getId());
        $order->setPhone($orderData['Phone']);
        $order->setEmail($orderData['Email']);
        $order->setPrice($cart->getTotalPrice());
        $order->setPromoCodeName($promoCode['Code']);
        $order->setPromoCodeData(json_encode($promoCode));
        $order->setDiscount('');

        $order->setIsNew(true);
        try{
            $em->getEntityQuery('Order')->save($order, true);
        }catch (StatementExecuteError $e){
            Debugger::log('Order::doOrder: Cant create order. '.implode(', ', $e->getErrorData()));
            $view = $this->module->view('Admin\\Error');
            $view->render(['code'=>5, 'message'=>$e->getErrorData()]);
            return;
        }
        if ($promoCode['Type'] == 2){
            $PCQuery = $em->getEntityQuery('PromoCode');
            $PCEnt = $em->getEntity('PromoCode');
            $PCEnt->fromArray($promoCode);
            $PCEnt->setData(json_encode($PCEnt->getData()));
            $PCEnt->setUsed(1);
            try{
                $PCEnt = $PCQuery->save($PCEnt, true);
            }catch (StatementExecuteError $e){
                Debugger::log('Order::doOrder: Cant set used promocode '.$PCEnt->getCode());
            }



        }



        $orderDayQuery = $em->getEntityQuery('OrderDay');
        $orderDayProductQuery = $em->getEntityQuery('OrderDayProduct');
        foreach ($cart->getData() as $date=>$data){
            $day = $this->trimKeys($orderData['OrderType'] == 'UniqueAddresses' ? $orderData['UniqueAddresses'][$date] : $orderData['ToAllDays'],
                ['Street', 'Building', 'Room', 'DeliveryTime', 'Comment', 'PersonsCount', 'MetroStation', ]);
            if ($this->isEmptyOrderData($day, ['Street', 'CityId'])){
                $view = $this->module->view('Admin\\Error');
                $view->render(['code'=>6, 'message'=>'Не заполнены обязательные поля доставки.']);
                return;
            }
            $orderDay = $em->getEntity('OrderDay');
            $orderDay->setOrderId($order->getId());
            $orderDay->setDeliveryType('Курьером');
            $orderDay->setStreet($day['Street']);
            $orderDay->setBuilding($day['Building']);
            $orderDay->setRoom($day['Room']);
            $orderDay->setDeliveryDate($date);
            $orderDay->setDeliveryTime($day['DeliveryTime']);
            $orderDay->setPaymentType('Оплата курьеру');
            $orderDay->setIsChanged(0);
            $orderDay->setChangedBy(0);
            $orderDay->setStatus('Выполняется');
            $orderDay->setClientComment($day['Comment']);
            $orderDay->setPrice($data['price']);//TODO: Стоимость доставки
            $orderDay->setDeliveryPrice(150);
            $orderDay->setMetroStation($day['MetroStation']);
            $orderDay->setPersonsCount($day['PersonsCount'] ? $day['PersonsCount'] : 0);
            $orderDay->setCourierId(0);
            $orderDay->setCityId($day['CityId']);
            $orderDay->setStockId(0);
            $orderDay->setDiscount('');
            $orderDay->setDiscountPrice(0);

            //$orderDay->setPhone($date['Phone']);
            $orderDay->setIsNew(true);
            try{
                $orderDayQuery->save($orderDay, true);
            }catch (StatementExecuteError $e){
                Debugger::log('Order: cant save order day '.$e->getErrorData());
                $view = $this->module->view('Admin\\Error');
                $view->render(['code'=>7, 'message'=>'Cant save order day: '.$date]);
                return;
            }

            foreach($data['products'] as $product){

                $orderDayProduct = $em->getEntity('OrderDayProduct');
                $orderDayProduct->setOrderDayId($orderDay->getId());
                $orderDayProduct->setProductId($product['id']);
                $orderDayProduct->setAmount($product['amount']);
                $orderDayProduct->setPrice($product['price']);
                $orderDayProductQuery->save($orderDayProduct);

                $orderDay->addProducts($orderDayProduct);
            }
            $order->addProducts($orderDay);
        }
        $order->setIsNew(false);
        $order->recount(true, true);
        if (preg_match('(\d{10})', $orderData['Phone']) === 1){
            $apiId = 'BBDA2D46-48AC-CAC2-02DC-9672D4EFF8AD';
            $phone1 = '+7'.$orderData['Phone'];
            $text1 = 'Ваш заказ '.$order->getId().' принят к обработке';
            try{
                $client = new \SmsRu\Api(new \SmsRu\Auth\ApiIdAuth($apiId));
                $sms1 = new \SmsRu\Entity\Sms($phone1, $text1);
                $sms1->from = 'SEKTAFOOD';
                $client->smsSend($sms1);
            }catch (Exception $e){
                Debugger::log('Order: cant send sms to '.$phone1);
                $view = $this->module->view('Admin\\Error');
                $view->render(['code'=>8, 'message'=>'Cant send sms: '.$phone1]);
                return;
            }

        }
        if (!is_null(QS_validate($orderData['Email'], TYPE_EMAIL))){
            $this->sendOrderEmail($orderData['Email'], $order);
        }
        $cart->clearCart();

        $view = $this->module->view('Order');
        $view->render($order);
    }

    /**
     * @param $email
     * @param $order
     * @return bool
     */
    protected function sendOrderEmail($email, $order){
        $mail = new Basic();
        $mail->setSMTP(['Auth'=>true, 'Username'=>'knuzz_1', 'Password'=>'6EniU8fYZJ']);
        //$mail = Basic::getSMTPMailer(['Auth'=>true, 'Username'=>'knuzz_1', 'Password'=>'6EniU8fYZJ']);
        $mail->addAddress($email);
        $mail->setSubject('Информация о заказе');
        $mail->setVar('header', 'СПАСИБО! ВАШ ЗАКАЗ ПРИНЯТ');
        $mail->render(QS_path(['templates', 'emails','order','success.php'], false), ['order'=>$order]);
        $result = $mail->send();
        $mail->clear();

        /*
        $mail = Mailer::getSMTPMailer(['Auth'=>true, 'Username'=>'knuzz_1', 'Password'=>'6EniU8fYZJ']);
        $mail->addAddress($email);
        $mail->setSubject('Заказ на сайте #SektaFood!');
        //$mail->Subject = 'Заказ на сайте #SektaFood!';
        $mail->addEmbeddedImage(QS_path(['templates','site', 'images', 'logo.png'], false), 'logo');
        $mail->addEmbeddedImage(QS_path(['templates','site', 'images', 'logo_black.png'], false), 'logo_black');
        $variables = [
            'images'=>['logo'=>['src'=>'cid:logo'], 'logo_black'=>['src'=>'cid:logo_black']],
            'order'=>$order->toArray()
        ];


        $template = Mailer::getTemplate(QS_path(['templates', 'site', 'email', 'order_success.php'], false), $variables);
        $mail->isHTML();
        $mail->msgHTML($template);
        $result = $mail->send();
        $mail->clear();
        */
        return $result;
    }

    /**
     * @param string $response
     * @return array
     */
    protected function loadReCaptchaData($response){
        $url = 'https://www.google.com/recaptcha/api/siteverify';
        $secret = '6LdwxSkTAAAAAGTpk9vLh3nUZABH4k7Ny_kk54c-';
        $ip = $this->getInput()->getIp();
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query(['secret'=>$secret,'response'=>$response,'remoteip'=>strval($ip)])
        ]);
        $data = curl_exec($curl);
        return json_decode($data, true);
    }

    protected function fixDBAddPromoCode(array $data){
        $em = $this->getEntityManager();
        $defaultCode = $this->module->getCart()->getDefaultPromoCode();
        $defaultCodeStr = json_encode($defaultCode);
        $query = $em->getEntityQuery('Order');
        $result = $query->load();
        $c = 0;
        foreach ($result as $ent){
            if (!$ent->getPromoCodeData()){
                $ent->setPromoCodeData($defaultCodeStr);
                $ent = $query->save($ent);
                $c++;
            }
        }
        var_dump($c);
    }
} 