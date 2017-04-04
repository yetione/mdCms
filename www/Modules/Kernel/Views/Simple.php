<?php
namespace Modules\Kernel\Views;


use Core\Module\Base\View;

class Simple extends View{

    protected function renderJSON($data){
        $this->response->set('data', $data);
    }

}