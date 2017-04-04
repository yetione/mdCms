<?php
namespace Core\Module;

use Core\Config;
use Core\Debugger;
use Core\Module\Base\Installer;
use Core\Module\Base\Module;
use Core\Module\Exception\DisabledModule;
use Core\Module\Exception\InvalidModule;
use Core\Module\Exception\NotFound;

class ModuleManager {

    const MODULES = 'modules';

    const AUTOLOAD = 'autoload';

    const MODULE_DISABLED = -1;

    /**
     * Путь до файла с описанием модулей
     * @var string
     */
    protected $modulesFilePath;

    /**
     * Имя кэшированного файла с модулями
     * @var string
     */
    protected $modulesCachedFileName;

    /**
     * Путь до файла с кэшированной информацией о модулях
     * @var string
     */
    protected $modulesCachedFilePath;

    /**
     * Путь до файла автозагрузки
     * @var string
     */
    protected $autoloadFilePath;

    /**
     * Имя кэшированного файла со списком автозагрузки
     * @var string
     */
    protected $autoloadCachedFileName;

    /**
     * Путь до кэшированного файла с автозагрузкой
     * @var string
     */
    protected $autoloadCachedFilePath;

    /**
     * @var string
     */
    protected $modulesDir;

    /**
     * @var array[]
     */
    protected $modules = array();

    /**
     * @var Module[]|callable[]
     */
    protected $loadedModules = array();

    /**
     * @var array[]
     */
    protected $autoload = array();

    /**
     * @var Config
     */
    protected $configs;

    protected $moduleBaseClass = '\\Core\\Module\\Base\\Module';

    public function __construct($modulesFilePath, $autoloadFilePath, $modulesDir, Config $configs){
        $this->modulesFilePath = $modulesFilePath;
        $this->modulesCachedFileName = 'modulesCached.dat';
        $this->modulesCachedFilePath = dirname($modulesFilePath).DIRECTORY_SEPARATOR.$this->modulesCachedFileName;


        $this->autoloadFilePath = $autoloadFilePath;
        $this->autoloadCachedFileName = 'autoloadCached.dat';
        $this->autoloadCachedFilePath = dirname($autoloadFilePath).DIRECTORY_SEPARATOR.$this->autoloadCachedFileName;

        $this->modulesDir = $modulesDir;

        $this->configs = $configs;

        $this->load(self::MODULES);
        $this->load(self::AUTOLOAD);
    }

    public function startAutoload($inputFormat){
        foreach ($this->autoload as $format => $modules){
            if ($format === $inputFormat || $format === 'All'){
                foreach ($modules as $module){
                    $this->getModule($module);
                }
            }
        }
        /*foreach ($this->autoload as $module){
            $this->getModule($module);
        }*/

        return true;
    }


    public function loadData(){
        $this->load(self::MODULES);
        $this->load(self::AUTOLOAD);
    }

    /**
     * Метод загружает информацию о модулях или автозагрузке модулей.
     * @param string $what modules|autoload в зависимости от требуемого списка.
     * @return bool
     */
    protected function load($what){
        $cachedPath = $what == self::MODULES ? $this->modulesCachedFilePath : $this->autoloadCachedFilePath;
        $path = $what == self::MODULES ? $this->modulesFilePath : $this->autoloadFilePath;
        if (!file_exists($cachedPath) ||
            filemtime($path) > filemtime($cachedPath)){
            return $this->serialize($what);
        }else{
            if (!$this->unserialize($what)){
                return $this->serialize($what);
            }
            return true;
        }
    }


    /**
     * Метод сериализует список модулей или автозагрузки
     * @param string $what modules|autoload в зависимости от требуемого списка.
     * @return bool
     */
    protected function serialize($what){
        $this->read($what);
        $list = $what == self::MODULES ? $this->modules : $this->autoload;
        if ($list){
            $path = $what == self::MODULES ? $this->modulesCachedFilePath : $this->autoloadCachedFilePath;
            if (file_put_contents($path, serialize($list)) !== false){
                return true;
            }
        }
        //TODO: Raise error: Can't serialize $what;
        return false;
    }

    /**
     * Метод читает список модулей или автозагрузки в переменные класса.
     * @param string $what modules|autoload в зависимости от требуемого списка.
     * @return bool
     */
    protected function read($what){
        return $what == self::MODULES ? $this->modulesToArray() : $this->autoloadToArray();
    }

    /**
     * Очищает информацию о модулях
     * @return bool
     */
    public function clear(){
        unlink($this->modulesCachedFilePath);
        $this->modules = array();
        unlink($this->autoloadCachedFilePath);
        $this->autoload = array();
        return true;
    }


    /**
     * Метод конвертирует XML файл со списком модулей в массив.
     * @return bool
     */
    protected function modulesToArray(){
        $xml = simplexml_load_file($this->modulesFilePath);
        $this->modules = array_map(function($item){
            $result = array();
            $result['Configs'] = array_map(function ($item){
                if (!isset($item['value'])){
                    throw new InvalidModule(__CLASS__.': Config:'.$item.' not have value');
                }
                return (string) $item['value'];
            }, isset($item->Configs) ? (array) $item->Configs : array());

            $result['Dependency'] = array_map(function ($item){
                return $item;
            }, isset($item->Dependency['modules']) ? explode(',', $item->Dependency['modules']) : array());
            $result['Enabled'] = isset($item->Enabled) ? (bool) (int) $item->Enabled : true;
            $result['AutoInstall'] = isset($item->AutoInstall) ? (bool) (int) $item->AutoInstall : true;
            $result['Installed'] = false;

            $adminMenu = array();
            if (isset($item->AdminMenu)){

            }

            return $result;
        }, (array) $xml);
        return (bool) count($this->modules);
    }

