<?php
namespace Core;

class Crypt {

    /**
     * Cтатическая соль, генерируемая при установке CMS, которая будет добовляться ко всем шифруемым данным
     * @var string
     */
    protected $_salt = null;


    /**
     * Символы, используемые для генерации случайной соли
     * Сгенерираванная соль используется для хэширования паролей
     * @var array
     */
    protected $_salt_letters = null;

    protected $_salt_length;

    protected $_openssl_configure = false;

    protected $_digest_alg = null;

    protected $_private_key_bits = null;

    protected $_private_key_type = null;

    protected $_keys_path = null;

    protected $_private_key = null;

    protected $_public_key = null;

    protected $_public_key_path = null;

    protected $_private_key_path = null;

    /**
     * Константы алгоритмов шифрования
     */
    const MD5 = 1;
    const SHA1 = 2;
    const PASSWORD = -1;

    protected $crypt_settings = array(
        'DES' => array(
            'salt_regexp' => '^[0-9A-Za-z]{2}$'
        )
    );


    /**
     * @param string $salt - Статическая соль
     * @param array $opensll_configs - Массив с конфигами opensll
     * @see http://ru2.php.net/manual/en/function.crypt.php
     */
    public function __construct($salt, $opensll_configs=array()){
        $this->_salt            = $salt;
        $this->_salt_letters    = array_merge(range(0,9), range('a','z'),range('A','Z'));
        $this->_salt_length = 22;
        if ($opensll_configs){
            $this->set_openssl_configs($opensll_configs['digest_alg'], $opensll_configs['private_key_bits'], $opensll_configs['private_key_type']);
            $this->_keys_path = QS_path(array('_data', 'keys'), true, true);
            $this->_private_key_path = QS_path(array('_data', 'keys', 'private.key'), false, false);
            $this->_public_key_path = QS_path(array('_data', 'keys', 'public.key'), false, false);
            if (!file_exists($this->_private_key_path)) {
                $this->_generate_keys();
            }
            $this->_load_keys();
        }
    }

    public function __destruct(){
        if (!is_null($this->_private_key)){
            openssl_free_key($this->_private_key);
        }
        if (!is_null($this->_public_key)){
            openssl_free_key($this->_public_key);
        }
    }

    protected function _generate_keys(){
        $private_key = openssl_pkey_new(array(
            'config'=>QS_path(array('Configs', 'openssl.cnf'), false, false),
            'digest_alg'=>$this->_digest_alg,
            'private_key_bits'=>$this->_private_key_bits,
            'private_key_type'=>OPENSSL_KEYTYPE_RSA,
        ));
        openssl_pkey_export_to_file($private_key, $this->_private_key_path);
        while(openssl_error_string()){
            //TODO: Log
            var_dump(openssl_error_string());
        }
        $keyDetails = openssl_pkey_get_details($private_key);
        file_put_contents($this->_public_key_path, $keyDetails['key']);
        openssl_free_key($private_key);
    }

    protected function _load_keys(){
        $this->_public_key = openssl_pkey_get_public('file://'.$this->_public_key_path);
        $this->_private_key = openssl_pkey_get_private('file://'.$this->_private_key_path);
    }

    public function encrypt($data){
        $result = '';
        openssl_public_encrypt($data, $result, $this->_public_key);
        return $result;
    }

    public function decrypt($data){
        $result = '';
        openssl_private_decrypt($data, $result, $this->_private_key);
        return $result;
    }

    /**
     * Метод хэширует строку алгоритмом md5.
     * @param   string  $str    Хэшируемая строка.
     * @param   int     $rounds Количество раундов (циклов хэширования).
     * @return  bool|string     Хэшированная строка или false в случае, если кол-во раундов отрицательное чило.
     */
    public function md5($str, $rounds=2){
        return $this->_hash($str, $rounds, 'md5');
    }

    /**
     * Метод хэширует строку алгоритмом sha1.
     * @param   string  $str    Хэшируемая строка.
     * @param   int     $rounds Количество раундов (циклов хэширования).
     * @return  bool|string     Хэшированная строка или false в случае, если кол-во раундов отрицательное чило.
     */
    public function sha1($str, $rounds=2){
        return $this->_hash($str, $rounds, 'sha1');
    }

    /**
     * Метод хэширует строку заданной функцией
     * @param $str
     * @param $rounds
     * @param $func
     * @return bool
     */
    protected function _hash($str, $rounds, $func){
        if ((int) $rounds > 0){
            for ($i=0;$i<(int)$rounds;$i++){
                $str = $func($str.$this->_salt);
            }
            return $str;
        }
        return false;
    }

