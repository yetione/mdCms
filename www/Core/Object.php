<?php
namespace Core;

/**
 * Класс объект системы
 * Class QSObject
 * @package Core
 */
class Object implements IExportable{

    /**
     * Свойство, в хотором хранятся данные объекта
     * @var array
     */
    protected $_data;

    const GET = 1;  // Константа для получения значения свойства из объекта
    const DELETE = 2; // Константа для удаления значения свойства из объекта
    const SET = 3; // Константа показывает, что надо установить значение свойству объекта

    const MISSING_KEY = 0; // Код ошибки отсутствующего ключа


    /**
     * @param array|object $data
     * @throws \Exception
     */
    public function __construct($data=array()){
        if (is_array($data)){
            $this->_data = $data;
        }else if (is_object($data) && ($d = get_object_vars($data)) !== false){
            $this->_data = $d;
        }else{
            throw new \Exception(__CLASS__.': Can\'t create QSObject from input');
        }
    }

    /**
     * @param string $name
     * @return null|mixed
     */
    public function __get($name){
        if (array_key_exists($name, $this->_data)){
            if (is_array($this->_data[$name])){
                return new Object($this->_data[$name]);
            }
            return $this->_data[$name];
        }
        return null;
    }

    /**
     * @param string $name
     * @param mixed $val
     */
    public function __set($name, $val){
        if ($val instanceof Object) $val = $val->getData();
        $this->_data[$name] = $val;
    }

    /**
     * Метод возвращает значение свойства $name
     * @param string $name
     * @param mixed|null $default
     * @return mixed|null
     */
    public function get($name, $default=null){
        try{
            return $this->_keyAction($name, self::GET);
        }catch (\Exception $e){
            return $default;
        }
    }

    /**
     * Метод устанавливает значение свойства $name в $val
     * Если свойство уже установлено и override - false, то перезапись не произойдет
     * @param string $name
     * @param mixed $val
     * @param bool $override
     * @return mixed|null
     */
    public function set($name, $val, $override = false){

        try{
            return $this->_keyAction($name, self::SET, $val, $override);
        }catch (\Exception $e){
            $result = false;
        }
        return $result;
    }


    /**
     * Метод удаляет значение из объекта по ключу
     * @param string $key
     * @return bool|mixed|null
     */
    public function delete($key){
        try{
            return $this->_keyAction($key, self::DELETE);
        }catch (\Exception $e){
            return false;
        }
    }



    /**
     * Метод производит манипуляции с данным по переданному ключу.
     * @param string $key
     * @param int $action
     * @param mixed|null $value
     * @param bool $override
     * @return mixed|null
     * @throws \Exception
     */
    protected function _keyAction($key, $action=self::GET, $value=null, $override=false){
        $name = explode('.', $key);
        $data = &$this->_data;
        $result = null;
        $c = count($name);
        for ($i=0;$i<$c;$i++){
            if (($i+1) == $c){
                if ($action === self::SET && ($override || !isset($data[$name[$i]]))){
                    $data[$name[$i]] = $value;
                }elseif ($action === self::DELETE && isset($data[$name[$i]])){
                    $result = &$data[$name[$i]];
                    unset($data[$name[$i]]);
                }elseif ($action === self::GET && isset($data[$name[$i]])){
                    $result = &$data[$name[$i]];
                }
                else{
                    throw new \Exception(__CLASS__.": Missing key: {$key}.", self::MISSING_KEY);
                }
                break;
            } else {
                if ($action === self::SET && !isset($data[$name[$i]])){
                    $data[$name[$i]] = array();
                }
                if (is_array($data[$name[$i]])){
                    if (!isset($data[$name[$i]][$name[$i+1]]) && $action !== self::SET){
                        throw new \Exception(__CLASS__.": Missing key ARR: {$key}.", self::MISSING_KEY);
                    }
                    $data = &$data[$name[$i]];
                    continue;
                }
            }
            throw new \Exception(__CLASS__.': Incorrect key: '.$key );
            break;
        }
        return $result;
    }



    /**
     * Метод возвращает свойства объекта в виде массива
     * @return array|object
     */
    public function getData(){
        return $this->_data;
    }

    /**
     * Этот статический метод вызывается для тех классов,
     * которые экспортируются функцией var_export() начиная с PHP 5.1.0.
     * Параметр этого метода должен содержать массив,
     * состоящий из экспортируемых свойств в виде array('property' => value, ...).
     * @see http://php.net/manual/ru/language.oop5.magic.php#object.set-state
     * @param array $array
     * @return mixed
     */
    public static function __set_state(array $array)
    {
        return new self($array['_data']);
    }

    public function reset(){
        $this->_data = array();
        return true;
    }
}