    /**
     * Метод конвертирует XML файл автозагрузки в массив.
     * @return bool
     */
    protected function autoloadToArray(){
        $xml = simplexml_load_file($this->autoloadFilePath);
        $this->autoload = array();
        foreach ($xml as $format=>$modules){
            $this->autoload[$format] = array();
            foreach ($modules as $module){
                $this->autoload[$format][] = $module->getName();
            }
        }
        //$this->autoload = array_keys((array) $xml);
        return (bool) count($this->autoload);

    }

    /**
     * Метод десериализует список модулей или автозагрузки
     * @param string $what modules|autoload в зависимости от требуемого списка.
     * @return bool
     */
    protected function unserialize($what){
        $path = $what == self::MODULES ? $this->modulesCachedFilePath : $this->autoloadCachedFilePath;
        if (($serialized = file_get_contents($path)) !== false){
            if ($what == self::MODULES){
                $this->modules = unserialize($serialized);
            }else{
                $this->autoload = unserialize($serialized);
            }
            return true;
        }
        //TODO: Raise error: Can't unserialize: $what;
        return false;
    }

    /**
     * Метод добавляет модуль.
     * @param string $name Название модуля
     * @param mixed $object Объект модуля, callable для lazy load
     * @return bool
     */
    public function addModule($name, $object){
        $this->loadedModules[$name] = $object;
        return true;
    }

    /**
     * @param string $moduleName
     * @throws DisabledModule Исключние возбуждается если класс модуля не является потомком базового класса
     * @throws InvalidModule Исключние возбуждается при отсутствие класса модуля
     * @throws NotFound Исключние возбуждается при отсутсвие информации о модуле
     * @return Module
     */
    public function getModule($moduleName){
        if (!isset($this->loadedModules[$moduleName])){
            if (isset($this->modules[$moduleName])){
                $module = $this->modules[$moduleName];
                $className = 'Modules\\'.$moduleName.'\\'.$moduleName;
                if (!class_exists($className)){
                    throw new NotFound(__CLASS__.': Module\'s class not found '.$className);
                }
                if (!is_a($className, $this->moduleBaseClass, true)){
                    throw new InvalidModule(__CLASS__.': Module: '.$moduleName.' is not subclass of '.$this->moduleBaseClass);
                }
                if (!$module['Enabled']){
                    throw new DisabledModule(__CLASS__.': Module '.$moduleName.' is disabled');
                }
                $this->installModule($moduleName);

                //Обработка секции настроек
                foreach ($this->modules[$moduleName]['Configs'] as $key){
                    if (substr($key, 0, 4) == 'cfg:'){
                        $value = $this->configs->get(substr($key, 4));
                    }
                }
                unset($key);
                unset($value);

                //Обработка секции зависимостей
                foreach ($this->modules[$moduleName]['Dependency'] as &$dependency){
                    $dependency = $this->getModule($dependency);
                }
                unset($dependency);

                /**
                 * @var Base\Module $obj
                 */
                $obj = new $className($this->modules[$moduleName]['Configs'], $this->modules[$moduleName]['Dependency'], $this);
                $this->loadedModules[$moduleName] = $obj;
            }else{
                throw new InvalidModule(__CLASS__.': Unknown module: '.$moduleName);
            }
        }
        if (is_callable($this->loadedModules[$moduleName])){
            $this->loadedModules[$moduleName] = call_user_func($this->loadedModules[$moduleName]);
        }
        return $this->loadedModules[$moduleName];
    }

    /**
     * Метод выполняет установку всех модулей. Если $reinstall то будет выполнена повторная установка
     * @param bool $reinstall
     */
    public function installModules($reinstall=false){
        foreach ($this->modules as $moduleName => $module){
            if (!$module['AutoInstall']) {continue;}
            if (!$this->installModule($moduleName) && $reinstall){
                $this->installModule($moduleName, true);
            }
        }
    }

    /**
     * Метод выполняет установку модуля
     * @param $moduleName
     * @param bool $isReinstall
     * @return bool
     */
    public function installModule($moduleName, $isReinstall=false){

        //var_dump($this->modules[$moduleName]);
        $moduleData = $this->modules[$moduleName];
        $className = 'Modules\\'.$moduleName.'\\Installer';
        if (class_exists($className) && (!$moduleData['Installed'] || $isReinstall)){
            /**
             * @var Installer $installer
             */
            $installer = new $className($this->getModule('Core'), $this);
            $installer->install();
            $moduleData['Installed'] = true;
            return true;
        }
        else{
            $install = QS_path(array($this->modulesDir, $moduleName, 'install.php'), false, false, false);
            $installed = QS_path(array($this->modulesDir, $moduleName, 'installed.php'), false, false, false);
            $path = $install;
            if (file_exists($installed) && $isReinstall && (filemtime($install) <= filemtime($installed))){
                $path = $installed;
            }


            /*$path = !$isReinstall ? QS_path(array($this->modulesDir, $moduleName, 'install.php'), false, false, false) :
                QS_path(array($this->modulesDir, $moduleName, 'installed.php'), false, false, false);*/
            if (file_exists($path)){
                try{
                    include_once $path;
                }catch (\Exception $e){
                    Debugger::log('ModuleManager::installModule: Cant install module '.$moduleName.' '.$e->getMessage());
                }
                $func = $moduleName.'_install';
                if (function_exists($func)){
                    $func($this->getModule('Core'), $this);
                    //$module = $this->getModule($moduleName);
                    //$module->onAfterInstall();
                }
                rename($path, QS_path(array($this->modulesDir, $moduleName, 'installed.php'), false, false, false));
                return true;
            }
            return false;
        }


    }
} 