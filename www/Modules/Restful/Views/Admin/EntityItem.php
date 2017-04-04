<?php
namespace Modules\Restful\Views\Admin;


use Core\DataBase\Model\Entity;
use Core\Module\Base\View;
use Core\Response\JSONResponse;

class EntityItem extends View{

    /**
     * @var JSONResponse
     */
    protected $response;

    protected function renderJSON(Entity $data){
        $this->response->set('data', is_null($data) ? null : $data->toArray());
    }
} 