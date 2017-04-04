<?php
use Core\Router\Route;

function AdminPanel_install(\Core\Core $core){
    $router = $core->getRouter();

    $builder = $core->getEntityManager()->getDatabaseBuilder();
    $ent = $builder->createEntity('admin_menu');
    $ent->addField('id')->addField('name')->addField('icon')->addField('action')
        ->addField('title')->addField('order', 1, TYPE_INT)
        ->addAccessFlag(\Modules\Kernel\Kernel::ENTITY_GET, 'a')
        ->addAccessFlag(\Modules\Kernel\Kernel::ENTITY_UPDATE, 'a')
        ->addAccessFlag(\Modules\Kernel\Kernel::ENTITY_DELETE, 'a');
}