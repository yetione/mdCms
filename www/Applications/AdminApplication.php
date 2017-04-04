<?php
namespace Applications;


use Core\Application;
use Core\Response\HTMLResponse;
use Core\Response\Response;

class AdminApplication extends Application{
    protected $responseOption = array(
        Response::FORMAT_HTML=>array(
            'className'=>'\\Core\\Response\\HTMLResponse',
            'template'=>'admin',
            'layout'=>'index'
        ),
        Response::FORMAT_JSON=>array(
            'className'=>'\\Core\\Response\\JSONResponse',
        ),
    );

    protected $responseFormat = Response::FORMAT_HTML;

    /**
     * @var HTMLResponse
     */
    protected $response;

    public function route(){
        $this->moduleManager->getModule('AdminPanel');
        $this->buildResponse($this->getResponseFormat());
        $event = $this->eventManager->event('Application.Route');
        $event->set('request_url', $_REQUEST['_request'])->set('application', $this);
        $event->preFire();
        if ($event->isHandled()){
            $this->close('Routing fail');
        }
        $event->postFire();

    }

    public function render(){
        if (!$this->getCurrentUser()->hasFlag('a')){
            $this->response->setLayout('login');
        }
        echo $this->response->render();
    }
} 