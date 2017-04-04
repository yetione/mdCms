<?php
require_once 'definitions.php';
$app = new \Applications\JSONApplication('SektaFood', $autoloader);
$app->init();
$app->route();
$app->render();