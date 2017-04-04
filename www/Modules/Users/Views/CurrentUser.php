<?php
namespace Modules\Users\Views;


use Core\Module\Base\View;

class CurrentUser extends View{

    protected function renderJSON($data){
        $this->response->set('data', $data);
    }

} 