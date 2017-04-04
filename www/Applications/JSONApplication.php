<?php
namespace Applications;

use Core\Application;
use Core\Event\EventVar;
use Core\Response\JSONResponse;
use Core\Response\Response;

class JSONApplication extends Application
{

    /**
     * @var JSONResponse
     */
    protected $response;

    /**
     * @var string
     */
    protected $controllerPrefix = '';

    protected $responseFormat = Response::FORMAT_JSON;


    /**
     * @return string
     */
    public function getControllerPrefix(){
        return $this->controllerPrefix;
    }

    /**
     * @param string $controllerPrefix
     */
    public function setControllerPrefix($controllerPrefix){
        $this->controllerPrefix = $controllerPrefix;
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
        $input = $this->core->getInput();

        $module = trim($input->get('module', null, TYPE_STRING));
        $controller = trim($input->get('controller', null, TYPE_STRING));
        $action = trim($input->get('action', null, TYPE_STRING));
        $module = $this->moduleManager->getModule($module);
        $module->setResponse($this->response);
        $controller = $module->controller($this->getControllerPrefix().$controller);

        $controller->execute($action);
        $event->postFire();

    }
}