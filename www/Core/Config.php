<?php
namespace Core;
use Core\Exception\FileNotFound;
use Core\Exception\IncorrectKey;
use Core\Exception\ReadFileError;
use Core\Exception\SerializeError;
use Core\Exception\UnserializeError;
use Core\Exception\WriteFileError;

/**
 * Класс для работы с конфигурацией системы.
 * Class Config
 * @package Core
 */
class Config {

    /**
     * Массив с конфигами
     * @var array
     */
    protected $configs = null;

    /**
     * Путь к кэшированному файлу
     * @var string
     */
    protected $cached_path = null;

    /**
     * Путь к файлу с конфигами
     * @var string
     */
    protected $file_path = null;

    /**
     * Внесены ли изменения
     * @var bool
     */
    protected $is_changed = false;

    public function __construct($path = null){
        if (!is_null($path)){
            if (!$this->load($path)){
                unset($this);
            }
        }
    }

    public function __destruct(){
        if ($this->is_changed){
            $this->serializeConfigs();
            $this->write();
        }
    }

    /**
     * Метод загружает конфиг, по переданному ему пути.
     * Возвращает true в случае успеха, иначе false.
     * @param string $path
     * @throws FileNotFound
     * @throws SerializeError
     * @throws UnserializeError
     * @return bool Результат загрузки
     */
    public function load($path){
        if (file_exists($path)){
            $path_info = pathinfo($path);
            $cached_name = explode('.', $path_info['basename'])[0].'_cache';
            $cached_path = QS_path(array($path_info['dirname'], $cached_name), false, false, false);

            $this->file_path = $path;
            $this->cached_path = $cached_path;

            if (!file_exists($cached_path) ||
                filemtime($path) > filemtime($cached_path)){
                return $this->serializeConfigs();
            } else {
                if (!$this->unserializeConfigs()){
                    return $this->serializeConfigs();
                }
                return true;
            }
        } else {
            throw new FileNotFound(__CLASS__.': Can\'t load configs from not existing path: '.$path);
        }
    }

    /**
     * Метод читает конфиг из переданного в конструктор файла.
     * Возвращает true в случае успеха, иначе false.
     * @throws ReadFileError
     * @return bool true - в случае успеха, иначе - false
     */
    protected function readConfigs(){
        if (!is_null($this->configs)) return true;
        if (($this->configs = parse_ini_file($this->file_path, true)) === false){
            throw new ReadFileError(__CLASS__.': Can\'t read config\'s file: '.$this->file_path);
        }
        return true;
    }

    /**
     * Метод записывает существующий конфиг в сериализованный файл.
     * Возвращает true в случае успеха, иначе false.
     * @throws ReadFileError
     * @throws SerializeError
     * @return bool
     */
    protected function serializeConfigs(){
        if ($this->readConfigs()){
            if (file_put_contents($this->cached_path, serialize($this->configs)) !== false){
                return true;
            }
        }
        throw new SerializeError(__CLASS__.': Can\'t serialize configs.');
    }

    /**
     * Метод читает конфиг из сериализованного файла.
     * Возвращает true в случае успеха, иначе false.
     * @throws UnserializeError
     * @return bool
     */
    protected function unserializeConfigs(){
        if (($serialized = file_get_contents($this->cached_path)) !== false){
            $this->configs = unserialize($serialized);
            return true;
        }
        throw new UnserializeError(__CLASS__.': Can\'t unserialize configs.');
    }

    /**
     * @param $data
     * @param null $default
     * @param string $type
     * @return mixed
     */
    public function get($data, $default = null, $type='raw'){
        $data = explode('.', $data);
        $c = count($data);
        if ($c == 2 && isset($this->configs[$data[0]]) && isset($this->configs[$data[0]][$data[1]])){
            return $this->convert($this->configs[$data[0]][$data[1]], $type);
        } elseif ($c == 1 && isset($this->configs[$data[0]])){
            return $this->convert($this->configs[$data[0]], $type);
        } else {
            return $default;
        }
    }

