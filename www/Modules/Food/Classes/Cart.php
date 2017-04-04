<?php
namespace Modules\Food\Classes;


use Core\Core;
use Core\DataBase\Connection;
use Core\DataBase\Model\Entity;
use Core\Debugger;
use Core\Event\EventManager;

class Cart {

    /**
     * @var array
     */
    protected $data;

    /**
     * @var Core
     */
    protected $core;

    /**
     * @var float
     */
    protected $totalPrice;

    /**
     * @var float
     */
    protected $totalDiscountSum;

    /**
     * @var float
     */
    protected $totalDiscountPrice;

    /**
     * @var int
     */
    protected $productsCount;

    /**
     * @var int
     */
    protected $uniqueProducts;

    /**
     * @var int
     */
    protected $daysSelected;

    /**
     * @var array
     */
    protected $promoCode = [];

    /**
     * @var array
     */
    protected $cartData = [];

    /**
     * @var bool
     */
    protected $isBlocked = false;

    /**
     * @var EventManager
     */
    protected $eventManager;

    protected $blockingReason = [
        'code'=>0,
        'message'=>'',
    ];

    /**
     * @var Connection
     */
    protected $db = null;

    /**
     * @var Entity */
    protected $city = null;

    protected $minProductsToCategory = [
        'category_1'=>2
    ];

    protected $maxOrdersToDay = [
        'msk'=>40
    ];

    const SESSION_KEY = 'Food.cart';

    const SET = 1;


    public function __construct(){
        //TODO: Событие обновления корзины
        $this->clearCart();
    }

    /**
     * @param $date
     * @param $product
     * @param $price
     * @param int $amount
     */
    public function addProduct($date, $product, $price, $amount=1){
        if (!isset($this->data[$date])){
            $this->data[$date] = array('date'=>$date,'price'=>0, 'products'=>array(), 'productsCount'=>0);
        }
        if (!isset($this->data[$date]['products'][$product->getId()])){
            $this->data[$date]['products'][$product->getId()] = array('amount'=>0, 'price'=>$price, 'id'=>$product->getId(), 'category_id'=>$product->getCategoryId());
        }
        $this->data[$date]['products'][$product->getId()]['amount']+= $amount;
        $this->update();
    }

    public function setProductAmount($date, $product, $amount){
        if (isset($this->data[$date])){
            if (isset($this->data[$date]['products'][$product->getId()])){
                if ($amount == 0){
                    unset($this->data[$date]['products'][$product->getId()]);
                }elseif ($amount > 0){
                    $this->data[$date]['products'][$product->getId()]['amount'] = $amount;
                    if ($this->data[$date]['products'][$product->getId()]['amount'] == 0){
                        unset($this->data[$date]['products'][$product->getId()]);
                    }
                }else{
                    return false;
                }
                //$this->update();
                return true;
            }
        }
        $this->update();
        return true;
    }

    public function removeProduct($date, $product, $amount=0){
        if (isset($this->data[$date])){
            if (isset($this->data[$date]['products'][$product->getId()])){
                if ($amount == 0){
                    unset($this->data[$date]['products'][$product->getId()]);
                }elseif ($amount > 0 && $amount <= $this->data[$date]['products'][$product->getId()]['amount']){
                    $this->data[$date]['products'][$product->getId()]['amount'] -= $amount;
                    if ($this->data[$date]['products'][$product->getId()]['amount'] == 0){
                        unset($this->data[$date]['products'][$product->getId()]);
                    }
                }elseif ($this->data[$date]['products'][$product->getId()]['amount'] <= 0){
                    unset($this->data[$date]['products'][$product->getId()]);
                }else{
                    $this->update();
                    return false;
                }
                $this->update();
                return true;
            }
        }
        $this->update();
        return false;
    }

