<?php
namespace Modules\Food\Views\Admin;


use Core\Module\Base\View;

class ProductUpdateImage extends View{

    protected function renderJSON($imageUrl){
        $this->response->set('url', $imageUrl);
    }
} 