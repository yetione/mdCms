<?php
namespace Modules\Users\Views;


use Core\Module\Base\View;
use Core\Response\JSONResponse;

class Simple extends View{

    /**
     * @var JSONResponse
     */
    protected $response;



    protected function renderJSON($value){
        $this->response->set('data', $value);
    }
} 