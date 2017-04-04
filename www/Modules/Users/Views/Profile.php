<?php
/**
 * Created by PhpStorm.
 * User: yeti
 * Date: 18.09.2016
 * Time: 9:04
 */

namespace Modules\Users\Views;


use Core\Module\Base\View;

class Profile extends View{

    protected function renderHTML(){
        $this->response->setLayout('profile');
    }
} 