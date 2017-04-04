<?php
namespace Core;

/**
 * Класс для сохранения данных в PHP файлах
 * Class QSPhpDumper
 * @package Core
 */
class PhpDumper {

    /**
     * Путь для сохранения файлов
     * @var string
     */
    protected $path;

    /**
     * Соль для хэширования имени
     * @var string
     */
    protected $salt;

    /**
     * @param string $path
     * @param string $salt
     */
    public function __construct($path, $salt){
        if (!file_exists($path)){
            if (!mkdir($path, 0755, true)){
                throw new \RuntimeException(__CLASS__.': Can\'t create directory: '.$path);
            }
        }
        $this->path = $path;
        $this->salt = strval($salt);
    }

    public function clear(){
        array_map('unlink', glob(QS_path(array($this->path, '*.php'), false, false, false)));
        return true;
    }

    /**
     * Метод проверяет, можно ли экспортировать переменную
     * @param mixed $var
     * @return bool
     */
    public function isExportable($var){
        return is_scalar($var) || is_array($var) || $var instanceof IExportable;
    }

    /**
     * Метод возвращает имя файла по ключу
     * @param $key
     * @return string
     */
    protected function _path($key){
        return QS_path(array($this->path, md5($this->salt.$key).'.php'), false, false, false);
    }

    /**
     * Метод сохраняет переменную в PHP файле
     * @param string $key
     * @param mixed $var
     * @param bool $override
     * @return bool
     */
    public function save($key, $var, $override=true){
        if ($this->isExportable($var)){
            $content = '<?php return '.var_export($var, true).';';
            $path = $this->_path($key);
            if (!file_exists($path) || $override){
                file_put_contents($path, $content);
                return true;
            }
        }
        return false;
    }

    /**
     * Метод возвращает значение переменной,
     * ранее сохраненной в PHP файле
     * @param string $key
     * @param mixed|null $default
     * @return bool|mixed
     */
    public function load($key, $default=null){
        $path = $this->_path($key);
        if (file_exists($path)){
            return include $path;
        }
        return $default;
    }

    /**
     * Удаляет ключ из хранилища
     * @param string $key
     * @return bool
     */
    public function remove($key){
        return unlink($this->_path($key));
    }
} 