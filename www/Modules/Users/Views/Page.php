<?php
namespace Modules\Users\Views;


use Core\Module\Base\View;

class Page extends View{


    public function renderHTML($title, $content){
        $block = $this->response->createBlock('pageTitle', $this->response->getFilePath('blocks/vk_login_link.php'));
        $block->set('link', $title);

        $block = $this->response->createBlock('pageContent', $this->response->getFilePath('blocks/vk_login_link.php'));
        $block->set('link', $content);
        $this->response->setLayout('page');
    }
}