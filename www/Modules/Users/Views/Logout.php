<?php
namespace Modules\Users\Views;


use Core\Module\Base\View;
use Core\Response\HTMLResponse;
use Core\Response\JSONResponse;
use Modules\Users\Users;

class Logout extends View {

    /**
     * @param int $result
     */
    protected function renderHTML($result){
        /**
         * @var HTMLResponse $response;
         */
        $response = $this->response;
        $response->setTitle('Регистрация');
        $response->setLayout('logout');
        if ($result === Users::SUCCESS){
            $this->response->redirect(BASE_URL);
        }else{
            $this->response->redirect('logout?err='.$result);
        }
    }

    /**
     * @param int $result
     */
    protected function renderJSON($result){
        /**
         * @var JSONResponse $response;
         */
        $response = $this->response;
        $response->set('status', $result===Users::SUCCESS ? 'OK' : 'error');
        $response->set('data', ['code'=>$result, 'redirect_url'=>BASE_URL]);
    }
}