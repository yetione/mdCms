<?php
namespace Modules\Food\Views;


use Core\Module\Base\View;
use Core\Response\HTMLResponse;

class Page extends View
{
    /**
     * @var HTMLResponse
     */
    protected $response;

    protected function renderHTML($layout){
        $this->response->setLayout($layout);
    }
}