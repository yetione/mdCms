<?php
namespace Core;


use Core\Cache\Cache;
use Core\DataBase\EntityManager;
use Core\DataBase\Model\Relationship;
use Core\Event\EventManager;
use Core\Module\ModuleManager;
use Core\Response\JSONResponse;
use Core\Response\HTMLResponse;
use Core\Response\PDFResponse;
use Core\Response\Response;
use Core\Router\Router;

class Application {
    /**
     * @var string
     */
    protected $name;

    /**
     * @var ModuleManager
     */
    protected $moduleManager;

    /**
     * @var Config
     */
    public $config;

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
     * @var \Autoloader
     */
    protected $autoLoader;

    /**
     * @var EventManager
     */
    protected $eventManager;

    /**
     * @var Response
     */
    protected $response;


    /**
     * @var Core
     */
    protected $core;

    protected $responseOption = array(
        Response::FORMAT_HTML=>array(
            'className'=>'\\Core\\Response\\HTMLResponse',
            'template'=>'site',
            'layout'=>'index'
        ),
        Response::FORMAT_JSON=>array(
            'className'=>'\\Core\\Response\\JSONResponse',
        ),
    );



    /**
     * @var string
     */
    protected $responseFormat;


    public function __construct($name, \Autoloader $autoloader){
        ini_set('display_errors', 1);

        $this->setName($name);
        $this->setAutoLoader($autoloader);

        $this->config = new Config(QS_path(array('Configs','config.ini'), false));

        Debugger::init(Debugger::ALL, QS_path(array('logs'), false, true));

        $modulesFilePath = QS_path(array('Modules', 'modules.xml'), false);
        $modulesAutoloadPath = QS_path(array('Modules', 'autoload.xml'), false);
        $modulesDir = QS_path(array('Modules'), false);
        $this->moduleManager = new ModuleManager($modulesFilePath, $modulesAutoloadPath, $modulesDir, $this->config);

        $this->core = new Core($this->config, $this);
        $this->moduleManager->addModule('Core', $this->core);
        //$this->core->getCache()->clean();
        Mailer::setDefaultSMTPOption('Host', $this->config->get('smtp.Host'));
        Mailer::setDefaultSMTPOption('Secure', $this->config->get('smtp.Secure'));
        Mailer::setDefaultSMTPOption('Port', $this->config->get('smtp.Port', null, 'int'));


        $this->eventManager = $this->core->getEventManager();
        $this->hookEvents();

        $this->router = $this->core->getRouter();


        //$this->buildResponse($this->getResponseFormat());

        $this->moduleManager->startAutoload($this->getResponseFormat());
        $this->startSession();
        //---------------------------------------------

        //Событие загрузки приложения. Используется для установки текущего пользователя
        $event = $this->eventManager->event('Application.Load');
        $event->set('application', $this)->set('microtime', microtime(true));
        $event->fire();

    }

    /**
     * @return \Autoloader
     */
    public function getAutoLoader(){
        return $this->autoLoader;
    }

    /**
     * @param \Autoloader $autoloader
     */
    public function setAutoLoader(\Autoloader $autoloader){
        $this->autoLoader = $autoloader;
    }

    /**
     * @return string
     */
    public function getName(){
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name){
        $this->name = strval($name);
    }

    protected function startSession(){
        $session = $this->core->getSession();
        $session->setName(ucfirst($this->name));
        //Событие начала сессии. Используется для установки хранилища сессии
        $event = $this->eventManager->event('Application.StartSession');
        //$event = $this->eventManager->event('Session.start');
        $event->set('session', $session)->preFire();
        if ($event->isHandled()){
            $this->close('Start of session is blocked.');
        }
        $session->start();
        $event->postFire();
    }

    protected function hookEvents(){}

    public function getResponseFormat(){
        if (is_null($this->responseFormat)){
            $this->responseFormat = strtoupper($this->core->getInput()->request('_format', Response::FORMAT_HTML));
        }
        return $this->responseFormat;
    }

    public function setResponseFormat($format){
        $this->responseFormat = $format;
        $this->buildResponse($this->responseFormat);
    }

    protected function buildResponse($format){
        $event = $this->eventManager->event('Application.buildResponse');
        if (!$event->preFire()){
            throw new \RuntimeException(__CLASS__.': Blocked response building');
        }
        $supportedFormats = array(Response::FORMAT_HTML, Response::FORMAT_JSON, Response::FORMAT_PDF);
        if (!in_array($format, $supportedFormats)){
            throw new \Exception(__CLASS__.': Response format: '.$format.' doesn\'t support');
        }
        $className = '\\Core\\Response\\'.$format.'Response';
        if (!class_exists($className)){
            throw new \Exception(__CLASS__.': Response class: '.$className. ' doesn\'t exists');
        }
        switch ($format){
            case Response::FORMAT_HTML:
                $this->response = new HTMLResponse($this->responseOption[Response::FORMAT_HTML]['template'], $this->responseOption[Response::FORMAT_HTML]['layout']);
                break;
            case Response::FORMAT_JSON:
                $this->response = new JSONResponse();
                $this->response->set('status', 'OK');
                break;
            case Response::FORMAT_PDF:
                $this->response = new PDFResponse();
                break;
            default:
                http_response_code(415);
                throw new \Exception(__CLASS__.': Response format: '.$format.' doesn\'t support');
                break;
        }
        $this->response->setCore($this->core);
        $this->moduleManager->addModule('Core/Response', $this->response);
        $event->set('response', $this->response);
        $event->postFire();

    }

    public function init(){
        $event = $this->eventManager->event('Application.Init');
        $event->set('init_time', microtime(true))->set('application', $this);
        $event->preFire();
        if ($event->isHandled()){
            $this->response->error(500, 'Internal error');
            $this->render();
            $this->close();
        }
        $event->postFire();
    }

    public function route(){
        $this->buildResponse($this->getResponseFormat());
        $event = $this->eventManager->event('Application.Route');
        $requestURL = $this->core->getInput()->request('_request', null, TYPE_STRING);
        $event->set('request_url', $requestURL)->set('application', $this);
        $event->preFire();
        if ($event->isHandled()){
            $this->close('Routing fail');
        }
        if (($route = $this->router->matchCurrentRequest($_REQUEST['_request'])) === false){
            $this->response->error(404, 'Not found');

        }else{
            $event->set('route', $route);
            $event->postFire();
            $configs = $route->getConfig();
            /**
             * @var Module\Base\Module $module
             */
            $module = $this->moduleManager->getModule($configs['module']);
            $module->setResponse($this->response);
            /**
             * @var Module\Base\Controller $controller
             */
            $controller = $module->controller($configs['controller']);
            $controller->execute($configs['action'], $route->getParameters());
        }
    }

    public function __destruct(){
        $event = $this->eventManager->event('Application.Close');
        $event->set('application', $this)->set('microtime', microtime(true));
        $event->fire();
    }

    public function render(){
        ob_start();
        echo $this->response->render();
        ob_end_flush();
    }

    /**
     * Закрывает приложение
     * @param int|string $msg
     */
    public function close($msg = 0){
        exit($msg);
    }

    /**
     * @return ModuleManager
     */
    public function getModuleManager(){
        return $this->moduleManager;
    }

    /**
     * @return mixed|null
     * @throws Session\Exception\StateError
     */
    protected function getCurrentUser(){
        return $this->core->getSession()->get(\Modules\Users\Users::CURRENT_USER_KEY);
    }

    /**
     * @return Response
     */
    public function getResponse(){
        return $this->response;
    }
} 