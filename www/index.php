<?php
require_once 'definitions.php';
$meter = new \Core\Meter('Время работы приложения. Запрос: '.$_REQUEST['_request']);
$meter->dir(array('logs','app_work_time'))->run();
$app = new \Applications\SiteApplication('SektaFood', $autoloader);
$app->init();
$app->route();
$app->render();

$meter->end();

