<?php
namespace Modules\Food\Views\Admin;


use Core\Module\Base\View;

class CategoryItem extends View {

    /**
     * @var \Core\Response\JSONResponse
     */
    protected $response;

    /**
     * @var \Modules\Food\Food
     */
    protected $module;

    protected function renderJSON($category, $products, $productsType){
        $input = $this->getInput();
        if ($input->get('layout', null, TYPE_STRING) === 'rightColumn'){
            $content = [
                'templateUrl'=>TEMPLATES_PATH.'admin/templates/shop/category.item.right.html',
                'data'=>['category'=>$category->toArray(), 'products'=>$this->entitiesToArray($products), 'productsType'=>$this->entitiesToArray($productsType)]
            ];
            $this->response->set('content', $content);
        }else{
            $data = ['products'=>$this->entitiesToArray($products), 'category'=>$category->toArray()];
            $this->response->set('data', $data);
        }
    }

} 