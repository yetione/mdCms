<?php
namespace Modules\Food\Views\Admin;


use Core\Module\Base\View;

class ProductSave extends View{

    protected function renderJSON($product, $result){
        $this->response->set('content', $product);
        $this->response->set('result', $result === false ? false : $result->toArray());
    }

} 