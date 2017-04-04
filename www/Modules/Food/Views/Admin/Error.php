<?php
namespace Modules\Food\Views\Admin;


use Core\Module\Base\View;
use Core\Response\JSONResponse;

class Error extends View{
    /**
     * @var JSONResponse
     */
    protected $response;

    protected function renderJSON(array $errorData=[]){
        $this->response->set('status', 'error');
        $this->response->set('error', $errorData);
    }
} 