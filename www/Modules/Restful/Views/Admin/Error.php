<?php
namespace Modules\Restful\Views\Admin;


use Core\Module\Base\View;

class Error extends View{

    protected function renderJSON($errorMessage, $errorCode=0){
        $this->response->set('status', 'error');
        $this->response->set('error', [
            'code'=>$errorCode,
            'message'=>$errorMessage
        ]);
    }
} 