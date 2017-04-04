<?php
namespace Modules\Restful\Views\Admin;


use Core\Module\Base\View;

class EntitiesList extends View{

    protected function renderJSON(array $data){
        $this->response->set('data', $this->entitiesToArray($data));
    }
} 