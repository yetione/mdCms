<?php
namespace Core\GeoIp;


use Core\GeoIp\Exception\GeoIpException;

class GeoIpDatabase {
    /**
     * @var string
     */
    protected $charset = 'utf-8';

    /**
     * @var string
     */
    protected $inputCharset = 'windows-1251';

    /**
     * @param string|null $charset Кодировка. По умолчанию с IPGeoBase приходит в windows-1251
     */
    public function __construct($charset=null){
        if (!extension_loaded('curl')){
            throw new \RuntimeException(__CLASS__.': Extension curl not found');
        }
        if (!function_exists('iconv')){
            throw new \RuntimeException(__CLASS__.': Extension iconv not found');
        }
        if (!function_exists('simplexml_load_string')){
            throw new \RuntimeException(__CLASS__.': Extension DOM not found');
        }
        if (!is_null($charset)){
            $this->setCharset($charset);
        }
    }


    /**
     * @param string $ip
     * @return GeoIpRecord|null
     */
    public function getGeobaseData($ip){
        if (!$this->isValidIp($ip)){
            return null;
        }
        // получаем данные по ip
        $ch = \curl_init('http://ipgeobase.ru:7020/geo?ip=' . $ip);
        \curl_setopt($ch, CURLOPT_HEADER, false);
        \curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        \curl_setopt($ch, CURLOPT_TIMEOUT, 3);
        \curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
        $string = curl_exec($ch);

        $document = simplexml_load_string($string);
        if (false === $document || !isset($document->ip)){
            return null;
            //throw new GeoIpException(__CLASS__.': Block ip not found');
        }

        $data = ['ip'=>$ip];
        $keysToFilter = ['country', 'city', 'region', 'district', 'message', 'lat', 'lng', 'inetnum'];
        foreach ($keysToFilter as $key){
            $data[$key] = isset($document->ip->$key) ? (string) $document->ip->$key : null;
        }
        $obj = new GeoIpRecord($data);
        return $obj;
    }

    public function isValidIp($ip){
        return is_null(QS_validate($ip, TYPE_IP_V4)) ? false : true;
    }







    /**
     * @return string
     */
    public function getCharset(){
        return $this->charset;
    }

    /**
     * @param string $charset
     */
    public function setCharset($charset){
        $this->charset = $charset;
    }


} 