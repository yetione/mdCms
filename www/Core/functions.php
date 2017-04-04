<?php

/**
 * Возвращает путь к папке с системными разделителями директорий.
 * @param array $path массив с названиями папок
 * @param bool $addEndSep добавлять ли разделитель в конце выходной строки (по умолчанию true)
 * @param bool $mkDir создавать ли указанную директорию (по умолчанию false)
 * @param bool $addAbsolutePath добавлять ли абсолютный путь
 * @param int $chmod Права доступа к файлу/папке
 * @return string
 */
function QS_path(array $path, $addEndSep=true, $mkDir=false, $addAbsolutePath=true, $chmod=0775){
    if ( count($path)  == 0){
        return null;
    }
    $path = ($addAbsolutePath === true ? BASE_PATH.DIRECTORY_SEPARATOR : '').implode(DIRECTORY_SEPARATOR, $path);
    if (!is_null($path) && $mkDir && !is_dir($path)){
        $e = mkdir($path,$chmod, true);
    }
    return $path.($addEndSep === true ? DIRECTORY_SEPARATOR : '');
}

function QS_joinPath(){
    $args = func_get_args();
    if (count($args) < 1){
        throw new \InvalidArgumentException('QS_joinPath: Function expect 1 or more arguments.');
    }
    $path = rtrim(array_shift($args), DIRECTORY_SEPARATOR);
    if (count($args) > 0){
        $path .= DIRECTORY_SEPARATOR.implode(DIRECTORY_SEPARATOR, array_map(function($el){
            return str_replace('/', DIRECTORY_SEPARATOR, trim($el, '/'));
        }, $args));
    }
    return $path;
}


/**
 * @param mixed $value
 * @param int $type
 * @param mixed $default
 * @return mixed
 */
function QS_validate($value, $type, $default=null){
    /** @var array $types */
    static $types = array(
        TYPE_INT     =>FILTER_VALIDATE_INT,
        TYPE_INT8    =>array(FILTER_VALIDATE_INT, array('flags'=>FILTER_FLAG_ALLOW_OCTAL)),
        TYPE_INT10   =>FILTER_VALIDATE_INT,
        TYPE_INT16   =>array(FILTER_VALIDATE_INT, array('flags'=>FILTER_FLAG_ALLOW_HEX)),
        TYPE_FLOAT   =>array(FILTER_VALIDATE_FLOAT, array('flags'=>FILTER_FLAG_ALLOW_THOUSAND)),
        TYPE_BOOL    =>array(FILTER_VALIDATE_BOOLEAN, array('flags'=>FILTER_NULL_ON_FAILURE)),
        TYPE_EMAIL   =>FILTER_VALIDATE_EMAIL,
        TYPE_IP      =>FILTER_VALIDATE_IP,
        TYPE_REGEXP  =>FILTER_VALIDATE_REGEXP,
        TYPE_URL     =>FILTER_VALIDATE_URL,
        TYPE_RAW     =>FILTER_DEFAULT,
        TYPE_STRING  =>FILTER_SANITIZE_STRING,
        TYPE_IP_V4   =>array(FILTER_VALIDATE_IP, array('flags'=>FILTER_FLAG_IPV4)),
        TYPE_IP_V6   =>array(FILTER_VALIDATE_IP, array('flags'=>FILTER_FLAG_IPV6))
    );
    if ($type==TYPE_INT){
        $result = filter_var($value, FILTER_VALIDATE_INT, array('flags'=>FILTER_FLAG_ALLOW_OCTAL|FILTER_FLAG_ALLOW_HEX));
    } elseif (isset($types[$type])){
        $result = is_array($types[$type]) ? filter_var($value, $types[$type][0], $types[$type][1]) : filter_var($value, $types[$type]);
    } elseif($type==TYPE_JSON){
        $result = json_decode($value);
        return json_last_error() == JSON_ERROR_NONE ? $result : $default;
    } else{
        \Core\Debugger::log('QS_validate: wrong type of filter: '.$type, \Core\Debugger::WARNING);
        return $default;
    }
    return $type == 'bool' ? (is_null($result) ? $default : $result) : ($result === false ? $default : $result);
}

/**
 * Функция обрабатывает строку со временем и возвращает результат в секундах.
 * Строка может содержать блоки вида <кол-во><единица времени>, разделенных запятой.
 * Единицы времени:
 * y - год; m - месяц;d - день;h - час;min - минута;sec - секунда
 * @param string|int $time
 * @return int
 */
