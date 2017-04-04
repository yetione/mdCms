<?php
namespace Modules\Restful\Views;


use Core\Module\Base\View;
use Core\Response\JSONResponse;

class ItemDelete extends View{

    /**
     * @var JSONResponse
     */
    protected $response;

    protected function renderJSON($result){
        $this->response->set('result', $result);
    }
} 