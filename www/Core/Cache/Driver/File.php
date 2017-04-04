<?php
namespace Core\Cache\Driver;


use Core\Cache\Exception\CacheException;

class File extends  AbstractDriver{

    /**
     * @var string
     */
    protected $path;

    /**
     * @var int
     */
    protected $chmod = 0777;

    protected $filePrefix = 'qsCache.';

    protected $cacheData = array();

    const LOAD_TIME = '_loadTime';


    public function __construct($config = array()){
        $this->setup($config);
        $this->path = $config['path'];
        $this->chmod = $config['chmod'] == '' || $config['chmod'] == 'auto' ? 0777 : $config['chmod'];
    }

    protected function getFileName($key){
        return QS_path(array($this->path, $this->filePrefix.$key.'.cache'), false, false, false);
    }

    protected function getFilePattern(){
        return QS_path(array($this->path, $this->filePrefix.'*'.'.cache'), false, false, false);
    }

    protected function fileNameToKey($fileName){
        $l = strlen(QS_path(array($this->path, $this->filePrefix), false, false, false));
        return str_replace('.cache', '' ,substr($fileName, $l));
    }

    public function getKeys(){
        $result = array();
        foreach (glob($this->getFilePattern()) as $file){
            $result[] =$this->fileNameToKey($file);
        }
        return $result;
    }

    /**
     * @param $key
     * @param string $options
     * @return mixed
     * @throws CacheException
     */
    public function get($key, $options=self::VALUE){
        $fileName = $this->getFileName($key);
        if (!file_exists($fileName)){
            return $this->onKeyNotFoundAction($key);
        }
        if (!isset($this->cacheData[$key]) || $this->cacheData[$key][self::LOAD_TIME] < fileatime($fileName)){
            $this->cacheData[$key] = $this->loadFromFile($fileName);
            $this->cacheData[$key][self::LOAD_TIME] = time();
        }
        //var_dump($this->cacheData[$key]);
        $expireTime = $this->cacheData[$key][self::LIFE_TIME] == 0 ? 0 :
            $this->cacheData[$key][self::CREATE_TIME] + $this->cacheData[$key][self::LIFE_TIME];
        //var_dump($expireTime, time(), 'exp');
        if ($expireTime <= time() && $expireTime > 0){
            unlink($fileName);
            unset($this->cacheData[$key]);
            return $this->onKeyExpireAction($key);
        }else{
            $ttl = $expireTime == 0 ? 0 : $expireTime - time();
        }
        $this->cacheData[$key][self::TTL] = $ttl;
        //var_dump($this->cacheData[$key]);
        if (is_null($options)){
            return $this->cacheData[$key];
        }elseif (is_string($options) && isset($this->cacheData[$key][$options])){
            return $this->cacheData[$key][$options];
        }elseif (is_array($options)){
            $result = array();
            foreach ($options as $option) {
                $result[$option] = $this->cacheData[$key][$option];
            }
            return $result;
        }else{
            throw new CacheException(__CLASS__.': Wrong type of options: '.gettype($options).' for key: '.$key);
        }

    }

    /**
     * @param $fileName
     * @return mixed
     * @throws CacheException
     */
    protected function loadFromFile($fileName){
        if (!is_array($result = unserialize(file_get_contents($fileName)))){
            throw new CacheException(__CLASS__.': cant load file data: '.$fileName);
        }
        return $result;
    }

    /**
     * @param $fileName
     * @param $data
     * @return int
     */
    protected function writeToFile($fileName, $data){
        return file_put_contents($fileName, serialize($data));
    }

    public function set($key, $value, $ttl=null, $override=true){
        $fileName = $this->getFileName($key);
        $ttl = is_null($ttl) ? $this->getDefaultTTL() : (int) $ttl;
        if (!file_exists($fileName) || $override){
            try{
                $data = $this->get($key, null);
                $data[self::VALUE] = $value;
                $data[self::ACCESS_TIME] = time();
                if (!isset($data[self::CREATE_TIME])){
                    $data[self::CREATE_TIME] = time();
                }
                if ($ttl > -1){
                    $data[self::LIFE_TIME] = $ttl;
                }

            }catch (CacheException $e){
                if ($ttl == -1){
                    return false;
                }
                $data = array(
                    self::KEY => $key,
                    self::VALUE => $value,
                    self::CREATE_TIME => time(),
                    self::ACCESS_TIME => time(),
                    self::LIFE_TIME => $ttl
                );
            }
            $data[self::LOAD_TIME] = time();
            $this->cacheData[$key] = $data;
            $this->writeToFile($fileName, $data);
            return true;
        }
        return false;
    }

    public function checkdriver(){
        return is_writable($this->path);
    }

    private function encodeFilename($keyword) {
        return trim(trim(preg_replace("/[^a-zA-Z0-9]+/","_",$keyword),"_"));
    }

    private function decodeFilename($filename) {
        return $filename;
    }

