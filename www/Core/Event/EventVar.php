<?php
namespace Core\Event;


class EventVar {

    protected $data = array();

    protected $temp = array();

    protected $inTransaction = false;

    private $transactionKey;

    public function __construct(array $data=array()){
        $this->setData($data);
    }

    public function getData(){
        return $this->data;
    }

    public function setData(array $data){
        $this->data = $data;
    }

    public function get($key, $default=null){
        if ($this->inTransaction){
            return isset($this->temp[$key]) ? $this->temp[$key] : $default;
        }
        return isset($this->data[$key]) ? $this->data[$key] : $default;
    }

    public function set($key, $value, $override=true){
        if ($this->inTransaction && (!isset($this->temp[$key]) || $override)){
            $this->temp[$key] = $value;
        }elseif (!$this->inTransaction && (!isset($this->data[$key]) || $override)){
            $this->data[$key] = $value;
        }else{
            //var_dump('UNKNOWN ERROR', $key, $value, $override);
            return false;
        }
        return true;
    }

    public function reset(){
        if (!$this->inTransaction){
            $this->data = array();
            return true;
        }
        return false;
    }

    public function startTransaction($key){
        if ($this->inTransaction){
            return false;
        }
        $this->temp = $this->data;
        $this->inTransaction = true;
        $this->transactionKey = strval($key);
        return true;
    }

    public function commit($key){
        if (!$this->inTransaction || strval($key) !== $this->transactionKey){
            return false;
        }
        $this->data = $this->temp;
        $this->inTransaction = false;
        return true;
    }

    public function rollback($key){
        if (!$this->inTransaction || strval($key) !== $this->transactionKey){
            return false;
        }
        $this->temp = array();
        $this->inTransaction = false;
        return true;
    }
} 