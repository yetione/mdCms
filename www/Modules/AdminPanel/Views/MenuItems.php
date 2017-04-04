<?php
namespace Modules\AdminPanel\Views;


use Core\Module\Base\View;

class MenuItems extends View{

    /**
     * @var \Core\Response\JSONResponse
     */
    protected $response;

    protected function renderJSON($items){
        $this->response->set('data', $this->entitiesToArray($items));
    }
} 