    public function update(){
        $productsCount = 0;
        $totalPrice = 0;
        $totalDiscountSum = 0;
        $uniqueProducts = 0;
        $productsToCategory = [];
        $isBlocked = false;
        $blockingReason = ['code'=>0, 'message'=>''];
        $promoCode = $this->getPromoCode();
        $discountToDay = 0;
        foreach ($this->data as $date=>&$dateInfo){
            $datePrice = 0;
            $productsToCategory[$date] = [];
            foreach ($dateInfo['products'] as $pId=>$data){
                $uniqueProducts++;
                $productsCount += $data['amount'];
                $totalPrice += $data['price']*$data['amount'];
                $datePrice += $data['price']*$data['amount'];
                $k = 'category_'.$data['category_id'];
                if (!isset($productsToCategory[$date][$k])){
                    $productsToCategory[$date][$k] = 0;
                }
                $productsToCategory[$date][$k] += $data['amount'];
            }
            $dateInfo['price'] = $datePrice;
            $dateInfo['deliveryPrice'] = 150; //TODO: Стоимость доставки
            $totalPrice += $dateInfo['deliveryPrice'];

            $dateInfo['productsCount'] = count($dateInfo['products']);
            if ($dateInfo['productsCount'] == 0){
                unset($this->data[$date]);
            }
            $dateInfo['discountPrice'] = $datePrice;
            $dateInfo['discountSum'] = 0;

            if ($promoCode['Data']['Type'] == 2){
                $value = (float) $promoCode['Data']['Options']['Value'];
                $discountSum = 0;
                if ($promoCode['Data']['Options']['Units'] == 'percents'){
                    if ($value <= 100){
                        $discountSum = round($dateInfo['price'] * ($value / 100.0), 2);
                    }
                }elseif ($promoCode['Data']['Options']['Units'] == 'value'){
                    if ($value <= $dateInfo['price']){
                        $discountSum = $value;
                    }
                }else{
                    Debugger::log('Cart::update : invalid promo code type '.$promoCode['Code'].' '.$promoCode['Data']['Type']);
                }
                $dateInfo['discountPrice'] = $dateInfo['price'] - $discountSum;
                $dateInfo['discountSum'] = $discountSum;
                $totalDiscountSum += $discountSum;
            }
            elseif ($promoCode['Data']['Type'] == 1 && $promoCode['Data']['Options']['Units'] == 'value'){
                $discountToDay = round((float) $promoCode['Data']['Options']['Value'] / count($this->data), 2);
                if ($dateInfo['price'] >= $discountToDay){
                    $dateInfo['discountPrice'] = $dateInfo['price'] - $discountToDay;
                    $dateInfo['discountSum'] = $discountToDay;
                    $totalDiscountSum += $discountToDay;
                }else{
                    $discountSum = $dateInfo['discountPrice'];
                    $t = $discountToDay - $dateInfo['discountPrice'];
                    $dateInfo['discountPrice'] = 0;
                    if ($dateInfo['deliveryPrice'] >= $t){
                        $dateInfo['deliveryPrice'] -= $t;
                        $discountSum += $t;
                    }else{
                        $discountSum += $dateInfo['deliveryPrice'];
                        $dateInfo['deliveryPrice'] = 0;
                    }
                    $dateInfo['discountSum'] = $discountSum;
                    $totalDiscountSum += $discountSum;
                }
            }
            $dateInfo['totalPrice'] = round($dateInfo['discountPrice'] + $dateInfo['deliveryPrice'], 2);
        }
        $this->totalPrice = $totalPrice;
        $this->totalDiscountSum = $totalDiscountSum;
        $this->totalDiscountPrice = $this->totalPrice - $this->totalDiscountSum;
        $this->productsCount = $productsCount;
        $this->uniqueProducts = $uniqueProducts;

        $this->cartData['DaysCount'] = count($this->data);

        if ($this->getPromoCode()){
            $pCode = $this->getPromoCode();
            $pData = $pCode['Data'];

            switch ($pData['Type']){
                case 1:
                    $value = (float) $pData['Options']['Value'];
                    if ($pData['Options']['Units'] == 'percents'){
                        if ($value <= 100){
                            $this->cartData['NewPrice'] = $this->totalPrice - ($this->totalPrice * ($value / 100.0));
                        }
                    }elseif ($pData['Options']['Units'] == 'value'){
                        if ($value <= $this->totalPrice){
                            $this->cartData['NewPrice'] = $this->totalPrice - $value;
                        }
                        /*$discountToDay = $value / $this->cartData['DaysCount'];
                        $this->totalDiscountPrice = 0;
                        $this->totalDiscountSum = 0;
                        foreach ($this->data as $date=>$dateInfo){
                            if ($dateInfo['discountPrice'] >= $discountToDay){
                                //var_dump('grate', $discountToDay, $dateInfo['discountPrice'], $date);
                                $dateInfo['discountPrice'] -= $discountToDay;
                                $dateInfo['discountSum'] = $discountToDay;
                                $this->totalDiscountPrice += $dateInfo['discountPrice']+$dateInfo['deliveryPrice'];
                                $this->totalDiscountSum += $discountToDay;
                            }else{
                                //var_dump('less', $discountToDay, $dateInfo['discountPrice'], $date);
                                $discountSum = $dateInfo['discountPrice'];
                                $t = $discountToDay - $dateInfo['discountPrice'];
                                $dateInfo['discountPrice'] = 0;
                                if ($dateInfo['deliveryPrice'] >= $t){
                                    $dateInfo['deliveryPrice'] -= $t;
                                    $discountSum += $t;
                                }else{
                                    $discountSum += $dateInfo['deliveryPrice'];
                                    $dateInfo['deliveryPrice'] = 0;
                                }
                                $dateInfo['discountSum'] = $discountSum;
                                $this->totalDiscountPrice += $dateInfo['discountPrice']+$dateInfo['deliveryPrice'];
                                $this->totalDiscountSum += $discountSum;
                            }
                        }*/
                    }
                    break;
                case 2:
                    $value = (float) $pData['Options']['Value'];
                    //foreach ($this->data as $date=>$dateInfo)
                    break;
                default:
                    break;
            }
        }

        $this->daysSelected = count($this->data);
        //var_dump($productsToCategory);
        foreach ($productsToCategory as $date=>$cat){
            foreach ($this->minProductsToCategory as $k=>$v){
                if (isset($productsToCategory[$date][$k]) && $productsToCategory[$date][$k] < $v){
                    $isBlocked = true;
                    $blockingReason = ['code'=>1, 'message'=>'Not enough product in cart by category'];
                    break;
                }
            }
        }



        if (!is_null($this->getDb())){
            if (!is_null($this->getCity())){
                $k = $this->getCity()->getMachine();
                if (isset($this->maxOrdersToDay[$k])){
                    $start = date('Y-m-d 00:00:00', time());
                    $end = date('Y-m-d 23:59:59', time());
                    $sql = 'SELECT COUNT(`o`.`id`) AS `co` FROM `order` `o` WHERE `o`.`date_created` BETWEEN ? AND ?';
                    $stm = $this->getDb()->prepare($sql);

                    if ($stm->execute([$start, $end])){
                        $r = $stm->fetch(\PDO::FETCH_COLUMN)[0];
                        if ($r > $this->maxOrdersToDay[$k]){
                            $isBlocked = true;
                            $blockingReason = ['code'=>2, 'message'=>'Max orders count'];
                        }
                    }
                }
            }
        }
        if (count($this->data) == 0){
            $this->isBlocked = true;
            $this->blockingReason = ['code'=>3, 'message'=>'Cart is empty'];
        }else{
            $this->isBlocked = $isBlocked;
            $this->blockingReason = $blockingReason;
        }

        asort($this->data);
    }

