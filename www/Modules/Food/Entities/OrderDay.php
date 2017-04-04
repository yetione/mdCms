<?php
namespace Modules\Food\Entities;


use Core\DataBase\Model\Entity;

class OrderDay extends Entity{

    protected $discountRegExp = '/^([0-9]*\.?[0-9]+)([%]?)$/';

    /**
     * @var int
     */
    protected $discountSum = 0;

    protected function init(){}

    public function recount($save=false){
        $products = $this->getProducts();
        $this->setDeliveryPrice(150);
        $totalPrice = 0;
        foreach ($products as $product){
            $totalPrice += ((int) $product->getAmount() * (float) $product->getPrice());
        }
        $this->setPrice($totalPrice);

        $discountSum = $this->getDiscountSum(true);
        if ($totalPrice >= $discountSum){
            $this->setDiscountPrice($totalPrice-$discountSum);
        }else{
            $discountSumNew = $totalPrice;
            $t = $discountSum - $totalPrice;
            $this->setDiscountPrice(0);
            //$dateInfo['discountPrice'] = 0;
            if ((float) $this->getDeliveryPrice() >= $t){
                $this->setDeliveryPrice((float) $this->getDeliveryPrice() - $t);
                $discountSumNew += $t;
            }else{
                $discountSumNew += $this->getDeliveryPrice();
                $this->setDeliveryPrice(0);
            }
            //$this->setDiscountSum($discountSumNew);
            //$dateInfo['discountSum'] = $discountSumNew;
        }

        if ($save && $this->getId()){
            $this->save();
        }
    }

    public function getDiscountPrice(){
        if (!$this->properties['DiscountPrice']){
            $totalPrice = (float)$this->getPrice();
            $discountSum = $this->getDiscountSum(true);
            if ($totalPrice >= $discountSum){
                $this->setDiscountPrice($totalPrice-$discountSum);
            }else{
                $this->setDeliveryPrice(150);
                $discountSumNew = $totalPrice;
                $t = $discountSum - $totalPrice;
                $this->setDiscountPrice(0);
                //$dateInfo['discountPrice'] = 0;
                //var_dump('this', $this->getDeliveryPrice());
                if ((float) $this->getDeliveryPrice() >= $t){
                    $this->setDeliveryPrice((float) $this->getDeliveryPrice() - $t);
                    $discountSumNew += $t;
                }else{

                    $discountSumNew += $this->getDeliveryPrice();
                    $this->setDeliveryPrice(0);
                }
                //$this->setDiscountSum($discountSumNew);
                //$dateInfo['discountSum'] = $discountSumNew;
            }

            //$this->setDiscountPrice($totalPrice -(float)$this->getDiscountSum());
        }
        return $this->properties['DiscountPrice'];
    }

    /**
     * @return int
     */
    public function getTotalPrice(){
        $t = (float) $this->getDiscountPrice() + (float) $this->getDeliveryPrice();
        return $t >= 0 ? $t : 0;
    }

    /**
     * @param string $discount
     * @return $this
     */
    public function setDiscount($discount){
        $discount = strval($discount);
        if (empty($discount) || preg_match($this->discountRegExp, $discount)){
            $this->properties['Discount'] = $discount;
            return $this;
        }
        throw new \UnexpectedValueException(__CLASS__.'::setDiscount : discount format not valid. '.$discount);
    }

    /**
     * @param bool $update
     * @return int
     */
    public function getDiscountSum($update=false){
        if ($this->discountSum == 0 || $update){
            $this->discountSum = 0;
            $discount = $this->getDiscount();
            $totalPrice = (float) $this->getPrice();
            if (preg_match($this->discountRegExp, strval($discount), $matches)){
                $d = (float) $matches[1];
                $this->discountSum =  round($matches[2] == '%' ? $totalPrice*($d/100.0) : $d, 2);
            }
        }
        return $this->discountSum ;
    }


}