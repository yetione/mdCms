<?php

use Core\Router\Route;

function Users_install(\Core\Core $core, \Core\Module\ModuleManager $moduleManager){
    $router = $core->getRouter();

    $route = new Route('/login', array('module'=>'Users', 'controller'=>'Auth', 'action'=>'loginPage', 'methods'=>array('GET')));
    $router->add($route);

    $route = new Route('/login/sendpassword', array('module'=>'Users', 'controller'=>'Auth', 'action'=>'sendPassword', 'methods'=>array('GET')));
    $router->add($route);

    $route = new Route('/login/restore_password', array('module'=>'Users', 'controller'=>'Auth', 'action'=>'restorePassword', 'methods'=>array('POST')));
    $router->add($route);

    $route = new Route('/login/new_password', array('module'=>'Users', 'controller'=>'Auth', 'action'=>'newPassword', 'methods'=>array('GET')));
    $router->add($route);

    $route = new Route('/login/new_password/set', array('module'=>'Users', 'controller'=>'Auth', 'action'=>'setNewPassword', 'methods'=>array('POST')));
    $router->add($route);

    $route = new Route('/login/vk', array('module'=>'Users', 'controller'=>'Auth', 'action'=>'loginVKPage', 'methods'=>array('GET')));
    $router->add($route);

    $route = new Route('/auth/login', array('module'=>'Users', 'controller'=>'Auth', 'action'=>'checkLogin', 'methods'=>array('POST')));
    $router->add($route);

    $route = new Route('/auth/logout', array('module'=>'Users', 'controller'=>'Auth', 'action'=>'logout', 'methods'=>array('GET')));
    $router->add($route);

    $route = new Route('/registration', array('module'=>'Users', 'controller'=>'Auth', 'action'=>'registrationPage', 'methods'=>array('GET')));
    $router->add($route);

    $route = new Route('/auth/registration', array('module'=>'Users', 'controller'=>'Auth', 'action'=>'doRegistration', 'methods'=>array('POST')));
    $router->add($route);

    $route = new Route('/profile', array('module'=>'Users', 'controller'=>'Profile', 'action'=>'page', 'methods'=>array('GET')));
    $router->add($route);

    $route = new Route('/logout', array('module'=>'Users', 'controller'=>'Auth', 'action'=>'logout', 'methods'=>array('GET')));
    $router->add($route);

    $builder = $core->getEntityManager()->getDatabaseBuilder();

    //User
    $ent = $builder->createEntity('user');
    $ent->addField('id')->addField('login')->addField('email')->addField('password')
        ->addField('name')->addField('surname')->addField('patronymic')->addField('vk_id')
        ->addField('registr_date', null, TYPE_INT)->addField('is_admin', 0, TYPE_INT)->addField('phone')->addField('fb_id');
    $ent->setEntityClass('\\Modules\\Users\\Entities\\User');
    $ent->addAccessFlag(\Modules\Kernel\Kernel::ENTITY_GET, 'a')
        ->addAccessFlag(\Modules\Kernel\Kernel::ENTITY_DELETE, 'a')
        ->addAccessFlag(\Modules\Kernel\Kernel::ENTITY_UPDATE, 'a');
        //->setTruncateOnCreate(true);

    $rel = $ent->createRelationship('Flags');
    $rel->setForeignEntity('UserFlags');
    $rel->addForeignColumn('user_id');
    $rel->setParentEntity('User');
    $rel->addParentColumn('id');
    $rel->setType(\Core\DataBase\Model\Relationship::ONE_MANY);
    $rel->setJoinType(\Core\DataBase\Model\Relationship::LEFT_JOIN);

    $rel = $ent->createRelationship('Address');
    $rel->setForeignEntity('UserAddress');
    $rel->addForeignColumn('user_id');
    $rel->setParentEntity('User');
    $rel->addParentColumn('id');
    $rel->setType(\Core\DataBase\Model\Relationship::ONE_MANY);
    $rel->setJoinType(\Core\DataBase\Model\Relationship::LEFT_JOIN);
    //---------

    //UserFlags
    $ent = $builder->createEntity('user_flags');
    $ent->addField('id')->addField('user_id', null, TYPE_INT)->addField('flag_id', null, TYPE_INT)
        //->setTruncateOnCreate(true)
        ->addAccessFlag(\Modules\Kernel\Kernel::ENTITY_GET, 'a')
        ->addAccessFlag(\Modules\Kernel\Kernel::ENTITY_DELETE, 'a')
        ->addAccessFlag(\Modules\Kernel\Kernel::ENTITY_UPDATE, 'a');
    $rel = $ent->createRelationship('Flag');
    $rel->setForeignEntity('AccessFlag');
    $rel->addForeignColumn('id');
    $rel->setParentEntity('UserFlags');
    $rel->addParentColumn('flag_id');
    $rel->setType(\Core\DataBase\Model\Relationship::MANY_ONE);
    $rel->setJoinType(\Core\DataBase\Model\Relationship::LEFT_JOIN);
    $rel->setLoadOnFind(true);
    //---------

    //UserAddress
    $ent = $builder->createEntity('user_address');
    $ent->addField('id')->addField('user_id', null, TYPE_INT)->addField('city_id', null, TYPE_INT)
        ->addField('name')->addField('street', '')->addField('building', '')->addField('room', '')->addField('metro_station', '')
        //->setTruncateOnCreate(true)
        ->addAccessFlag(\Modules\Kernel\Kernel::ENTITY_GET, 'a')
        ->addAccessFlag(\Modules\Kernel\Kernel::ENTITY_DELETE, 'a')
        ->addAccessFlag(\Modules\Kernel\Kernel::ENTITY_UPDATE, 'a');

    $rel = $ent->createRelationship('User');
    $rel->setForeignEntity('User');
    $rel->addForeignColumn('id');
    $rel->setParentEntity('UserAddress');
    $rel->addParentColumn('user_id');
    $rel->setType(\Core\DataBase\Model\Relationship::MANY_ONE);
    $rel->setJoinType(\Core\DataBase\Model\Relationship::LEFT_JOIN);
    $rel->setLoadOnFind(false);

    $rel = $ent->createRelationship('City');
    $rel->setForeignEntity('City');
    $rel->addForeignColumn('id');
    $rel->setParentEntity('UserAddress');
    $rel->addParentColumn('city_id');
    $rel->setType(\Core\DataBase\Model\Relationship::MANY_ONE);
    $rel->setJoinType(\Core\DataBase\Model\Relationship::LEFT_JOIN);
    $rel->setLoadOnFind(true);
    //---------

    //AccessFlag
    $ent = $builder->createEntity('access_flag');
    $ent->addField('id')->addField('name')->addField('description')->addField('flag');
    $ent->addValue(['Name'=>'Root access', 'Description'=>'Access to all functions', 'Flag'=>'z'])
        ->addValue(['Name'=>'Admin flag', 'Description'=>'Access to admin panel', 'Flag'=>'a']);
    $ent->addAccessFlag(\Modules\Kernel\Kernel::ENTITY_GET, 'a')
        ->addAccessFlag(\Modules\Kernel\Kernel::ENTITY_DELETE, 'a')
        ->addAccessFlag(\Modules\Kernel\Kernel::ENTITY_UPDATE, 'a');
        //->setTruncateOnCreate(true);
    //---------

    //UserToken
    $ent = $builder->createEntity('user_token');
    $ent->addField('id')->addField('user_id', null, TYPE_INT)->addField('type')->addField('value')->addField('expire_time', null, TYPE_INT)
        //->setTruncateOnCreate(true)
        //->addAccessFlag(\Modules\Kernel\Kernel::ENTITY_GET, 'a')
        //->addAccessFlag(\Modules\Kernel\Kernel::ENTITY_DELETE, 'a')
        ->addAccessFlag(\Modules\Kernel\Kernel::ENTITY_UPDATE, 'a');

    $rel = $ent->createRelationship('User');
    $rel->setForeignEntity('User');
    $rel->addForeignColumn('id');
    $rel->setParentEntity('UserAddress');
    $rel->addParentColumn('user_id');
    $rel->setType(\Core\DataBase\Model\Relationship::MANY_ONE);
    $rel->setOnParentDelete(\Core\DataBase\Model\Relationship::BEHAVIOUR_NOTHING);
    $rel->setJoinType(\Core\DataBase\Model\Relationship::LEFT_JOIN);
    $rel->setLoadOnFind(false);
    //---------
}