    public function clearDate($date){
        if (isset($this->data[$date])){
            $this->data[$date] = array();
            //$this->update();
            return true;
        }
        $this->update();
        return false;
    }

    public function clearCart(){
        $this->data = array();
        $this->setDefaultPromoCode();
        $this->update();
    }

    /**
     * @return array
     */
    public function getData(){
        return $this->data;
    }

    /**
     * @param array $data
     */
    public function setData($data){
        $this->data = $data;
    }

    /**
     * @return Core
     */
    public function getCore(){
        return $this->core;
    }

    /**
     * @param Core $core
     */
    public function setCore($core){
        $this->core = $core;
    }

    /**
     * @return float
     */
    public function getTotalPrice(){
        return $this->totalPrice;
    }

    /**
     * @param float $totalPrice
     */
    public function setTotalPrice($totalPrice){
        $this->totalPrice = $totalPrice;
    }

    /**
     * @return int
     */
    public function getProductsCount(){
        return $this->productsCount;
    }

    /**
     * @param int $productsCount
     */
    public function setProductsCount($productsCount){
        $this->productsCount = $productsCount;
    }

    /**
     * @return int
     */
    public function getUniqueProducts(){
        return $this->uniqueProducts;
    }

    /**
     * @param int $uniqueProducts
     */
    public function setUniqueProducts($uniqueProducts){
        $this->uniqueProducts = $uniqueProducts;
    }