    private function getFilePath($keyword, $skip = false) {
        $path = $this->path;

        $filename = $this->encodeFilename($keyword);
        $folder = substr($filename,0,2);
        $path = rtrim($path,DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.$folder;
        /*
         * Skip Create Sub Folders;
         */
        if($skip == false) {
            if(!file_exists($path)) {
                if(!@mkdir($path,$this->chmod, true)) {
                    throw new CacheException("PLEASE CHMOD ".$this->path." - 0777 OR ANY WRITABLE PERMISSION!",92);
                }
            } elseif(!is_writeable($path)) {
                if(!chmod($path,$this->chmod)) {
                    die("PLEASE CHMOD ".$this->path." - 0777 OR ANY WRITABLE PERMISSION! MAKE SURE PHP/Apache/WebServer have Write Permission");
                }
            }
        }
        $file_path = $path.DIRECTORY_SEPARATOR.$filename.".txt";
        return $file_path;
    }

    public function driver_set($keyword, $value = "", $time = 300, $option = array()){
        $file_path = $this->getFilePath($keyword);
        $data = $this->encode($value);
        $toWrite = true;
        /*
         * Skip if Existing Caching in Options
         */
        if(isset($option['skipExisting']) && $option['skipExisting'] == true && file_exists($file_path)) {
            $content = $this->readfile($file_path);
            $old = $this->decode($content);
            $toWrite = false;
            if($this->isExpired($old)) {
                $toWrite = true;
            }
        }
        if($toWrite == true) {
            try {
                $f = fopen($file_path, "w+");
                fwrite($f, $data);
                fclose($f);
                return true;
            } catch (\Exception $e) {
                // miss cache
                return false;
            }
        }
        return true;
    }

    public function driver_get($keyword, $option = array()){
        $file_path = $this->getFilePath($keyword);
        if(!file_exists($file_path)) {
            return null;
        }
        $content = $this->readfile($file_path);
        $object = $this->decode($content);
        if($this->isExpired($object)) {
            @unlink($file_path);
            $this->auto_clean_expired();
            return null;
        }
        return $object;
    }

    public function driver_stats($option = array()){
        $res = array(
            "info"  =>  "",
            "size"  =>  "",
            "data"  =>  "",
        );
        $path = $this->path;
        $dir = @opendir($path);
        if(!$dir) {
            throw new CacheException("Can't read PATH:".$path,94);
        }
        $total = 0;
        $removed = 0;
        while($file=readdir($dir)) {
            if($file!="." && $file!=".." && is_dir($path."/".$file)) {
                // read sub dir
                $subdir = @opendir($path."/".$file);
                if(!$subdir) {
                    throw new CacheException("Can't read path:".$path."/".$file,93);
                }
                while($f = readdir($subdir)) {
                    if($f!="." && $f!="..") {
                        $file_path = $path."/".$file."/".$f;
                        $size = filesize($file_path);
                        $object = $this->decode($this->readfile($file_path));
                        if($this->isExpired($object)) {
                            @unlink($file_path);
                            $removed = $removed + $size;
                        }
                        $total = $total + $size;
                    }
                } // end read subdir
            } // end if
        } // end while
        $res['size']  = $total - $removed;
        $res['info'] = array(
            "Total" => $total,
            "Removed"   => $removed,
            "Current"   => $res['size'],
        );
        return $res;
    }

    function driver_delete($keyword, $option = array()) {
        $file_path = $this->getFilePath($keyword,true);
        if(@unlink($file_path)) {
            return true;
        } else {
            return false;
        }
    }

    public function driver_clean($option = array()){
        $path = $this->path;
        $dir = @opendir($path);
        if(!$dir) {
            throw new CacheException("Can't read PATH:".$path,94);
        }
        while($file=readdir($dir)) {
            if($file!="." && $file!=".." && is_dir($path."/".$file)) {
                // read sub dir
                $subdir = @opendir($path."/".$file);
                if(!$subdir) {
                    throw new CacheException("Can't read path:".$path."/".$file,93);
                }
                while($f = readdir($subdir)) {
                    if($f!="." && $f!="..") {
                        $file_path = $path."/".$file."/".$f;
                        @unlink($file_path);
                    }
                } // end read subdir
            } // end if
        } // end while
    }

    function driver_isExisting($keyword) {
        $file_path = $this->getFilePath($keyword,true);
        if(!file_exists($file_path)) {
            return false;
        } else {
            // check expired or not
            $value = $this->driver_get($keyword);
            if($value == null) {
                return false;
            } else {
                return true;
            }
        }
    }

    function isExpired($object) {
        if(isset($object['expired_time']) && @date("U") >= $object['expired_time']) {
            return true;
        } else {
            return false;
        }
    }

    function auto_clean_expired() {
        $autoclean = $this->driver_get("keyword_clean_up_driver_files");
        if($autoclean == null) {
            $this->driver_set("keyword_clean_up_driver_files",3600*24);
            $res = $this->driver_stats();
        }
    }

    public static function __set_state(array $data)
    {
        return new self($data['config']);
    }
}