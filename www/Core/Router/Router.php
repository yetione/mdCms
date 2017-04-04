<?php
namespace Core\Router;


use Core\Cache\Cache;
use Core\DataBase;
use Core\DataBase\Connection;
use Core\Meter;

class Router {


    /**
     * Array that holds all Route objects
     *
     * @var RouteCollection
     */
    private $routes;
    /**
     * Array to store named routes in, used for reverse routing.
     * @var Route[]
     */
    private $namedRoutes = array();
    /**
     * The base REQUEST_URI. Gets prepended to all route url's.
     * @var string
     */
    private $basePath = '';

    /**
     * @var \Core\Cache\Cache
     */
    protected $cache;

    protected $changed = false;

    protected $path;

    /**
     * @var \Core\DataBase\Connection
     */
    protected $db;

    protected $inCache = true;

    protected $readRoutesFromDB = true;

    protected $inFile = true;

    protected $inDB = true;

    const CACHE_KEY = 'QSRouters';

    /**
     * @param Cache $cache
     * @param Connection $db
     * @internal param QSRouteCollection $collection
     */
    /*public function __construct(QSRouteCollection $collection)
    {
        $this->routes = $collection;
    }*/
    public function __construct(Cache $cache, Connection $db=null){
        $this->path = QS_path(array('_cache', 'routes'), false, false, true);
        $this->cache = $cache;
        $this->db = $db;
        $this->readRoutesFromDB = is_null($db) ? false : true;
        $this->readRoutes();

    }

    public function clear(){

        $this->cache->set(Router::CACHE_KEY, null);
        unlink($this->path);
        return true;
    }

    public function readRoutes(){
        if (!$this->readFromCache()){
            $this->inCache = false;
            if (!$this->readFromFile()){
                $this->inFile = false;
                if (($this->readRoutesFromDB && !$this->readFromDB()) || !$this->readRoutesFromDB){
                    $this->routes = new RouteCollection();
                }
            }
        }
        /*
        $this->routes = $this->cache->get(QSRouter::CACHE_KEY);
        if ( is_null($this->routes) ) {
            $this->inCache = false;
            if (file_exists($this->path)) {
                $meter = new QSMeter('Чтение роутов из файла');
                $meter->dir(array('logs', 'routers_create'))->run();
                $this->routes = unserialize(file_get_contents($this->path));
                $meter->end();
            } else {
                $routes = array();
                if ($this->readFromDB){
                    $this->changed = true;
                    $meter = new QSMeter('Чтение роутов из БД');
                    $meter->dir(array('logs','routers_create'))->run();
                    $qr = $this->db->prepare('SELECT `url`, `data` FROM `routes`');
                    $qr->execute();
                    $qr->bindColumn(1, $url);
                    $qr->bindColumn(2, $data);
                    while ($row = $qr->fetch(\PDO::FETCH_BOUND)) {
                        $routes[] = new QSRoute($url, unserialize($data));
                    }
                    $meter->end();
                }
                $this->routes = new QSRouteCollection($routes);
            }
        }
        */
    }

    public function __destruct(){
        if ($this->changed){
            $this->writeToCache();
            $this->writeToFile();

        }
        if (!$this->inCache){
            $this->cache->set(Router::CACHE_KEY, $this->routes, 60*60*24*365);
        }
    }

    protected function readFromFile(){
        if (file_exists($this->path)) {
            $meter = new Meter('Чтение роутов из файла');
            $meter->dir(array('logs', 'routers_create'))->run();
            if (($content = file_get_contents($this->path)) === false){
                // TODO: Log it!
                $meter->end();
                return false;
            }
            if (($this->routes = unserialize($content)) === false){
                // TODO: Log it!
                $meter->end();
                return false;
            }
            $meter->end();
            return true;
        }
        return false;
    }

    /**
     * @return int
     */
    protected function writeToFile(){
        $meter = new Meter('Запись роутов в файл');
        $meter->dir(array('logs','routers_create'))->run();
        $result = file_put_contents($this->path, serialize($this->routes));
        $meter->end();
        if ($result === false){
            // TODO: Log it!
        }
        return $result;
    }

    protected function readFromCache(){
        $meter = new Meter('Чтение роутов из кэша');
        $meter->dir(array('logs','routers_create'))->run();
        $this->routes = $this->cache->get(Router::CACHE_KEY);
        $result = true;
        if (is_null($this->routes)){
            // TODO: Log it!
            $result = false;
        }
        $meter->end();
        return $result;
    }

    protected function writeToCache($time=null){
        $meter = new Meter('Запись роутов в кэш');
        $meter->dir(array('logs','routers_create'))->run();
        $result = $this->cache->set(Router::CACHE_KEY, $this->routes, is_null($time) ? 60*60*24*365 : (int) $time);
        $meter->end();
        if ($result === false){
            // TODO: Log it!
        }
        return $result;
    }

