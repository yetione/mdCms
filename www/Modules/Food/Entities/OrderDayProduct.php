<?php
namespace Modules\Food\Entities;


use Core\DataBase\Model\Entity;

class OrderDayProduct extends Entity{

    protected function init(){}

    /**
     * @param float $value
     * @return $this
     */
    public function setPrice($value){
        return $this->_setTypedValue('Price', $value, TYPE_FLOAT);
    }

    /**
     * @param float $value
     * @return $this
     */
    public function setAmount($value){
        return $this->_setTypedValue('Amount', $value, TYPE_INT);
    }
}