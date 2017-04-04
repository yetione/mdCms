<?php
namespace Modules\Restful\Views;


use Core\Module\Base\View;
use Core\Response\JSONResponse;

class ItemSave extends View{

    /**
     * @var JSONResponse
     */
    protected $response;

    protected function renderJSON($item, $result){
        $this->response->set('data', $item);
        $this->response->set('result', $result === false ? false : $result->toArray());
    }
} 