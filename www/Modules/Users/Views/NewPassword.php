<?php
namespace Modules\Users\Views;


use Core\Module\Base\View;

class NewPassword extends View {


    public function renderHTML($token){
        $block = $this->response->createBlock('token', $this->response->getFilePath('blocks/vk_login_link.php'));
        $block->set('link', $token);
        $this->response->setLayout('new_password');
    }
}