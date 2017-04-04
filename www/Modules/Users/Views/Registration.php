<?php
namespace Modules\Users\Views;


use Core\Module\Base\View;
use Core\Response\HTMLResponse;

class Registration extends View{

    /**
     * @var HTMLResponse
     */
    protected $response;

    protected function renderHTML($layout='registr'){
        $this->response->setLayout($layout);
    }

} 