<?php
namespace Modules\Restful\Views;


use Core\Module\Base\View;

class AccessDenied extends View{

    protected function renderJSON(){
        $this->response->set('status', 'error');
        $this->response->set('message', ['code'=>403,'message'=>'Access Denied']);
    }
} 