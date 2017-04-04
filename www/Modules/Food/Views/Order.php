<?php
namespace Modules\Food\Views;


use Core\Module\Base\View;

class Order extends View{


    protected function renderJSON($order){
        $this->response->set('data', $order->toArray());
    }
} 