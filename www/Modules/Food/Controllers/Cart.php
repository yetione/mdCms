<?php
namespace Modules\Food\Controllers;


use Core\DataBase\Exception\StatementExecuteError;
use Core\Module\Base\Controller;
use Modules\Food\Food;
use Modules\GeoLocation\GeoLocation;
use Modules\Users\Users;

class Cart extends Controller{

    /**
     * @var Food
     */
    protected $module;

    protected function checkInput(){
        $input = $this->module->getCore()->getInput();
        $date = $input->get('Date', null, TYPE_STRING);
        $pId = $input->get('ProductId', 0, TYPE_INT);
        $amount = $input->get('Amount', 1, TYPE_INT);

    }

    public function addProduct(array $data){
        $input = $this->module->getCore()->getInput();
        $date = $input->get('Date', null, TYPE_STRING);
        $pId = $input->get('ProductId', 0, TYPE_INT);
        $amount = $input->get('Amount', 1, TYPE_INT);
        if (!$this->checkDate($date)){
            //TODO: Error
            return;
        }
        if ($pId <= 0){
            //TODO: Error
            return;
        }
        if ($amount < 0){
            //TODO: Error
            return;
        }
        $dateObj = new \DateTime($date);
        $nowObj = new \DateTime();
        if ($nowObj >= $dateObj){
            //TODO: Error
            return;
        }

        $product = $this->module->getCore()->getEntityManager()->getEntityQuery('Product')->findById($pId)->loadOne(false);
        if (is_null($product)){
            //TODO: Error
            return;
        }
        $cart = $this->module->getCart();
        /**
         * @var GeoLocation $geoLocation
         */
        $geoLocation = $this->module->getManager()->getModule('GeoLocation');
        $cityData = $geoLocation->getGeoData();
        $methodName = 'getPrice'.ucfirst($cityData->getMachine());
        $price = (float) $product->$methodName();

        $cart->addProduct($date, $product, $price, $amount);
        $this->module->saveCart();

        $view = $this->module->view('Cart');
        $view->render($cart);
    }

    public function setProductAmount(array $data){
        $input = $this->module->getCore()->getInput();
        $date = $input->get('Date', null, TYPE_STRING);
        $pId = $input->get('ProductId', 0, TYPE_INT);
        $amount = $input->get('Amount', -1, TYPE_INT);
        if (!$this->checkDate($date)){
            //TODO: Error
            return;
        }
        if ($pId <= 0){
            //TODO: Error
            return;
        }
        if ($amount < 0){
            //TODO: Error
            return;
        }
        $dateObj = new \DateTime($date);
        $nowObj = new \DateTime();
        if ($nowObj >= $dateObj){
            //TODO: Error
            return;
        }
        $product = $this->module->getCore()->getEntityManager()->getEntityQuery('Product')->findById($pId)->loadOne(false);
        if (is_null($product)){
            //TODO: Error
            return;
        }
        $cart = $this->module->getCart();

        $cart->setProductAmount($date, $product, $amount);
        $this->module->saveCart();

        $view = $this->module->view('Cart');
        $view->render($cart);
    }

    public function removeProduct(array $data){
        $input = $this->module->getCore()->getInput();
        $date = $input->get('Date', null, TYPE_STRING);
        $pId = $input->get('ProductId', 0, TYPE_INT);
        $amount = $input->get('Amount', 1, TYPE_INT);

        if (!$this->checkDate($date)){

            //TODO: Error
            return;
        }
        if ($pId <= 0){
            //TODO: Error
            return;
        }
        if ($amount < 0){
            //TODO: Error
            return;
        }
        $dateObj = new \DateTime($date);
        $nowObj = new \DateTime();
        if ($nowObj >= $dateObj){
            //TODO: Error
            return;
        }
        $product = $this->module->getCore()->getEntityManager()->getEntityQuery('Product')->findById($pId)->loadOne(false);
        if (is_null($product)){
            //TODO: Error
            return;
        }
        $cart = $this->module->getCart();
        $cart->removeProduct($date, $product, $amount);
        $this->module->saveCart();

        $view = $this->module->view('Cart');
        $view->render($cart);
    }

