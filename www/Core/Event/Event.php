<?php
namespace Core\Event;


use Core\Event\Exception\WrongResult;

class Event{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var int
     */
    protected $status;

    protected $preHooks = array();

    protected $postHooks = array();

    protected $oneTimeHooks = array();

    protected $preFired = false;

    protected $postFired = false;

    protected $postOnly = false;

    protected $eventManager;

    /**
     * @var EventVar
     */
    protected $eventData;

    const EVENT_STATUS_DISABLED = 0;
    const EVENT_STATUS_FIRING = 1;
    const EVENT_STATUS_WAIT_POST = 7;
    const EVENT_STATUS_HANDLED = 8;
    const EVENT_STATUS_FINISHED = 9;


    const HOOK_MODE_PRE = 12;
    const HOOK_MODE_POST = 13;


    public function __construct($name, EventManager $em){
        $this->status = self::EVENT_STATUS_DISABLED;
        $this->name = (string) $name;
        $this->eventManager = $em;
        //$this->hookMany($listeners);
        $this->eventData = new EventVar();
    }

    /**
     * Статус события
     * @return int
     */
    public function getStatus(){
        return $this->status;
    }

    /**
     * Название события
     * @return string
     */
    public function getName(){
        return $this->name;
    }

    /**
     * Метод устанавливает режим postOnly.
     * В этом режиме не будет работать перехват события до выполнения.
     *
     * @param bool $value
     */
    public function setPostOnly($value){
        $this->postOnly = (bool) $value;
    }

    public function isHandled(){
        return $this->status === self::EVENT_STATUS_HANDLED;
    }

    /**
     * Метод выполняет обработчики события в режиме перехвата до выполнения.
     * Метод возвращает false в случае, если обработка события должна остановиться.
     * @throws WrongResult
     * @return bool
     */
    public function preFire(){
        if ($this->status !== self::EVENT_STATUS_DISABLED || $this->postOnly){
            // TODO: Log it! Event already firing
            return false;
        }
        $this->status = self::EVENT_STATUS_FIRING;
        $hooks = $this->eventManager->getPreHooks($this->name);
        foreach($hooks as $id=>$listener){
            $transactionKey = uniqid();
            $this->eventData->startTransaction($transactionKey);
            $result = $listener($this->eventData);
            /*
            if (($index = array_search($id, $this->oneTimeHooks)) !== false){
                unset($this->preHooks[$index]);
            }
            */
            if (!in_array($result, array(EVENT_CONTINUE, EVENT_CHANGE, EVENT_HANDLED))){
                throw new WrongResult(__CLASS__.': Event must return one of event result constant');
            } elseif ($result === EVENT_CHANGE){
                $this->eventData->commit($transactionKey);
            } elseif ($result === EVENT_HANDLED){
                $this->eventData->commit($transactionKey);
                $this->status = self::EVENT_STATUS_HANDLED;
                return false;
            }else{
                $this->eventData->rollback($transactionKey);
            }
            //var_dump($this->eventData->getData());
        }
        $this->status = self::EVENT_STATUS_WAIT_POST;
        return true;
    }

    /**
     * Метод выполняет обработчики события в режиме перехвата после выполнения.
     * Данный метод может быть вызван, только после метода QSEvent::preFire.
     * @return bool
     */
    public function postFire(){
        if ($this->status !== self::EVENT_STATUS_WAIT_POST){
            return false;
        }
        return $this->fire();
    }

    public function fire(){
        if (!in_array($this->status, array(self::EVENT_STATUS_DISABLED, self::EVENT_STATUS_WAIT_POST))){
            return false;
        }
        $this->status = self::EVENT_STATUS_FIRING;
        $hooks = $this->eventManager->getPostHooks($this->name);

        foreach ($hooks as $id=>$listener) {
            $transactionKey = uniqid();
            $this->eventData->startTransaction($transactionKey);
            $listener($this->eventData);
            /*
            if (($index = array_search($id, $this->oneTimeHooks)) !== false){
                unset($this->postHooks[$index]);
            }
            */
            $this->eventData->rollback($transactionKey);
        }
        $this->status = self::EVENT_STATUS_DISABLED;
        return true;
    }

    public function get($key, $default=null){
        return $this->eventData->get($key, $default);
    }

    public function set($key, $value, $override=true){
        $this->eventData->set($key, $value, $override);
        return $this;
    }

    public function reset(){
        if (!$this->eventData->reset()){
            // TODO: Log it! Can't reset event data
            return false;
        }

        $this->status = self::EVENT_STATUS_DISABLED;
        $this->preHooks = array();
        $this->postHooks = array();

        return true;
    }
} 