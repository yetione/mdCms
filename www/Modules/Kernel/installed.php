<?php
use Core\Router\Route;

function Kernel_install(\Core\Core $core, \Core\Module\ModuleManager $moduleManager){
    $builder = $core->getEntityManager()->getDatabaseBuilder();

    //EntityData
    $ent = $builder->createEntity('entity_data');
    $ent->addField('id')->addField('name')->addField('fields')->addField('relationships')->addField('table_name')->addField('description')
        ->setDescription('Сущность содержащая данные о установленных сущностях')
        ->setTruncateOnCreate(true)
        ->addAccessFlag(\Modules\Kernel\Kernel::ENTITY_GET, 'a')
        ->addAccessFlag(\Modules\Kernel\Kernel::ENTITY_DELETE, 'a')
        ->addAccessFlag(\Modules\Kernel\Kernel::ENTITY_UPDATE, 'a');
    //---------

    //EntityAccessFlag
    $ent = $builder->createEntity('entity_access_flag');
    $ent->addField('id')->addField('entity_id', null, TYPE_INT)->addField('action')->addField('flag_id', null, TYPE_INT)
        ->setTruncateOnCreate(true)
        ->addAccessFlag(\Modules\Kernel\Kernel::ENTITY_GET, 'a')
        ->addAccessFlag(\Modules\Kernel\Kernel::ENTITY_DELETE, 'a')
        ->addAccessFlag(\Modules\Kernel\Kernel::ENTITY_UPDATE, 'a');

    $rel = $ent->createRelationship('Entity');
    $rel->setParentEntity('EntityAccessFlag');
    $rel->addParentColumn('entity_id');
    $rel->setForeignEntity('EntityData');
    $rel->addForeignColumn('id');
    $rel->setType(\Core\DataBase\Model\Relationship::MANY_ONE);
    $rel->setLoadOnFind(false);

    $rel = $ent->createRelationship('Flag');
    $rel->setParentEntity('EntityAccessFlag');
    $rel->addParentColumn('flag_id');
    $rel->setForeignEntity('AccessFlag');
    $rel->addForeignColumn('id');
    $rel->setType(\Core\DataBase\Model\Relationship::MANY_ONE);
    $rel->setLoadOnFind(false);
    //---------

    //Route
    $ent = $builder->createEntity('route');
    $ent->addField('id')->addField('url')->addField('data', '')
        ->addAccessFlag(\Modules\Kernel\Kernel::ENTITY_GET, 'a')
        ->addAccessFlag(\Modules\Kernel\Kernel::ENTITY_DELETE, 'a')
        ->addAccessFlag(\Modules\Kernel\Kernel::ENTITY_UPDATE, 'a')
        ->setTruncateOnCreate(true);
    //---------


}