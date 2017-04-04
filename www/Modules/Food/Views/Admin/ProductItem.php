<?php
namespace Modules\Food\Views\Admin;


use Core\Module\Base\View;

class ProductItem extends View{

    protected function renderJSON($product, $productsType){
        $input = $this->getInput();
        if ($input->get('layout', null, TYPE_STRING) === 'rightColumn'){
            $content = [
                'templateUrl'=>TEMPLATES_PATH.'admin/templates/shop/product.item.right.html',
                'data'=>['product'=>$product->toArray(), 'productsType'=>$this->entitiesToArray($productsType)]
            ];
            $this->response->set('content', $content);
        }else{
            $data = [
                'product'=>$product->toArray(),
                'productsType'=>$this->entitiesToArray($productsType)
            ];
            $this->response->set('data', $data);
        }
    }
} 