function QS_parseTime($time){
    if (!is_scalar($time)){
        throw new \InvalidArgumentException(__FUNCTION__.': time must be a scalar, not: '.gettype($time));
    }
    $time = strval($time);
    if (ctype_digit($time)){
        return (int) $time;
    }else{
        $time = explode(',', strtolower($time));
        $matches = array();
        $result = 0;
        foreach ($time as $element){
            if (preg_match('/^([0-9]+)([ymdhinsec]+)$/i', $element, $matches) !== 1){
                throw new \InvalidArgumentException(__FUNCTION__.": argument time({$time}) has wrong format.");
            }
            switch ($matches[2]){
                case 'y':
                    $multiplier = 60*60*24*365; //Секунд в году
                    break;
                case 'm':
                    $multiplier = 60*60*24*30; //Секунд в месяце и т.д.
                    break;
                case 'd':
                    $multiplier = 60*60*24;
                    break;
                case 'h':
                    $multiplier = 60*60;
                    break;
                case 'min':
                    $multiplier = 60;
                    break;
                case 'sec':
                    $multiplier = 1;
                    break;
                default:
                    throw new \InvalidArgumentException(__FUNCTION__.": argument time({$time}) has wrong format.");
            }
            $result += (int) $matches[1] * $multiplier;
        }
        return $result;
    }
}


if (!function_exists('http_response_code')) {
    function http_response_code($code = NULL) {

        if ($code !== NULL) {

            switch ($code) {
                case 100: $text = 'Continue'; break;
                case 101: $text = 'Switching Protocols'; break;
                case 200: $text = 'OK'; break;
                case 201: $text = 'Created'; break;
                case 202: $text = 'Accepted'; break;
                case 203: $text = 'Non-Authoritative Information'; break;
                case 204: $text = 'No Content'; break;
                case 205: $text = 'Reset Content'; break;
                case 206: $text = 'Partial Content'; break;
                case 300: $text = 'Multiple Choices'; break;
                case 301: $text = 'Moved Permanently'; break;
                case 302: $text = 'Moved Temporarily'; break;
                case 303: $text = 'See Other'; break;
                case 304: $text = 'Not Modified'; break;
                case 305: $text = 'Use Proxy'; break;
                case 400: $text = 'Bad Request'; break;
                case 401: $text = 'Unauthorized'; break;
                case 402: $text = 'Payment Required'; break;
                case 403: $text = 'Forbidden'; break;
                case 404: $text = 'Not Found'; break;
                case 405: $text = 'Method Not Allowed'; break;
                case 406: $text = 'Not Acceptable'; break;
                case 407: $text = 'Proxy Authentication Required'; break;
                case 408: $text = 'Request Time-out'; break;
                case 409: $text = 'Conflict'; break;
                case 410: $text = 'Gone'; break;
                case 411: $text = 'Length Required'; break;
                case 412: $text = 'Precondition Failed'; break;
                case 413: $text = 'Request Entity Too Large'; break;
                case 414: $text = 'Request-URI Too Large'; break;
                case 415: $text = 'Unsupported Media Type'; break;
                case 500: $text = 'Internal Server Error'; break;
                case 501: $text = 'Not Implemented'; break;
                case 502: $text = 'Bad Gateway'; break;
                case 503: $text = 'Service Unavailable'; break;
                case 504: $text = 'Gateway Time-out'; break;
                case 505: $text = 'HTTP Version not supported'; break;
                default:
                    exit('Unknown http status code "' . htmlentities($code) . '"');
                    break;
            }

            $protocol = (isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0');

            header($protocol . ' ' . $code . ' ' . $text);

            $GLOBALS['http_response_code'] = $code;

        } else {

            $code = (isset($GLOBALS['http_response_code']) ? $GLOBALS['http_response_code'] : 200);

        }

        return $code;
    }


}

/**
 * @param array $keys
 * @param array $arr
 * @return bool
 */
function isSetArrayKeys(array $keys, array $arr){
    foreach ($keys as $k){
        if (!isset($arr[$k])) return false;
    }
    return true;
}



function isInt($val){
    return ctype_digit(strval($val));
}

function toInt(&$val){
    if (isInt($val)){
        $val = (int) $val;
    }else{
        $val = null;
    }
}

/**
 * Multibyte
 */

if (function_exists('iconv')) {
    function convert_encoding($str, $to, $from='utf-8') {
        return iconv($from, $to, $str);
    }
} else if (function_exists('mb_convert_encoding')) {
    function convert_encoding($str, $to, $from='utf-8') {
        return mb_convert_encoding($str, $to, $from);
    }
} else {
    function convert_encoding($str, $to, $from='utf-8') {
        throw new \Exception('You need "Multibyte String" or "iconv" for this.');
    }
}
