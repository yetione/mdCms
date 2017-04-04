<?php
namespace Modules\Restful\Views;


use Core\Module\Base\View;
use Core\Response\JSONResponse;

class Entity extends View{

    /**
     * @var JSONResponse
     */
    protected $response;

    protected function renderJSON(\Core\DataBase\Model\Entity $item){
        $this->response->set('data', $item->toArray());
    }
} 