<?php
namespace Modules\Food\Views\Admin;


use Core\Module\Base\View;

class CitiesList extends View{
    /**
     * @var \Core\Response\JSONResponse
     */
    protected $response;

    /**
     * @var \Modules\Food\Food
     */
    protected $module;

    protected function renderJSON($categories){
        $input = $this->getInput();
        if ($input->get('layout', null, TYPE_STRING) === 'centerColumn'){
            $this->response->set('tabs', ['items'=>$this->module->getModuleTabs(), 'activeTab'=>0]);
            $content = [
                'templateUrl'=>TEMPLATES_PATH.'admin/templates/shop/cities.list.center.html',
                'data'=>['cities'=>$this->entitiesToArray($categories)]
            ];
            $this->response->set('content', $content);
        }else{
            $this->response->set('items', $this->entitiesToArray($categories));
            //$this->response->set('current', $this->module->getCityData()->toArray());
        }

    }
} 