    public function getCart(array $data){
        $cart = $this->module->getCart();
        $cart->update();
        $view = $this->module->view('Cart');
        $view->render($cart);
    }

    public function clearCart(array $data){
        $this->module->getCart()->clearCart();
        $this->module->saveCart();
        $view = $this->module->view('Cart');
        $view->render($this->module->getCart());
    }

    public function activatePromoCode(array $data){
        /*
        $cUser = $this->module->getCore()->getSession()->get(Users::CURRENT_USER_KEY);
        $cUserId = (int) $cUser->getId() > 0 ? $cUser->getId() : 0;
        if ($cUserId == 0){
            $view = $this->module->view('Admin\\Error');
            $view->render(['code'=>6, 'message'=>'You must log in before use promo code.']);
            return;
        }
        */
        $input = $this->getInput();
        $code = trim($input->get('Code', ''));
        if ($code == ''){
            $view = $this->module->view('Admin\\Error');
            $view->render(['code'=>1, 'message'=>'Code is not set']);
            return;
        }
        $em = $this->getEntityManager();
        /*
        $query = $em->getEntityQuery('Order');
        $query->findByUserId($cUserId)->findByPromoCodeName($code)->load(false, true);
        */

        $query = $em->getEntityQuery('PromoCode');
        $query->findByCode($code);
        try{
            $code = $query->loadOne(false, true);
            if (!$code){
                $view = $this->module->view('Admin\\Error');
                $view->render(['code'=>2, 'message'=>'Code code not found']);
                return;
            }
            /*
            $sql = "SELECT `o`.`id` FROM `order` `o` WHERE `o`.`promo_code_name` = ? AND `o`.`user_id` = ?";
            $stm = $this->module->getCore()->getDb()->prepare($sql);
            $stm->bindValue(1, $code->getCode());
            $stm->bindValue(2, $cUserId, \PDO::PARAM_INT);
            $result = $stm->execute();
            if (!$result || $stm->fetch(\PDO::FETCH_ASSOC) !== false){
                $view = $this->module->view('Admin\\Error');
                $view->render(['code'=>7, 'message'=>'Promo code already use.']);
                return;
            }
            */
        }catch (StatementExecuteError $e){
            $view = $this->module->view('Admin\\Error');
            $view->render(['code'=>3, 'data'=>$e]);
            return;
        }
        $code->setData(json_decode($code->getData(), true));
        $cart = $this->module->getCart();
        if ($cart->getPromoCode()['Id'] == 0){
            $now = time();
            if ($now < $code->getStartDate() || $now > $code->getExpireDate() || $code->getActive() == 0){
                $view = $this->module->view('Admin\\Error');
                $view->render(['code'=>4, 'message'=>'Promo code expire or not active.']);
                return;
            }
            if ($code->getType() == 2 && $code->getUsed() == 1){
                $view = $this->module->view('Admin\\Error');
                $view->render(['code'=>6, 'message'=>'Promo code already used.']);
                return;
            }
            $cart->setPromoCode($code);
            $this->module->saveCart();
            $view = $this->module->view('Cart');
            $view->render($cart);
            return;
        }else{
            /*$view = $this->module->view('Cart');
            $view->render($cart);*/
            $view = $this->module->view('Admin\\Error');
            $view->render(['code'=>5, 'message'=>'Promo code already set.']);
            return;
        }
    }

    public function deletePromoCode(array $data){
        /*
        $cUser = $this->module->getCore()->getSession()->get(Users::CURRENT_USER_KEY);
        $cUserId = (int) $cUser->getId() > 0 ? $cUser->getId() : 0;
        if ($cUserId == 0){
            $view = $this->module->view('Admin\\Error');
            $view->render(['code'=>1, 'message'=>'You must log in before use promo code.']);
            return;
        }
        */
        $cart = $this->module->getCart();

        if (!$cart->deletePromoCode()){
            $view = $this->module->view('Admin\\Error');
            $view->render(['code'=>2, 'message'=>'Promo code does not set.']);
            return;
        }else{
            $this->module->saveCart();
            $view = $this->module->view('Cart');
            $view->render($cart);
        }
    }

    protected function checkDate($date, $format='Y-m-d'){
        $d = \DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) == $date;
    }


} 