<?php
namespace Core\DataBase\Utils;


trait SetOptions {

    protected function setOptions(array $options){
        foreach ($options as $option => $value){
            $methodName = 'set'.ucfirst($option);
            if (method_exists($this, $methodName)){
                call_user_func(array($this, $methodName), $value);
            }
        }
    }
} 