<?php
namespace Modules\Food\Views\Admin;


use Core\DataBase\Model\Entity;
use Core\Module\Base\View;
use Core\Response\JSONResponse;

class Item extends View{

    /**
     * @var JSONResponse
     */
    protected $response;

    protected function renderJSON(Entity $item){
        $this->response->set('data', $item->toArray());
    }
} 