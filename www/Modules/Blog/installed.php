<?php
use Core\Router\Route;

function Blog_install(\Core\Core $core, \Core\Module\ModuleManager $moduleManager){
    $router = $core->getRouter();
    $builder = $core->getEntityManager()->getDatabaseBuilder();
    $route = new Route('/blog', array('module'=>'Blog', 'controller'=>'Index', 'action'=>'indexPage', 'methods'=>array('GET')));
    $router->add($route);

    //BlogCategory
    $ent = $builder->createEntity('blog_category');
    $ent->addField('id')->addField('name')->addField('url')->addField('parent_id')->addField('all_parents')
        ->addField('posts_count')->addField('public_posts_count')
        ->addField('public')
        ->addField('creation_date')->addField('creator_id')
        ->addField('update_date')->addField('updater_id')
        ->addField('announce')->addField('content')
        ->addField('html_title')->addField('seo_keywords')->addField('seo_description')
        ->addAccessFlag(\Modules\Kernel\Kernel::ENTITY_UPDATE, 'a')
        ->addAccessFlag(\Modules\Kernel\Kernel::ENTITY_DELETE, 'a');

    $rel = $ent->createRelationship('Parent');
    $rel->setParentEntity('BlogCategory')->addParentColumn('parent_id')
        ->setForeignEntity('BlogCategory')->addForeignColumn('id')
        ->setType(\Core\DataBase\Model\Relationship::MANY_ONE)
        ->setJoinType(\Core\DataBase\Model\Relationship::LEFT_JOIN)
        ->setLoadOnFind(false);

    $rel = $ent->createRelationship('Creator');
    $rel->setParentEntity('BlogCategory')->addParentColumn('creator_id')
        ->setForeignEntity('User')->addParentColumn('id')
        ->setType(\Core\DataBase\Model\Relationship::MANY_ONE)
        ->setJoinType(\Core\DataBase\Model\Relationship::LEFT_JOIN)
        ->setLoadOnFind(false);

    $rel = $ent->createRelationship('Updater');
    $rel->setParentEntity('BlogCategory')->addParentColumn('updater_id')
        ->setForeignEntity('User')->addParentColumn('id')
        ->setType(\Core\DataBase\Model\Relationship::MANY_ONE)
        ->setJoinType(\Core\DataBase\Model\Relationship::LEFT_JOIN)
        ->setLoadOnFind(false);
    //---------

    //BlogPost
    $ent = $builder->createEntity('blog_post');
    $ent->addField('id')->addField('header')->addField('announce')->addField('content')->addField('image')->addField('thumbnail')->addField('category_id')
        ->addField('public')
        ->addField('creation_date')->addField('creator_id')
        ->addField('update_date')->addField('updater_id')
        ->addField('html_title')->addField('seo_keywords')->addField('seo_description')
        ->addAccessFlag(\Modules\Kernel\Kernel::ENTITY_UPDATE, 'a')
        ->addAccessFlag(\Modules\Kernel\Kernel::ENTITY_DELETE, 'a');

    $rel = $ent->createRelationship('Category');
    $rel->setParentEntity('BlogPost')->addParentColumn('category_id')
        ->setForeignEntity('BlogCategory')->addForeignColumn('id')
        ->setType(\Core\DataBase\Model\Relationship::MANY_ONE)
        ->setJoinType(\Core\DataBase\Model\Relationship::INNER_JOIN)
        ->setLoadOnFind(false);

    $rel = $ent->createRelationship('Creator');
    $rel->setParentEntity('BlogPost')->addParentColumn('creator_id')
        ->setForeignEntity('User')->addParentColumn('id')
        ->setType(\Core\DataBase\Model\Relationship::MANY_ONE)
        ->setJoinType(\Core\DataBase\Model\Relationship::LEFT_JOIN)
        ->setLoadOnFind(false);

    $rel = $ent->createRelationship('Updater');
    $rel->setParentEntity('BlogPost')->addParentColumn('updater_id')
        ->setForeignEntity('User')->addParentColumn('id')
        ->setType(\Core\DataBase\Model\Relationship::MANY_ONE)
        ->setJoinType(\Core\DataBase\Model\Relationship::LEFT_JOIN)
        ->setLoadOnFind(false);
    //---------

    $adminPanel = $moduleManager->getModule('AdminPanel');
    $adminPanel->addAdminMenuItem('blog', 'templates/admin/images/icons/shop.png', 'centerColumn.show(\'templates/admin/templates/blog/center_column.html\')', 'Блог',1);
}