<?php
namespace Modules\Food\Views\Admin;


use Core\Module\Base\View;

class MenuToDate extends View{

    protected function renderJSON($date, $items){
        $this->response->set('items', $this->entitiesToArray($items));
    }
} 