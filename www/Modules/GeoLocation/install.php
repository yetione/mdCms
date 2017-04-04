<?php
use Core\Router\Route;

function GeoLocation_install(\Core\Core $core, \Core\Module\ModuleManager $moduleManager){

    $router = $core->getRouter();

    $route = new Route('/geo-location/change-city/:cityId', array('module'=>'GeoLocation', 'controller'=>'Index', 'action'=>'setCity', 'methods'=>array('GET')));
    $router->add($route);

    $builder = $core->getEntityManager()->getDatabaseBuilder();

    //City
    $ent = $builder->createEntity('city');
    $ent->addField('id')->addField('name')->addField('short')->addField('machine')->addField('okato')->addField('is_active', 1, TYPE_INT);
    //---------
}