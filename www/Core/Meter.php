<?php
namespace Core;


class Meter {

    /**
     * @var string
     */
    protected $name;

    /**
     * @var callable
     */
    protected $func;

    /**
     * @var float
     */
    protected $startTime;

    /**
     * @var int
     */
    protected $startMemory;

    /**
     * Относительный путь до папки сохранения
     * @var array
     */
    protected $dir = array('logs', 'meters');

    /**
     * Формат имени выходного файла
     * @var string
     */
    protected $fileName = '%H.00_%d-%m-%Y.log';

    /**
     * Массив, содержащий сообщения
     * @var string[]
     */
    protected $messages = array();

    /**
     * @param string        $name       Название измерения
     * @param callable|null $function   Анонимная функция, выполняющая требуемые действия. Функция должна быть без аргументов.
     * @param bool          $autoStart  Запускать ли функцию сразу
     */
    public function __construct($name, callable $function=null, $autoStart=false  ){
        $this->name = $name;
        $this->func = $function;
        if ($autoStart){
            $this->run();
        }
        return $this;
    }

    /**
     * Запускает измеритель
     * @param bool $autoUnset
     * @return $this
     */
    public function run($autoUnset = false){
        $this->startTime = microtime(true);
        $this->startMemory = memory_get_usage(true);
        if (is_callable($this->func)){
            call_user_func($this->func);
            $this->end($autoUnset);
        }
        return $this;
    }

    /**
     * Установка или получение имени файла
     * @param null|string $filename
     * @return $this|string
     */
    public function fileName($filename=null){
        return $this->props('fileName', $filename);
    }

    /**
     * Установка или получение массива сохранения данных
     * @param array|null $dir
     * @return $this|array
     */
    public function dir(array $dir=null){
        return $this->props('dir', $dir);
    }

    /**
     * Установка или получение имени измерения
     * @param null|string $name
     * @return $this|null
     */
    public function name($name=null){
        return $this->props('name', $name);
    }

    /**
     * Установка или получение функции
     * @param callable|null $func
     * @return $this|callable|null
     */
    public function func(callable $func=null){
        return $this->props('func', $func);
    }

    /**
     * Функция пишет сообщение к результату
     * @param string $msg
     * @return $this
     */
    public function msg($msg){
        $this->messages[] = strftime('%H:%M:%S: ').strval($msg);
        return $this;
    }

    /**
     * Получение данных о состояние измерителя
     * @return array
     */
    public function data(){
        return array(
            'startTime'=>$this->startTime,
            'startMemory'=>$this->startMemory,
            'currentTime'=>microtime(true),
            'currentMemory'=>memory_get_usage(true)
        );
    }

    /**
     * Управление свойствами
     * @param string $prop
     * @param mixed|null $val
     * @return mixed|$this
     */
    protected function props($prop, $val=null){
        if (is_null($val)){
            return $this->$prop;
        }else{
            $this->$prop = $val;
            return $this;
        }
    }

    /**
     * Остонавливает измеритель
     * @param bool $autoUnset Если true, то объект удаляется
     * @return $this|null
     */
    public function end($autoUnset = false){
        $spendTime = number_format(microtime(true) - $this->startTime, 15, '.',' ');
        $startMemory = number_format($this->startMemory, 0, '.',' ');
        $endMemory = number_format(memory_get_usage(true), 0, '.', ' ');
        $msgs = count($this->messages) > 0 ? "\n\rСообщения:\n\r".implode('\n\r\t',$this->messages)."\n\r" : '';
        $message = "--------------------------\n\rВыполнение: \"{$this->name}\" заняло: {$spendTime} сек. \n\rВыделено памяти:\n\r\tв начале работы скрипта: {$startMemory} Б;\n\r\tв конце работы скрипта: {$endMemory} Б.\n\r{$msgs}--------------------------------------------------";
        $path = QS_path($this->dir,true,true).strftime($this->fileName, time());
        Debugger::writeToFile($path, $message);
        if ($autoUnset){
            unset($this);
            return null;
        }
        else return $this;
    }
} 