    /**
     * Метод кновертирует строку в заданный тип. Тип может быть:
     * raw - оставляет значение без изменений;
     * int - конвертирует строку в целое число;
     * float - конвертирует строку в число с плавующей точкой;
     * bool - конвертирует строку в логической значение;
     * array - конвертирует строку в массив, значения массива должны быть разделены ",",
     * а строки, входящие в масив должны быть заключены в двойные кавычки.
     * Если методу передано не скалярное значение, то оно остается без изменений.
     * @param mixed $value  Значение для преобразования.
     * @param string $type  Тип, к которому его приводить.
     * @return mixed|null   В случае, если тип не распознан, будет возвращенно null.
     */
    protected function convert($value, $type){
        if (is_array($value)){
            foreach ($value as $k=>&$v){
                if ((substr($v, 0, 1) == '[' && substr($v, -1, 1) == ']') ||
                    (substr($v, 0, 1) == '{' && substr($v, -1, 1) == '}')){
                    $temp = json_decode($v, true);
                    if (json_last_error() == JSON_ERROR_NONE){
                        $v = $temp;
                    }
                }
            }
            unset($v);
            return $value;
        }
        $rules = array(
            'raw' => FILTER_UNSAFE_RAW,
            'int' => FILTER_SANITIZE_NUMBER_INT,
            'int2' => FILTER_SANITIZE_NUMBER_INT,
            'int8' => FILTER_SANITIZE_NUMBER_INT,
            'int16' => FILTER_SANITIZE_NUMBER_INT,
            'float' => FILTER_VALIDATE_FLOAT,
            'bool' => FILTER_VALIDATE_BOOLEAN,
            'email'=> FILTER_VALIDATE_EMAIL,
            'ip'=>FILTER_VALIDATE_IP,
            'url'=>FILTER_VALIDATE_URL,
            'regexp'=>FILTER_VALIDATE_REGEXP
        );
        $type = strtolower($type);
        if (isset($rules[$type])){
            $result = filter_var($value, $rules[$type]);
            switch ($type){
                case 'int':
                    $result = intval($result);
                    break;
                case 'int2':
                    $result = intval($result, 2);
                    break;
                case 'int8':
                    $result = intval($result, 8);
                    break;
                case 'int16':
                    $result = intval($value, 2);
                    break;
                case 'float':
                    $result = filter_var($value, FILTER_SANITIZE_NUMBER_FLOAT, array('flags'=>FILTER_FLAG_ALLOW_FRACTION));
                    $result = floatval($result);
                    break;
                default:
                    break;
            }
            return $result;
        } elseif ($type == 'json'){
            return json_decode($value, true);
        }
        return null;
    }

    /**
     * Метод возвращает весь массив с конфигами.
     * @return array
     */
    public function getAll(){
        return $this->configs;
    }

    /**
     * Метод устанавливает значения конфига.
     * @param string $data Ключ конфига.
     * @param mixed $value Значение.
     * @throws IncorrectKey
     * @return bool Возвращает true в случае успешной записи, иначе false.
     */
    public function set($data, $value){
        $data = explode('.', $data);
        $c = count($data);
        if ($c == 2){
            if (!isset($this->configs[$data[0]])){
                $this->configs[$data[0]] = array();
            }
            $this->configs[$data[0]][$data[1]] = $value;
        } elseif ($c == 1){
            $this->configs[$data[0]] = $value;
        } else {
            throw new IncorrectKey(__CLASS__.': Wrong key: '.$c);
        }
        $this->is_changed = true;
        return true;
    }

    /**
     * Метод возвращает INI строку из массива.
     * @param array $a Входной массив с данными
     * @param bool $is_second
     * @return string INI строка
     */
    protected function toString(array $a, $is_second=false){
        $out = '';
        foreach ($a as $k => $v) {
            if (is_array($v) && !$is_second){
                $out .= "[{$k}]" . PHP_EOL;
                $out .= $this->toString($v,  true);
            }elseif (is_array($v) && $is_second){
                $v = json_encode($v);
                $out .= "{$k}='{$v}'" . PHP_EOL;
            }elseif (!is_array($v) && !$is_second) {
                $out .= PHP_EOL . "{$k}='{$v}'" . PHP_EOL;
            }else {
                $out .= "{$k}='{$v}'" . PHP_EOL;
            }
        }
        return $out;
    }

    /**
     * Метод записывает файл конфига и делает его сериализованную копию.
     * @param null|string $file Файл для записи. Если null, то пишется в файл, переданный в конструктор.
     * @throws SerializeError
     * @throws WriteFileError
     * @return bool Возвращает true в случае успешности всех операций, иначе - false.
     */
    protected function write($file=null){
        if (file_put_contents(is_null($file) ? $this->file_path : $file, $this->toString($this->configs)) !== false){
            if ($this->serializeConfigs()){
                return true;
            }
        }
        throw new WriteFileError(__CLASS__.': Can\'t write configs file.');
    }
} 