    /**
     * Метод хэширует строку алгоритмом Blowfish.
     * Hash new pass:
     * $db_pass = $this->blowfish($pass, 11, 'UNIQUE', true)
     * Check input pass:
     * $salt = substr($db_pass, 0, 22)
     * $hashed = $this->blowfish($input_pass, 11, $salt, true)
     * $db_pass == $hashed
     * @param string        $str Хэшируемая строка.
     * @param int           $rounds  Весовой параметр из двух цифр является двоичным логарифмом счетчика итераций хэширующего алгоритма, должен быть в диапазоне 04-31.
     * @param null|string   $salt Соль для алгоритма. Либо строка длиной 22 символа, либо null.
     * @param bool          $replace_method Флаг, показвывающий, нужно ли удалять первые 7 символов из сторки, показывающие метод шифрования сторки
     * @return bool|string Хэшированная строка или false в случае ошибки входных параметров или если не потдерживается алгоритм шифрования
     */
    public function blowfish($str, $rounds=4, $salt=null, $replace_method=false){
        $rounds = (int) $rounds;
        if (($rounds < 4 && $rounds > 31) || !CRYPT_BLOWFISH){
            return false;
        }
        $rounds = (strlen(strval($rounds)) == 1 ? '0'  : '').$rounds;
        if (is_null($salt) || strlen($salt) != 22){
            $salt = $this->getUniqueSalt(22);
        }
        $method = sprintf('$2y$%s$', $rounds);
        return $this->_crypt($str, $method, $salt, $replace_method);
    }

    /**
     * Метод хэширует строку алгоритмом sha256
     * @param string $str
     * @param int $rounds
     * @param null $salt
     * @param bool $replace_method
     * @return string|bool
     */
    public function sha256($str, $rounds=5000, $salt=null, $replace_method=false){
        if (!CRYPT_SHA256){
            return false;
        }
        if (is_null($salt) || strlen($salt) != 16){
            $salt = $this->getUniqueSalt(self::MD5, 16);
        }
        $method = sprintf('$5$rounds=%s$', strval($rounds));
        return $this->_crypt($str, $method, $salt, $replace_method);
    }


    /**
     * Метод хэширует строку алгоритмом sha512
     * @param string $str
     * @param int $rounds
     * @param null $salt
     * @param bool $replace_method
     * @return string|bool
     */
    public function sha512($str, $rounds=5000, $salt=null, $replace_method=false){
        if (!CRYPT_SHA512){
            return false;
        }
        if (is_null($salt) || strlen($salt) != 16){
            $salt = $this->getUniqueSalt(self::MD5, 16);
        }
        $method = sprintf('$6$rounds=%s$', strval($rounds));
        return $this->_crypt($str, $method, $salt, $replace_method);
    }

    /**
     * Метод хэширует строку заданным методом
     * @param $str
     * @param $method
     * @param $salt
     * @param $replace_method
     * @return mixed|string
     */
    protected function _crypt($str, $method, $salt, $replace_method){
        $result = crypt($str.$this->_salt, $method.$salt);
        if ($replace_method){
            $result = substr_replace($result, '', 0, strlen($method));
        }
        return $result;
    }

    /**
     * Метод конфигурации openssl.
     * @param string $digest_alg
     * @param int $private_key_bits
     * @param string $private_key_type
     */
    public function set_openssl_configs($digest_alg=null, $private_key_bits=null, $private_key_type=null){
        $pk_types = array('rsa'=>OPENSSL_KEYTYPE_RSA, 'dh'=>OPENSSL_KEYTYPE_DH, 'dsa'=>OPENSSL_KEYTYPE_DSA);
        if (!is_null($digest_alg)){
            $this->_digest_alg = (string) $digest_alg;
        }
        if (!is_null($private_key_bits)){
            $this->_private_key_bits = (int) $private_key_bits;
        }
        if (!is_null($private_key_type) && isset($pk_types[strtolower($private_key_type)])){
            $this->_private_key_type = $pk_types[strtolower($private_key_type)];
        }
        $this->_openssl_configure = true;
    }


    /**
     * Генерирует соль заданной длины
     * @param null $length
     * @return string
     */
    public function getUniqueSalt($length=null){
        $salt = '';
        $length = is_null($length) ? $this->_salt_length : (int) $length;
        while (strlen($salt) < $this->_salt_length){
            $salt .= md5(uniqid().$this->_salt);
        }
        $start = mt_rand(0, strlen($salt) - $length-1);
        return substr($salt, $start, $length);
    }

    /**
     * Метод хэширует строку по алгоритму $alg заданное кол-во раз, но не менее 1.
     * @param string $str   Строка для шифрования
     * @param int $alg      Константа алгоритма (md5, sha1)
     * @param int $rounds   Количество раундов шифрования
     * @return string|bool  Хэшированная строка
     */
    public function hashStr($str, $alg=self::MD5, $rounds=2){
        if ($rounds > 0) {
            for ($i = 0; $i < $rounds; $i++) {
                $str = $alg === self::MD5 ? md5($str . $this->_salt) : sha1($str . $this->_salt);
            }
            return $str;
        }
        return false;
    }

    /**
     * Декодирует строку данных из шестнадцатеричного представления
     * @param string $hex_string
     * @return string
     */
    protected function hex2bin($hex_string){
        return function_exists('hex2bin') ? hex2bin($hex_string) : pack("H*" , $hex_string);
    }

    /**
     * Метод возвращает значение свойства
     * @param $opt
     * @return mixed
     */
    public function get($opt){
        $prop = "_{$opt}";
        return (isset($this->$prop)) ?$this->$prop : null;
    }

    /**
     * Метод устанавливает значение свойства
     * @param $opt
     * @param $value
     * @return mixed
     */
    public function set($opt, $value){
        $prop = "_{$opt}";
        if (isset($this->$prop)){
            $previous = $this->$prop;
            $this->$prop = $value;
        }
        return isset($previous) ? $previous : null;
    }
}