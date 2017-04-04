<?php
namespace Modules\Kernel\Views;


use Core\Module\Base\View;

class Error extends View{

    protected function renderJSON($data){
        $this->response->set('status', 'error');
        $this->response->set('data', $data);
    }

}