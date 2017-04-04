<?php
namespace Core;
use Core\Cache\Cache;
use Core\DataBase\Connection;
use Core\DataBase\EntityManager;
use Core\Event\EventManager;
use Core\Router\Router;
use Core\Session\Session;

class Core{

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var Cache
     */
    protected $cache;

    /**
     * @var Crypt
     */
    protected $crypt;

    /**
     * @var Router
     */
    protected $router;

    /**
     * @var Input
     */
    protected $input;

    /**
     * @var EventManager
     */
    protected $eventManager;

    /**
     * @var Connection
     */
    protected $db = null;

    /**
     * @var Application
     */
    protected $app;

    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var PHPDumper
     */
    protected $phpDumper;

    /**
     * @var Config
     */
    protected $config;

    public function __construct(Config $config, Application $app){
        $this->config = $config;
        $this->app = $app;

        $this->eventManager = new EventManager();

        $cryptConf = $this->config->get('crypt');
        $this->crypt = new Crypt($cryptConf['salt'], array('digest_alg'=>'sha256', 'private_key_bits'=>1024, 'private_key_type'=>'rsa'));

        $this->input = new Input();

        $cacheConf = $this->config->get('cache');
        $this->cache = $cacheConf['enable'] == 1 ?
            new Cache(
                $cacheConf
            ) : null;

        $this->router = new Router($this->cache);

        $session_options = $this->config->get('session');
        $session_options['cookie'] = $this->config->get('cookie');
        $this->session = new Session($session_options, $this->input, $this->eventManager);
    }

    /**
     * @return Session
     */
    public function getSession(){
        return $this->session;
    }

    public function destroySession(){
        unset($this->session);
    }

    /**
     * @return Cache
     */
    public function getCache(){
        return $this->cache;
    }

    /**
     * @return Crypt
     */
    public function getCrypt(){
        return $this->crypt;
    }

    /**
     * @return Router
     */
    public function getRouter(){
        return $this->router;
    }

    /**
     * @return Input
     */
    public function getInput(){
        return $this->input;
    }

    /**
     * @return EventManager
     */
    public function getEventManager(){
        return $this->eventManager;
    }

    /**
     * @return Connection
     */
    public function getDb(){
        if (is_null($this->db)){
            //var_dump('INIT_DB');
            $dbConf = $this->config->get('database');
            $this->db = new Connection($dbConf['host'], $dbConf['user_name'], $dbConf['password'], $dbConf['db_name'], $dbConf['charset'], $dbConf['port']);
            //$this->db = new DataBase($dbConf['host'], $dbConf['user_name'], $dbConf['password'], $dbConf['db_name'], $dbConf['charset'], $dbConf['port']);
        }
        return $this->db;
    }

    /**
     * @return Application
     */
    public function getApp(){
        return $this->app;
    }

    /**
     * @return EntityManager
     */
    public function getEntityManager(){

        if (is_null($this->entityManager)){
            $this->entityManager = new EntityManager($this);
        }
        return $this->entityManager;
    }

    /**
     * @return PhpDumper
     */
    public function getPhpDumper(){
        if (is_null($this->phpDumper)){
            $this->phpDumper = new PhpDumper($this->config->get('dumper.path'), $this->config->get('dumper.salt'));
        }
        return $this->phpDumper;
    }

    /**
     * @return Config
     */
    public function getConfig(){
        return $this->config;
    }
}