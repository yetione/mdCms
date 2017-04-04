<?php
namespace Modules\Food\Views\Admin;


use Core\Module\Base\View;

class ProductDelete extends View{

    protected function renderJSON($result){
        $this->response->set('result', $result);
    }
} 