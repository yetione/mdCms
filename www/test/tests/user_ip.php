<?php
$core = $app->getCore();
$user = $core->getSession()->get(\Modules\Users\Users::CURRENT_USER_KEY);
var_dump($core->getInput()->getIp(), $user);