    /**
     * @return int
     */
    public function getDaysSelected(){
        return $this->daysSelected;
    }

    /**
     * @param int $daysSelected
     */
    public function setDaysSelected($daysSelected){
        $this->daysSelected = $daysSelected;
    }

    /**
     * @return array
     */
    public function toArray(){
        $result = array();
        $result['TotalPrice'] = $this->getTotalPrice();
        $result['ProductsCount'] = $this->getProductsCount();
        $result['UniqueProducts'] = $this->getUniqueProducts();
        $result['Data'] = $this->getData();
        $result['DaysSelected'] = $this->getDaysSelected();
        $result['CartData'] = $this->cartData;
        $result['PromoCode'] = $this->getPromoCode();
        $result['IsBlocked'] = $this->isBlocked;
        $result['BlockingReason'] = $this->blockingReason;
        $result['TotalDiscountSum'] = $this->getTotalDiscountSum();
        $result['TotalDiscountPrice'] = $this->getTotalDiscountPrice();
        return $result;
    }

    /**
     * @return array
     */
    public function getPromoCode(){
        return $this->promoCode;
    }

    /**
     * @param Entity|array $promoCode
     */
    public function setPromoCode($promoCode){
        $this->promoCode = is_array($promoCode) ? $promoCode : $promoCode->toArray();
        $this->update();
    }

    public function setDefaultPromoCode(){
        $this->setPromoCode($this->getDefaultPromoCode());

    }

    /**
     * @return array
     */
    public function getDefaultPromoCode(){
        return ['Id'=>0, 'Code'=>null, 'Data'=>['Type'=>0, 'Options'=>[]]];
    }

    public function deletePromoCode(){
        if ($this->promoCode['Id'] != 0){
            $pData = $this->promoCode['Data'];
            switch ($pData['Type']){
                case 1:
                    unset($this->cartData['NewPrice']);
                    break;
                default:
                    break;
            }
            $this->setDefaultPromoCode();
            return true;
        }
        return false;
    }

    /**
     * @return Connection
     */
    public function getDb(){
        return $this->db;
    }

    /**
     * @param  $db
     */
    public function setDb($db){
        $this->db = $db;
    }

    public function unsetDb(){
        $this->db = null;
    }

    /**
     * @return Entity
     */
    public function getCity(){
        return $this->city;
    }

    /**
     * @param Entity $city
     */
    public function setCity($city){
        $this->city = $city;
    }

    public function unsetCity(){
        $this->city = null;
    }

    /**
     * @return boolean
     */
    public function isIsBlocked(){
        return $this->isBlocked;
    }

    /**
     * @param float $totalDiscountSum
     * @return Cart
     */
    public function setTotalDiscountSum($totalDiscountSum){
        $this->totalDiscountSum = $totalDiscountSum;
        return $this;
    }

    /**
     * @return float
     */
    public function getTotalDiscountSum(){
        return $this->totalDiscountSum;
    }

    /**
     * @return float
     */
    public function getTotalDiscountPrice(){
        return $this->totalDiscountPrice;
    }

    /**
     * @param float $totalDiscountPrice
     * @return $this
     */
    public function setTotalDiscountPrice($totalDiscountPrice){
        $this->totalDiscountPrice = $totalDiscountPrice;
        return $this;
    }


}