<?php

use Core\Router\Route;

function Food_install(\Core\Core $core, \Core\Module\ModuleManager $moduleManager){
    $router = $core->getRouter();

    $route = new Route('/', array('module'=>'Food', 'controller'=>'Index', 'action'=>'indexPage', 'methods'=>array('GET')));
    $router->add($route);

    $route = new Route('/cart', array('module'=>'Food', 'controller'=>'Index', 'action'=>'cartPage', 'methods'=>array('GET')));
    $router->add($route);

    $route = new Route('/cart_new', array('module'=>'Food', 'controller'=>'Index', 'action'=>'cartNewPage', 'methods'=>array('GET')));
    $router->add($route);

    $route = new Route('/offer', array('module'=>'Food', 'controller'=>'Index', 'action'=>'offerPage', 'methods'=>array('GET')));
    $router->add($route);

    $route = new Route('/delivery', array('module'=>'Food', 'controller'=>'Index', 'action'=>'deliveryPage', 'methods'=>array('GET')));
    $router->add($route);

    $route = new Route('/requisites', array('module'=>'Food', 'controller'=>'Index', 'action'=>'requisitesPage', 'methods'=>array('GET')));
    $router->add($route);

    $route = new Route('/contacts', array('module'=>'Food', 'controller'=>'Index', 'action'=>'contactsPage', 'methods'=>array('GET')));
    $router->add($route);


    $route = new Route('/orders/:id', array('module'=>'Food', 'controller'=>'Index', 'action'=>'orderPage', 'methods'=>array('GET')));
    $router->add($route);


    $builder = $core->getEntityManager()->getDatabaseBuilder();
    //Category
    $ent = $builder->createEntity('category');
    $ent->addField('id')->addField('name')->addField('announce')
        ->addField('parent_id', 0, TYPE_INT)->addField('url', '')
        ->addField('weight', 0, TYPE_INT)->addField('public', 1, TYPE_INT);

    $rel = $ent->createRelationship('parent');
    $rel->setForeignEntity('Category');
    $rel->addForeignColumn('id');
    $rel->setParentEntity('Category');
    $rel->addParentColumn('parent_id');
    $rel->setLoadOnFind(false);
    $rel->setType(\Core\DataBase\Model\Relationship::ONE_MANY);
    $rel->setJoinType(\Core\DataBase\Model\Relationship::LEFT_JOIN);
    //---------

    //Product_type
    $ent = $builder->createEntity('product_type');
    $ent->addField('id')->addField('name')->addField('category_id', null, TYPE_INT);

    $rel = $ent->createRelationship('category');
    $rel->setForeignEntity('Category');
    $rel->addForeignColumn('id');
    $rel->setParentEntity('ProductType');
    $rel->addParentColumn('category_id');
    $rel->setLoadOnFind(false);
    $rel->setType(\Core\DataBase\Model\Relationship::MANY_ONE);
    $rel->setJoinType(\Core\DataBase\Model\Relationship::LEFT_JOIN);
    //---------

    //Product
    $ent = $builder->createEntity('product');
    $ent->addField('id')->addField('name')->addField('image')
        ->addField('price_spb', null, TYPE_FLOAT)->addField('price_msk', null, TYPE_FLOAT)
        ->addField('description', '')->addField('announce', '')
        ->addField('proteins', 0, TYPE_FLOAT)->addField('fats', 0, TYPE_FLOAT)
        ->addField('calorie', 0, TYPE_FLOAT)->addField('carbs', 0, TYPE_FLOAT)
        ->addField('weight', 0, TYPE_FLOAT)
        ->addField('type_id', null, TYPE_INT)->addField('category_id', null, TYPE_INT);
    $ent->setEntityClass('\\Modules\\Food\\Entities\\Product');

    $rel = $ent->createRelationship('Category');
    $rel->setForeignEntity('Category');
    $rel->addForeignColumn('id');
    $rel->setParentEntity('Product');
    $rel->addParentColumn('category_id');
    $rel->setType(\Core\DataBase\Model\Relationship::MANY_ONE);
    $rel->setJoinType(\Core\DataBase\Model\Relationship::LEFT_JOIN);

    $rel = $ent->createRelationship('Type');
    $rel->setForeignEntity('ProductType');
    $rel->addForeignColumn('id');
    $rel->setParentEntity('Product');
    $rel->addParentColumn('type_id');
    $rel->setType(\Core\DataBase\Model\Relationship::MANY_ONE);
    $rel->setJoinType(\Core\DataBase\Model\Relationship::LEFT_JOIN);
    //---------

    //Menu
    $ent = $builder->createEntity('menu');
    $ent->addField('id')->addField('date')->addField('data')
    /*->addField('city_id')*/
    ->addField('enabled', 1, TYPE_INT);

    //Order
    $ent = $builder->createEntity('order');
    $ent->addField('id')->addField('date_created')->addField('status')->addField('client_name')->addField('user_id', 0, TYPE_INT)->addField('city_id', null, TYPE_INT)
        ->addField('phone')->addField('email')->addField('price', 0, TYPE_FLOAT)->addField('promo_code_data')->addField('promo_code_name','')->addField('discount', '')
        ->addAccessFlag(\Modules\Kernel\Kernel::ENTITY_GET, 'a')
        ->addAccessFlag(\Modules\Kernel\Kernel::ENTITY_UPDATE, 'a')
        ->addAccessFlag(\Modules\Kernel\Kernel::ENTITY_DELETE, 'a');
    $ent->setEntityClass('\\Modules\\Food\\Entities\\Order');

    $rel = $ent->createRelationship('User');
    $rel->setForeignEntity('User');
    $rel->addForeignColumn('id');
    $rel->setParentEntity('Order');
    $rel->addParentColumn('user_id');
    $rel->setType(\Core\DataBase\Model\Relationship::MANY_ONE);
    $rel->setJoinType(\Core\DataBase\Model\Relationship::LEFT_JOIN);
    $rel->setLoadOnFind(false);

    $rel = $ent->createRelationship('Products');
    $rel->setForeignEntity('OrderDay');
    $rel->addForeignColumn('order_id');
    $rel->setParentEntity('Order');
    $rel->addParentColumn('id');
    $rel->setType(\Core\DataBase\Model\Relationship::ONE_MANY);
    $rel->setJoinType(\Core\DataBase\Model\Relationship::LEFT_JOIN);
    $rel->setLoadOnFind(false);

    $rel = $ent->createRelationship('City');
    $rel->setForeignEntity('City');
    $rel->addForeignColumn('id');
    $rel->setParentEntity('Order');
    $rel->addParentColumn('city_id');
    $rel->setType(\Core\DataBase\Model\Relationship::MANY_ONE);
    $rel->setJoinType(\Core\DataBase\Model\Relationship::INNER_JOIN);
    $rel->setLoadOnFind(false);
    //---------

    //OrderDay
    $ent = $builder->createEntity('order_day');
    $ent->addField('id')->addField('order_id', null, TYPE_INT)->addField('delivery_time', '')->addField('delivery_type')->addField('delivery_price', 0, TYPE_FLOAT)
        ->addField('street', '')->addField('building', '')->addField('room', '')->addField('client_comment', '')
        ->addField('manager_comment', '')->addField('delivery_date')->addField('payment_type')->addField('card_number', '')
        ->addField('price', 0, TYPE_FLOAT)->addField('is_changed', 0, TYPE_INT)->addField('changed_by', 0, TYPE_INT)->addField('status')
        ->addField('phone', '')->addField('persons_count', 1)->addField('metro_station', '')
        ->addField('courier_id', 0, TYPE_INT)->addField('city_id', null, TYPE_INT)->addField('stock_id', 0, TYPE_INT)
        ->addField('discount', '')->addField('discount_price', 0, TYPE_FLOAT)
        ->addAccessFlag(\Modules\Kernel\Kernel::ENTITY_GET, 'a')
        ->addAccessFlag(\Modules\Kernel\Kernel::ENTITY_UPDATE, 'a')
        ->addAccessFlag(\Modules\Kernel\Kernel::ENTITY_DELETE, 'a');
    $ent->setEntityClass('\\Modules\\Food\\Entities\\OrderDay');

    $rel = $ent->createRelationship('Order');
    $rel->setForeignEntity('Order');
    $rel->addForeignColumn('id');
    $rel->setParentEntity('OrderDay');
    $rel->addParentColumn('order_id');
    $rel->setType(\Core\DataBase\Model\Relationship::MANY_ONE);
    $rel->setJoinType(\Core\DataBase\Model\Relationship::INNER_JOIN);
    $rel->setLoadOnFind(false);

    $rel = $ent->createRelationship('Editor');
    $rel->setForeignEntity('User');
    $rel->addForeignColumn('id');
    $rel->setParentEntity('OrderDay');
    $rel->addParentColumn('changed_by');
    $rel->setType(\Core\DataBase\Model\Relationship::MANY_ONE);
    $rel->setJoinType(\Core\DataBase\Model\Relationship::LEFT_JOIN);
    $rel->setLoadOnFind(false);

    $rel = $ent->createRelationship('Products');
    $rel->setForeignEntity('OrderDayProduct');
    $rel->addForeignColumn('order_day_id');
    $rel->setParentEntity('OrderDay');
    $rel->addParentColumn('id');
    $rel->setType(\Core\DataBase\Model\Relationship::ONE_MANY);
    $rel->setJoinType(\Core\DataBase\Model\Relationship::LEFT_JOIN);
    $rel->setLoadOnFind(false);

    $rel = $ent->createRelationship('Courier');
    $rel->setForeignEntity('Courier');
    $rel->addForeignColumn('id');
    $rel->setParentEntity('OrderDay');
    $rel->addParentColumn('courier_id');
    $rel->setType(\Core\DataBase\Model\Relationship::MANY_ONE);
    $rel->setJoinType(\Core\DataBase\Model\Relationship::LEFT_JOIN);
    $rel->setLoadOnFind(false);

    $rel = $ent->createRelationship('City');
    $rel->setForeignEntity('City');
    $rel->addForeignColumn('id');
    $rel->setParentEntity('OrderDay');
    $rel->addParentColumn('city_id');
    $rel->setType(\Core\DataBase\Model\Relationship::MANY_ONE);
    $rel->setJoinType(\Core\DataBase\Model\Relationship::LEFT_JOIN);
    $rel->setLoadOnFind(false);

    $rel = $ent->createRelationship('Stock');
    $rel->setForeignEntity('Stock');
    $rel->addForeignColumn('id');
    $rel->setParentEntity('OrderDay');
    $rel->addParentColumn('stock_id');
    $rel->setType(\Core\DataBase\Model\Relationship::MANY_ONE);
    $rel->setJoinType(\Core\DataBase\Model\Relationship::LEFT_JOIN);
    $rel->setLoadOnFind(false);
    //---------

    //OrderDayProduct
    $ent = $builder->createEntity('order_day_product');
    $ent->addField('id')->addField('order_day_id', null, TYPE_INT)->addField('product_id', null, TYPE_INT)->addField('amount', null, TYPE_INT)->addField('price', null, TYPE_FLOAT)
        ->addAccessFlag(\Modules\Kernel\Kernel::ENTITY_GET, 'a')
        ->addAccessFlag(\Modules\Kernel\Kernel::ENTITY_UPDATE, 'a')
        ->addAccessFlag(\Modules\Kernel\Kernel::ENTITY_DELETE, 'a');
    $ent->setEntityClass('\\Modules\\Food\\Entities\\OrderDayProduct');

    $rel = $ent->createRelationship('OrderDay');
    $rel->setForeignEntity('OrderDay');
    $rel->addForeignColumn('id');
    $rel->setParentEntity('OrderDayProduct');
    $rel->addParentColumn('order_day_id');
    $rel->setType(\Core\DataBase\Model\Relationship::MANY_ONE);
    $rel->setJoinType(\Core\DataBase\Model\Relationship::INNER_JOIN);
    $rel->setLoadOnFind(false);

    $rel = $ent->createRelationship('Product');
    $rel->setForeignEntity('Product');
    $rel->addForeignColumn('id');
    $rel->setParentEntity('OrderDayProduct');
    $rel->addParentColumn('product_id');
    $rel->setType(\Core\DataBase\Model\Relationship::MANY_ONE);
    $rel->setJoinType(\Core\DataBase\Model\Relationship::INNER_JOIN);
    $rel->setLoadOnFind(false);
    //---------

    //Courier
    $ent = $builder->createEntity('courier');
    $ent->addField('id')->addField('name')->addField('phone')->addField('data')->addField('city_id', null, TYPE_INT)
        ->addAccessFlag(\Modules\Kernel\Kernel::ENTITY_GET, 'a')
        ->addAccessFlag(\Modules\Kernel\Kernel::ENTITY_UPDATE, 'a')
        ->addAccessFlag(\Modules\Kernel\Kernel::ENTITY_DELETE, 'a');

    $rel = $ent->createRelationship('City');
    $rel->setForeignEntity('City');
    $rel->addForeignColumn('id');
    $rel->setParentEntity('Courier');
    $rel->addParentColumn('city_id');
    $rel->setType(\Core\DataBase\Model\Relationship::MANY_ONE);
    $rel->setJoinType(\Core\DataBase\Model\Relationship::INNER_JOIN);
    $rel->setLoadOnFind(false);
    //---------

    //Itinerary Маршрутный лист
    $ent = $builder->createEntity('itinerary');
    $ent->addField('id')->addField('date')->addField('courier_id', null, TYPE_INT)->addField('city_id', null, TYPE_INT)->addField('name')->addField('data')
        ->addAccessFlag(\Modules\Kernel\Kernel::ENTITY_GET, 'a')
        ->addAccessFlag(\Modules\Kernel\Kernel::ENTITY_UPDATE, 'a')
        ->addAccessFlag(\Modules\Kernel\Kernel::ENTITY_DELETE, 'a');

    $rel = $ent->createRelationship('City');
    $rel->setForeignEntity('City');
    $rel->addForeignColumn('id');
    $rel->setParentEntity('Itinerary');
    $rel->addParentColumn('city_id');
    $rel->setType(\Core\DataBase\Model\Relationship::MANY_ONE);
    $rel->setJoinType(\Core\DataBase\Model\Relationship::INNER_JOIN);
    $rel->setLoadOnFind(false);

    $rel = $ent->createRelationship('Courier');
    $rel->setForeignEntity('Courier');
    $rel->addForeignColumn('id');
    $rel->setParentEntity('Itinerary');
    $rel->addParentColumn('courier_id');
    $rel->setType(\Core\DataBase\Model\Relationship::MANY_ONE);
    $rel->setJoinType(\Core\DataBase\Model\Relationship::INNER_JOIN);
    $rel->setLoadOnFind(false);
    //---------

    //PromoCode
    $ent = $builder->createEntity('promo_code');
    $ent->addField('id')->addField('code')->addField('description', '')->addField('expire_date', null, TYPE_INT)
        ->addField('data')->addField('start_date', null, TYPE_INT)->addField('type', 1, TYPE_INT)->addField('active', 1, TYPE_INT)->addField('used', 0, TYPE_INT)
        ->addAccessFlag(\Modules\Kernel\Kernel::ENTITY_UPDATE, 'a')
        ->addAccessFlag(\Modules\Kernel\Kernel::ENTITY_DELETE, 'a');
    //---------

    //Stock
    $ent = $builder->createEntity('stock');
    $ent->addField('id')->addField('name')->addField('city_id', null, TYPE_INT)->addField('metro_station', '')->addField('street', '')->addField('building', '')->addField('room', '')
        ->addAccessFlag(\Modules\Kernel\Kernel::ENTITY_UPDATE, 'a')
        ->addAccessFlag(\Modules\Kernel\Kernel::ENTITY_DELETE, 'a');

    $rel = $ent->createRelationship('City');
    $rel->setForeignEntity('City');
    $rel->addForeignColumn('id');
    $rel->setParentEntity('Stock');
    $rel->addParentColumn('city_id');
    $rel->setType(\Core\DataBase\Model\Relationship::MANY_ONE);
    $rel->setJoinType(\Core\DataBase\Model\Relationship::INNER_JOIN);
    $rel->setLoadOnFind(false);

    //---------

    //OrderDayNew
    $ent = $builder->createEntity('order_day_new');
    $ent->addField('id')->addField('order_id')->addField('city_id')->addField('update_by', 0)->addField('update_date')
        ->addField('order_date')->addField('status')->addField('price')->addField('client_comment')->addField('manager_comment')
        ->addField('shipping_type')->addField('shipping_data')
        ->addField('payment_type')->addField('payment_data')
        ->addAccessFlag(\Modules\Kernel\Kernel::ENTITY_GET, 'a')
        ->addAccessFlag(\Modules\Kernel\Kernel::ENTITY_UPDATE, 'a')
        ->addAccessFlag(\Modules\Kernel\Kernel::ENTITY_DELETE, 'a');

    $rel = $ent->createRelationship('Order');
    $rel->setForeignEntity('Order');
    $rel->addForeignColumn('id');
    $rel->setParentEntity('OrderDayNew');
    $rel->addParentColumn('order_id');
    $rel->setType(\Core\DataBase\Model\Relationship::MANY_ONE);
    $rel->setJoinType(\Core\DataBase\Model\Relationship::INNER_JOIN);
    $rel->setLoadOnFind(false);

    $rel = $ent->createRelationship('Editor');
    $rel->setForeignEntity('User');
    $rel->addForeignColumn('id');
    $rel->setParentEntity('OrderDayNew');
    $rel->addParentColumn('update_by');
    $rel->setType(\Core\DataBase\Model\Relationship::MANY_ONE);
    $rel->setJoinType(\Core\DataBase\Model\Relationship::LEFT_JOIN);
    $rel->setLoadOnFind(false);

    $rel = $ent->createRelationship('Products');
    $rel->setForeignEntity('OrderDayProduct');
    $rel->addForeignColumn('order_day_id');
    $rel->setParentEntity('OrderDayNew');
    $rel->addParentColumn('id');
    $rel->setType(\Core\DataBase\Model\Relationship::ONE_MANY);
    $rel->setJoinType(\Core\DataBase\Model\Relationship::LEFT_JOIN);
    $rel->setLoadOnFind(false);

    $rel = $ent->createRelationship('City');
    $rel->setForeignEntity('City');
    $rel->addForeignColumn('id');
    $rel->setParentEntity('OrderDayNew');
    $rel->addParentColumn('city_id');
    $rel->setType(\Core\DataBase\Model\Relationship::MANY_ONE);
    $rel->setJoinType(\Core\DataBase\Model\Relationship::LEFT_JOIN);
    $rel->setLoadOnFind(false);
    //---------

    $adminPanel = $moduleManager->getModule('AdminPanel');
    $adminPanel->addAdminMenuItem('shop', 'templates/admin/images/icons/shop.png', 'centerColumn.show(\'templates/admin/templates/shop/center_column.html\')', 'Магазин',1);
}