<?php
namespace Modules\Food\Controllers;


use Core\DataBase\Exception\StatementExecuteError;
use Core\Debugger;
use Core\Exception\AccessDenied;
use Core\Module\Base\Controller;
use Modules\Users\Users;

class Index extends Controller{

    public function indexPage(array $data){
        $this->module->getResponse()->setTitle('Главная');
    }

    public function cartPage(array $data){
        $this->module->getResponse()->addHeader('Access-Control-Allow-Origin: '.$_SERVER['HTTP_ORIGIN']);
        $this->module->getResponse()->setTitle('Корзина');
        $view = $this->module->view('Cart');
        $view->render();
    }

    public function cartNewPage(array $data){
        $this->module->getResponse()->addHeader('Access-Control-Allow-Origin: '.$_SERVER['HTTP_ORIGIN']);
        $this->module->getResponse()->setTitle('NEW Корзина');
        $view = $this->module->view('Page');
        $view->render('cart_new');
    }

    public function offerPage(array $data){
        $this->module->getResponse()->setTitle('Оферта');
        $view = $this->module->view('Offer');
        $view->render();
    }

    public function requisitesPage(array $data){
        $this->module->getResponse()->setTitle('Реквизиты');
        $view = $this->module->view('Page');
        $view->render('requisites');
    }

    public function deliveryPage(array $data){
        $this->module->getResponse()->setTitle('Доставка и оплата');
        $view = $this->module->view('Page');
        $view->render('delivery');
    }

    public function contactsPage(array $data){
        $this->module->getResponse()->setTitle('Контакты');
        $view = $this->module->view('Page');
        $view->render('contacts');
    }

    public function orderPage(array $data){
        $orderId = (int) $data['id'];
        $cU = $this->module->getCore()->getSession()->get(Users::CURRENT_USER_KEY);
        $em = $this->getEntityManager();
        $query = $em->getEntityQuery('Order');
        try{
            $order = $query->findById($orderId)->loadOne(true, true);
            if (!$order){
                throw new StatementExecuteError(['message'=>'Заказ не найден', 'code'=>1]);
            }
            if (!(int) $cU->getId()){
                throw new AccessDenied('User not login.', 2);
            }
            if ($cU->getId() != $order->getUserId()){
                throw new AccessDenied('User has not access.', 3);
            }
            $this->module->getResponse()->setTitle('Информация о заказе');
            $view = $this->module->view('OrderDetail');
            $view->render($order);

        }catch (StatementExecuteError $e){
            Debugger::log('Food\\Controllers\\Index\\orderPage::statement error');
            $this->module->getResponse()->redirect('order/error?c=1');
            return;
        }catch (AccessDenied $e){
            Debugger::log('Food\\Controllers\\Index\\orderPage::Access denied: '.$e->getMessage());
            $this->module->getResponse()->redirect('order/error?c='.$e->getCode());
            return;
        }

    }
} 