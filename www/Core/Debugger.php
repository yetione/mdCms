<?php
namespace Core;


class Debugger {
    /**
     * Путь до папки с логами
     * @var string
     */
    protected static $path;

    /**
     * Шаблон имени файла с логами
     * @var string
     */
    protected static $fileNameFormat;



    /**
     * Время начала работы скрипта
     * @var float
     */
    protected static $startTime;

    /**
     * Начальное кол-во выделеной памяти
     * @var int
     */
    protected static $startMemory;




    const ERROR = 1;
    const WARNING = 2;
    const NOTICE = 4;
    const DEPRECATED = 8;
    const ALL = -1;

    protected static $errorsLevels = array(
        1 => E_USER_ERROR,
        2 => E_USER_WARNING,
        4 => E_USER_NOTICE,
        8 => E_USER_DEPRECATED
    );

    /**
     * Уровень логинга
     * @var int
     */
    protected static $debugLevel = 3; // ERROR|WARNING

    /**
     * Путь к папке с логами
     * @var string
     */
    protected static $logPath;

    /**
     * Формат имени логфайлов
     * @var string
     */
    protected static $logFilenameFormat = 'd-m-Y';

    public static function debugLevel($debugLevel=null){

        if (is_null($debugLevel)){
            return self::$debugLevel;
        }
        elseif ($debugLevel == -1){
            self::$debugLevel = self::ERROR | self::WARNING | self::NOTICE | self::DEPRECATED;
        }
        elseif ( (($debugLevel & ($debugLevel-1)) == 0) && ($debugLevel > 0 && $debugLevel < 9)){
            //Проверка на то, что число является степенью двойки и входит в допустимый диапазон
            self::$debugLevel = $debugLevel;
        }else{
            self::error('QSDebugger: Wrong value of debug level: '.$debugLevel, self::WARNING);
            return false;
        }
        return true;
    }

    public static function logPath($path=null){
        if (is_null($path)){
            return self::$logPath;
        }
        elseif (file_exists($path)){
            self::$logPath = $path;
        }else{
            self::error('QSDebugger: Directory: '.$path.' doen\'t exists', self::WARNING);
            return false;
        }
        return true;
    }

    public static function init($debugLevel = null, $logPath = null, $logFilenameFormat = null){
        if (!is_null($debugLevel)){
            self::debugLevel((int) $debugLevel);
        }
        if (!is_null($logPath)){
            self::logPath($logPath);
        }
        if (!is_null($logFilenameFormat)){
            self::$logFilenameFormat = $logFilenameFormat;
        }
        set_error_handler(__CLASS__.'::errorHandler');
    }

    public static function log($message, $level=self::NOTICE){
        if ( self::$debugLevel&$level > 0){
            $file = date(self::$logFilenameFormat, time()).'.log';
            $path = QS_path(array(self::$logPath, $file), false, false, false);
            self::writeToFile($path, $message);
        }
        return false;
    }





    /**
     * Обработчик ошибок
     * @param int $errno Код ошибки
     * @param string $errstr Сообщение об ошибке
     * @param string $errfile Имя файла
     * @param int $errline Номер строки с ошибкой
     * @param array $errcontext Массив указателей на активную таблицу символов в точке, где произошла ошибка
     * @return bool
     */
    public static function errorHandler($errno , $errstr , $errfile , $errline , array $errcontext ){
        $message = "{$errfile}:{$errline} : ".$errstr;
        //$trace = debug_backtrace();
        switch ($errno){
            case E_USER_ERROR:
                self::log($message, self::ERROR);
                exit('QSpace crashed. Check logs.');

            case E_STRICT:
            case E_NOTICE:
            case E_USER_NOTICE:
                self::log($message, self::NOTICE);
                return true; //Не запускает встроенный обработчик

            case E_WARNING:
            case E_CORE_WARNING:
            case E_COMPILE_WARNING:
            case E_USER_WARNING:
                self::log($message, self::WARNING);
                return true;

            case E_ERROR:
            case E_PARSE:
            case E_CORE_ERROR:
            case E_COMPILE_ERROR:
                self::log($message, self::ERROR);
                return false;   //Запускает встроенный обработчие

            case E_RECOVERABLE_ERROR:
                self::log($message, self::ERROR);
                return true;

            case E_DEPRECATED:
            case E_USER_DEPRECATED:
                self::log($message, self::DEPRECATED);
                return true;

            default:
                self::log("Unknown error: {$errfile}:{$errline} : ".$errstr, self::NOTICE);
                return false;
        }
    }


    /**
     * Функция записывает данные в файл
     * @param string $filePath Путь до файла
     * @param string $message Сообщение
     * @return int
     */
    public static function writeToFile($filePath, $message){

        $file = fopen($filePath,'a+');
        $result = fwrite($file,'L '.date('m/d/Y - H:i:s: ').$message.PHP_EOL);
        fclose($file);
        return $result;
    }

    /**
     * Вызывает пользовательскую ошибку
     * @param $message
     * @param int $level
     */
    public static function error($message, $level = self::NOTICE){
        //$caller = next(debug_backtrace());
        trigger_error($message, self::$errorsLevels[$level]);
    }

    /**
     * @param $msg
     * @param int $code
     * @param \Exception $previous
     * @return \Exception
     */
    public static function exception($msg, $code=0, \Exception $previous = null){
        return new \Exception($msg, $code, $previous);
    }
} 