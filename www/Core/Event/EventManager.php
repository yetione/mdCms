<?php
namespace Core\Event;


use Core\Exception\WrongArgument;

class EventManager {
    /**
     * @var array
     */
    protected $events = array();

    protected $listeners = array();

    const HOOK_MODE_POST_KEY = 0;
    const HOOK_MODE_PRE_KEY = 1;

    protected $constants = array(
        'HOOK_MODE_PRE'=>10,
        'HOOK_MODE_POST'=>11,
        'EVENT_CONTINUE'=>20,
        'EVENT_CHANGE'=>21,
        'EVENT_HANDLED'=>22,
        'FIRE_MODE_PRE'=>30,
        'FIRE_MODE_POST'=>31
    );

    public function __construct(){
        $this->defineConstants();
    }

    public function hook($ev_name, callable $callback, $hookMode=HOOK_MODE_POST, $prepend=false){
        if (!isset($this->listeners[$ev_name])){
            $this->listeners[$ev_name] = array(
                self::HOOK_MODE_POST_KEY => array(),
                self::HOOK_MODE_PRE_KEY => array()
            );
        }
        $key = $this->keyFromHookMode($hookMode);
        if (($index = array_search($callback, $this->listeners[$ev_name][$key], true)) !== false){
            return $index;
        }
        $id = $this->getHookId($ev_name);
        if ($prepend){
            $this->listeners[$ev_name][$key] = $this->prepend($this->listeners[$ev_name][$key], $id, $callback);
        }else{
            $this->listeners[$ev_name][$key][$id] = $callback;
        }
        return $id;
    }

    /**
     * @param string $ev_name
     * @param callable|string $data
     * @param int $hookMode
     * @return bool
     * @throws WrongArgument
     */
    public function unhook($ev_name, $data, $hookMode=HOOK_MODE_POST){
        $key = $this->keyFromHookMode($hookMode);
        if (isset($this->listeners[$ev_name]) && isset($this->listeners[$ev_name][$key])){
            if ( (is_callable($data) && ($index = array_search($data, $this->listeners[$ev_name][$key], true)) !== false) ){
                unset($this->listeners[$ev_name][$key][$index]);
            }elseif (!is_callable($data) && isset($this->listeners[$ev_name][$key][$data])){
                unset($this->listeners[$ev_name][$key][$data]);
            }else{
                throw new WrongArgument(__CLASS__.'Illegal argument data. It must be callable or hook id.');
            }
            return true;
        }
        return false;
    }

    /**
     * @param $ev_name
     * @return Event
     */
    public function event($ev_name){
        $event = new Event($ev_name, $this);

        return $event;
    }

    public function getPreHooks($ev_name){
        return isset($this->listeners[$ev_name][self::HOOK_MODE_PRE_KEY]) ?
                    $this->listeners[$ev_name][self::HOOK_MODE_PRE_KEY] :
                    array();
    }

    public function getPostHooks($ev_name){
        return isset($this->listeners[$ev_name][self::HOOK_MODE_POST_KEY]) ?
            $this->listeners[$ev_name][self::HOOK_MODE_POST_KEY] :
            array();
    }


    protected function getHookId($ev_name){
        do{
            $id = uniqid();
        }while (isset($this->listeners[$ev_name][self::HOOK_MODE_POST_KEY][$id]) ||
            isset($this->listeners[$ev_name][self::HOOK_MODE_PRE_KEY][$id]));
        return $id;
    }

    protected function keyFromHookMode($hookMode){
        if (!in_array($hookMode, array(HOOK_MODE_POST, HOOK_MODE_PRE))){
            throw new WrongArgument(__CLASS__.': Argument hookMode('.$hookMode.') is wrong.');
        }else{
            return $hookMode === HOOK_MODE_POST ? self::HOOK_MODE_POST_KEY : self::HOOK_MODE_PRE_KEY;
        }
    }

    protected function prepend(&$arr, $key, $value){
        if (count($arr) >= 2){
            $arr = array_reverse($arr, true);
        }
        $arr[$key] = $value;
        return  array_reverse($arr, true);
    }

    protected function defineConstants(){
        foreach ($this->constants as $name => $value){
            if (!defined($name)){
                define($name, $value);
            }
        }
    }

    public function kill(Event $event){
        $ev_name = $event->getName();
        if (!isset($this->events[$ev_name])){
            return true;
        }
        $index = array_search($event, $this->events[$ev_name], true);
        if (false === $index) {
            return false;
        }
        unset($this->events[$ev_name][$index]);
        if (count($this->events[$ev_name]) == 0){
            unset($this->events[$ev_name]);
            unset($this->listeners[$ev_name]);
        }
        return true;
    }

    public function getEvents(){
        return $this->events;
    }

    protected function makeArray($ev_name){
        if (!isset($this->events[$ev_name])){
            $this->events[$ev_name] = array();
        }
        if (!isset($this->listeners[$ev_name])){
            $this->listeners[$ev_name] = array();
        }
    }
} 