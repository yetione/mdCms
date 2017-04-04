<?php
require_once '../definitions.php';
$app = new \Applications\AdminApplication('SektaFood', $autoloader);
$app->init();
$app->route();
$app->render();