    protected function readFromDB(){
        $meter = new Meter('Чтение роутов из БД');
        $meter->dir(array('logs','routers_create'))->run();
        $qr = $this->db->prepare('SELECT `url`, `data` FROM `routes`');
        if (!$qr->execute()){
            // TODO: Log it!
            $meter->end();
            return false;
        }
        $routes = array();
        $qr->bindColumn(1, $url);
        $qr->bindColumn(2, $data);
        while ($row = $qr->fetch(\PDO::FETCH_BOUND)) {
            $routes[] = new Route($url, unserialize($data));
        }
        $this->routes = new RouteCollection($routes);
        $meter->end();
        return true;
    }

    protected function writeToDB(){
        $data=$this->routes->all();
        $qr = $this->db->prepare('INSERT INTO `routes` (`url`, `data`) VALUES (?, ?)');
        $meter = new Meter('Запись роутов в таблицу');
        $meter->dir(array('logs','routers_create'))->run();
        foreach ($data as $route){
            $qr->bindParam(1, $route->getUrl(), \PDO::PARAM_STR);
            $qr->bindParam(2, serialize($route->getConfig()), \PDO::PARAM_STR);
            if (!$qr->execute()){
                // TODO: Log it!
            }
        }
        $meter->end();
        return true;
    }

    public function add(Route $route){
        if ($this->routes->attachRoute($route)){
            $this->changed = true;
            return true;
        }
        return false;
    }

    /**
     * @param mixed $data
     * @return bool
     */
    public function remove($data){
        if ($this->routes->remove($data)){
            $this->changed = true;
            return true;
        }
        return false;
    }

    public function getCollection(){
        return $this->routes;
    }



    /**
     * Set the base _url - gets prepended to all route _url's.
     * @param $basePath
     */
    public function setBasePath($basePath)
    {
        $this->basePath = (string) $basePath;
    }

    /**
     * Matches the current request against mapped routes
     * @param null|string $requestUrl
     * @return bool|Route
     */
    public function matchCurrentRequest($requestUrl)
    {
        $requestMethod = (
            isset($_POST['_method'])
            && ($_method = strtoupper($_POST['_method']))
            && in_array($_method, array('PUT', 'DELETE'))
        ) ? $_method : $_SERVER['REQUEST_METHOD'];
        // strip GET variables from URL
        if (($pos = strpos($requestUrl, '?')) !== false) {
            $requestUrl =  substr($requestUrl, 0, $pos);
        }
        return $this->match($requestUrl, $requestMethod);
    }
    /**
     * Match given request _url and request method and see if a route has been defined for it
     * If so, return route's target
     * If called multiple times
     *
     * @param string $requestUrl
     * @param string $requestMethod
     *
     * @return bool|Route
     */
    public function match($requestUrl, $requestMethod = 'GET')
    {
        foreach ($this->routes->all() as $routes) {
            // compare server request method with route's allowed http methods
            if (! in_array($requestMethod, (array) $routes->getMethods())) {
                continue;
            }

            // check if request _url matches route regex. if not, return false.

            if (! preg_match("@^" . $this->basePath . $routes->getRegex() . "*$@i", $requestUrl, $matches)) {
                continue;
            }
            $params = array();
            if (preg_match_all("/:([\w-%]+)/", $routes->getUrl(), $argument_keys)) {
                // grab array with matches
                $argument_keys = $argument_keys[1];
                // loop trough parameter names, store matching value in $params array
                foreach ($argument_keys as $key => $name) {
                    if (isset($matches[$key + 1])) {
                        $params[$name] = $matches[$key + 1];
                    }
                }
            }
            $routes->setParameters($params);
//            $routes->dispatch();
            return $routes;
        }
        return false;
    }
    /**
     * Reverse route a named route
     *
     * @param $routeName
     * @param array $params Optional array of parameters to use in URL
     *
     * @throws \Exception
     *
     * @internal param string $route_name The name of the route to reverse route.
     *
     * @return string The url to the route
     */
    public function generate($routeName, array $params = array())
    {
        // Check if route exists
        if (! isset($this->namedRoutes[$routeName])) {
            throw new \Exception(__CLASS__.": No route with the name $routeName has been found.");
        }
        $route = $this->namedRoutes[$routeName];
        $url = $route->getUrl();
        // replace route url with given parameters
        if ($params && preg_match_all("/:(\w+)/", $url, $param_keys)) {
            // grab array with matches
            $param_keys = $param_keys[1];
            // loop trough parameter names, store matching value in $params array
            foreach ($param_keys as $key) {
                if (isset($params[$key])) {
                    $url = preg_replace("/:(\w+)/", $params[$key], $url, 1);
                }
            }
        }
        return $url;
    }
    /**
     * Create routes by array, and return a Router object
     *
     * @param array $config provide by Config::loadFromFile()
     * @return Router
     */
    public static function parseConfig(array $config)
    {
        $collection = new RouteCollection();
        foreach ($config['routes'] as $name => $route) {
            $collection->attachRoute(new Route($route[0], array(
                '_controller' => str_replace('.', '::', $route[1]),
                'methods' => $route[2]
            )));
        }

        $router = new Router($collection);
        if (isset($config['base_path'])) {
            $router->setBasePath($config['base_path']);
        }

        return $router;
    }

} 