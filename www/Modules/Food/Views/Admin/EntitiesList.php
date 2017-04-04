<?php
namespace Modules\Food\Views\Admin;


use Core\Module\Base\View;
use Core\Response\JSONResponse;

class EntitiesList extends View{
    /**
     * @var JSONResponse
     */
    protected $response;

    protected function renderJSON($entitiesList){
        $this->response->set('data', $this->entitiesToArray($entitiesList));
    }
} 