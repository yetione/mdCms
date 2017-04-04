<?php
namespace Modules\Users\Views;

use Core\Module\Base\View;

class Login extends View{

    /**
     * @param string $vkLoginLink
     */
    protected function renderHTML($vkLoginLink){
        $block = $this->response->createBlock('vkLoginLink', $this->response->getFilePath('blocks/vk_login_link.php'));
        $block->set('link', $vkLoginLink);
        $this->response->setLayout('login');
    }
} 