<?php
namespace Modules\Food;


use Core\DataBase\Entities\City;
use Core\Debugger;
use Core\Event\EventVar;
use Core\GeoIp\GeoIpDatabase;
use Core\Module\Base\Module;
use Core\Response\Response;
use Modules\Food\Classes\Cart;
use Modules\GeoLocation\GeoLocation;

class Food extends Module{

    protected $moduleName = 'Food';

    /**
     * @var GeoLocation
     */
    protected $geoLocation;

    const SESSION_CART_KEY = 'Food.cart';
    const COOKIE_CART_KEY = 'Food_cart';

    /**
     * @var Cart
     */
    protected $cart;


    protected function init(array $configs){
        $this->core->getEventManager()->hook('Application.Load', array($this, 'onAppLoad'));
        $this->core->getEventManager()->hook('Application.buildResponse', array($this, 'onBuildResponse'));
    }


    public function onAppLoad(EventVar $ev){
        $this->getCart();
        //$this->fixOrders();
        //$this->recountOrder();
    }

    public function onBuildResponse(EventVar $ev){
        //var_dump($ev->get('response'));
        if ($ev->get('response')->getFormat() === Response::FORMAT_HTML){
            //$ev->get('response')->setTitlePostfix('| Sekta Food');
        }
    }

    public function saveCart(){
        $cart = $this->getCart();
        $cart->unsetDb();
        $cart->unsetCity();
        $session = $this->getCore()->getSession();
        $session->set(self::SESSION_CART_KEY, $cart);
        $cart->setDb($this->core->getDb());
        $cart->setCity($this->getCity());
    }

    public function deleteCart(){
        $session = $this->getCore()->getSession();
        $session->set(self::SESSION_CART_KEY, null);
    }

    /**
     * @return Cart
     * @throws \Core\Session\Exception\StateError
     */
    public function getCart(){
        if (!$this->cart){
            $session = $this->getCore()->getSession();
            $cart = $session->get(self::SESSION_CART_KEY, null);
            if (is_null($cart)){
                $cart = new Cart();
            }
            $this->cart = $cart;
            $this->cart->setDb($this->core->getDb());
            $this->cart->setCity($this->getCity());
        }
        return $this->cart;
    }

    public function getCity(){
        $city = $this->getCore()->getSession()->get(GeoLocation::SESSION_KEY);
        if (!is_null($city)){
            //$city->_setEntityManager($this->getCore()->getEntityManager());
            //$city->_setEntityMetadata($this->getCore()->getEntityManager()->getEntityMetadata('City'));
            //$t = $city->toArray();
            //$city->_setEntityManager(null);
            //$city->_setEntityMetadata(null);
            //$city = $t;


        }

        return $city;
    }

    public function getModuleTabs(){
        return [
            ['title'=>'Модуль', 'action'=>''],
        ];
    }

    /**
     * Пересчитывает сумму всех заказов, если не передан ID заказа
     * @param $orderId
     * @return bool
     */
    public function recountOrder($orderId=0){
        $sql = "UPDATE `order_day` `od` SET `od`.`price` = (SELECT SUM(`odp`.`price`*`odp`.`amount`) FROM `order_day_product` `odp` WHERE `odp`.`order_day_id` = `od`.`id`)".($orderId > 0 ? " WHERE `od`.`order_id`= ?" : " WHERE 1");
        $sql2 = "UPDATE `order` `o` SET `o`.`price` = (SELECT SUM(`od`.`price`+`od`.`delivery_price`) FROM `order_day` `od` WHERE `od`.`order_id` = `o`.`id`)".($orderId > 0 ? " WHERE `od`.`order_id`= ?" : " WHERE 1");
        $db = $this->getCore()->getDb();
        $updateOrderDay = $db->prepare($sql);
        $updateOrder = $db->prepare($sql2);
        if ($orderId > 0){
            $updateOrderDay->bindValue(1, $orderId, \PDO::PARAM_INT);
            $updateOrder->bindValue(1, $orderId, \PDO::PARAM_INT);
        }
        if ($updateOrderDay->execute() && $updateOrder->execute()){
            Debugger::log('Food::recountOrder: Success '.$orderId);
            return true;
        }
        return false;
    }

    /**
     * Удаляет несуществубщие данные о заказаз
     * @return int
     */
    public function fixOrders(){
        $stm = $this->getCore()->getDb()->prepare('DELETE `odp` FROM `order_day_product` `odp` LEFT JOIN `product` `p` ON `odp`.`product_id` = `p`.`id` WHERE `p`.`id` IS NULL');
        if (!$stm->execute()){
            Debugger::log('Food::fixOrderDaysProduct: error 1 '.$stm->errorCode());
            return false;
        }
        $stm = $this->getCore()->getDb()->prepare('DELETE `odp` FROM `order_day_product` `odp` LEFT JOIN `order_day` `od` ON `odp`.`order_day_id` = `od`.`id` WHERE `od`.`id` IS NULL');
        if (!$stm->execute()){
            Debugger::log('Food::fixOrderDaysProduct: error 2 '.$stm->errorCode());
            return false;
        }

        $stm = $this->getCore()->getDb()->prepare('DELETE `od` FROM `order_day` `od` LEFT JOIN `order_day_product` `odp` ON `od`.`id` = `odp`.`order_day_id` WHERE `odp`.`order_day_id` IS NULL');
        if (!$stm->execute()){
            Debugger::log('Food::fixOrderDaysProduct: error 3 '.$stm->errorCode());
            return false;
        }

        $stm = $this->getCore()->getDb()->prepare('DELETE `od` FROM `order_day` `od` LEFT JOIN `order` `o` ON `od`.`order_id` = `o`.`id` WHERE `o`.`id` IS NULL');
        if (!$stm->execute()){
            Debugger::log('Food::fixOrderDaysProduct: error 4 '.$stm->errorCode());
            return false;
        }

        $stm = $this->getCore()->getDb()->prepare('DELETE `o` FROM `order` `o` LEFT JOIN `order_day` `od` ON `o`.`id` = `od`.`order_id` WHERE `od`.`order_id` IS NULL');
        if (!$stm->execute()){
            Debugger::log('Food::fixOrderDaysProduct: error 4 '.$stm->errorCode());
            return false;
        }
        Debugger::log('Food:fixOrders: Success');
        return true;
    }
} 