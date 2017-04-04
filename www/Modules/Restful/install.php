<?php
use Core\Router\Route;

function Restful_install(\Core\Core $core, \Core\Module\ModuleManager $moduleManager){
    $router = $core->getRouter();

    $route = new Route('/data/:entity.json', array('module'=>'Restful', 'controller'=>'Data', 'action'=>'data', 'methods'=>array('GET')));
    $router->add($route);
}