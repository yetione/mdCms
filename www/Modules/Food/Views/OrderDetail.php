<?php
namespace Modules\Food\Views;


use Core\DataBase\Model\Entity;
use Core\Module\Base\View;
use Core\Response\HTMLResponse;

class OrderDetail extends View
{
    /**
     * @var HTMLResponse
     */
    protected $response;

    protected function renderHTML(Entity $order){
        $this->response->setLayout('order_detail');

        $orderDays = $order->getProducts();
        $oDResult = [];
        $products = [];
        foreach ($orderDays as $oD){
            $tmp = $oD->toArray();
            $tmp['Products'] = [];
            foreach ($oD->getProducts() as $oDP){
                $tmp['Products'][] = $oDP->toArray();
                $p = $oDP->getProduct();
                if (!isset($products[$p->getId()])){
                    $products[$p->getId()] = $p->toArray();
                }
            }
            $oDResult[] = $tmp;
        }

        $block = $this->response->createBlock('OrderEntity',  $this->response->getFilePath('blocks/js_variable.php'));
        $block->set('name', 'OrderEntity')->set('value', json_encode($order->toArray()));

        /*$block = $this->response->createBlock('OrderDaysEntity',  $this->response->getFilePath('blocks/js_variable.php'));
        $block->set('name', 'OrderDaysEntity')->set('value', json_encode($oDResult));*/

        /*$block = $this->response->createBlock('ProductsEntity',  $this->response->getFilePath('blocks/js_variable.php'));
        $block->set('name', 'ProductsEntity')->set('value', json_encode(array_values($products)));*/




    }

}