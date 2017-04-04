<?php
namespace Modules\Food\Entities;


use Core\DataBase\Model\Entity;

class Order extends Entity{

    protected $promoCode = null;

    protected function init(){
        //if ($this->getId()) {$this->setIsNew(false);$this->recount(true, true);}
    }

    public function recount($saveOrder=true, $saveDays=false){
        $days = $this->getProducts();
        $promoCode = \json_decode($this->getPromoCodeData(), true);
        /**
         * @var OrderDay $day
         */
        $totalPrice = 0;
        foreach ($days as $day){
            if ($promoCode['Data']['Type'] == 2){
                $discount = $promoCode['Data']['Options']['Value'].($promoCode['Data']['Options']['Units'] == 'percents' ? '%' : '');
                $day->setDiscount($discount);
            }elseif ($promoCode['Data']['Type'] == 1 && $promoCode['Data']['Options']['Units'] == 'value'){
                $discountToDay = round((float) $promoCode['Data']['Options']['Value'] / count($days), 2);
                $day->setDiscount($discountToDay);
            }
            $day->recount($saveDays);
            $totalPrice += $day->getTotalPrice();
        }
        /*if ($promoCode['Data']['Type'] == 1){
            $value = round((float) $promoCode['Data']['Options']['Value'], 2);
            if ($promoCode['Data']['Options']['Units'] == 'percents'){
                if ($value <= 100){
                    $totalPrice = round($totalPrice - ($totalPrice * ($value / 100.0)), 2);
                }
            }elseif ($promoCode['Data']['Options']['Units'] == 'value'){
                if ($value <= $totalPrice){
                    $totalPrice = $totalPrice - $value;
                }
            }
        }*/
        $this->setPrice($totalPrice);
        if ($saveOrder) $this->save();

    }

    public function getPromoCode(){
        if (is_null($this->promoCode) || gettype($this->properties['PromoCodeData']) == 'string'){
            $this->promoCode = \json_decode($this->getPromoCodeData(), true);
        }
        return $this->promoCode;
    }

    /**
     * @param float $value
     * @return $this
     */
    public function setPrice($value){
        return $this->_setTypedValue('Price', $value, TYPE_FLOAT);
    }
}