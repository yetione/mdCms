<?php
namespace Modules\Food\Views;


use Core\Module\Base\View;
use Core\Response\HTMLResponse;

class Cart extends View{

    /**
     * @var HTMLResponse
     */
    protected $response;

    protected function renderHTML(){
        $this->response->setLayout('cart');
    }

    protected function renderJSON(\Modules\Food\Classes\Cart $cart){
        $this->response->set('data', $cart->toArray());
    }
} 