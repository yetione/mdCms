<?php
namespace Modules\Food\Views;


use Core\Module\Base\View;

class Offer extends View{
    /**
     * @var HTMLResponse
     */
    protected $response;

    protected function renderHTML(){
        $this->response->setLayout('offer');
    }
}