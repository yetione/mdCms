<?php
namespace Modules\Food\Views\Admin;


use Core\Module\Base\View;

class ProductTypes extends View{

    protected function renderJSON($productTypes){
        $this->response->set('types', $this->entitiesToArray($productTypes));
    }
} 