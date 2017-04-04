<?php
namespace Applications;


use Core\Application;
use Core\Core;
use Core\Response\Response;

class TestApplication extends Application{
    protected $responseOption = array(
        Response::FORMAT_HTML=>array(
            'className'=>'\\Core\\Response\\HTMLResponse',
            'template'=>'test',
            'layout'=>'index'
        ),
        Response::FORMAT_JSON=>array(
            'className'=>'\\Core\\Response\\JSONResponse',
        ),
    );


    protected $getVars = [
        'module'=>'module',
        'controller'=>'controllers',
        'action'=>'action',
        'install_module'=>'im'
    ];

    /**
     * @return Core
     */
    public function getCore(){
        return $this->core;
    }

    public function route(){
        $this->buildResponse($this->getResponseFormat());
        $event = $this->eventManager->event('Application.Route');
        $event->set('request_url', $_REQUEST['_request'])->set('application', $this);
        $event->preFire();
        if ($event->isHandled()){
            $this->close('Routing fail');
        }
        $input = $this->core->getInput();

        $module = $input->get($this->getVars['module'], null, TYPE_STRING);
        $controller = $input->get($this->getVars['controller'], null, TYPE_STRING);
        $action = $input->get($this->getVars['action'], null, TYPE_STRING);

        if (!is_null($module) && !is_null($controller) && !is_null($action)){
            $module = $this->moduleManager->getModule(trim($module));
            $module->setResponse($this->response);
            $controller = $module->controller(trim($controller));
            $controller->execute(trim($action));
        }
        $event->postFire();
    }



} 