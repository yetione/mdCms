<?php
namespace Modules\Blog;


use Core\Router\Route;
use Modules\Kernel\Kernel;
use Core\DataBase\Model\Relationship;

class Installer extends \Core\Module\Base\Installer{

    public function install(){
        $this->createRoutes();
        $this->createEntities();
    }

    protected function createRoutes(){
        $router = $this->getCore()->getRouter();
        $route = new Route('/blog', array('module'=>'Blog', 'controller'=>'Index', 'action'=>'indexPage', 'methods'=>array('GET')));
        $router->add($route);
    }

    protected function createEntities(){
        $builder = $this->getCore()->getEntityManager()->getDatabaseBuilder();
        //BlogCategory
        $ent = $builder->createEntity('blog_category');
        $ent->addField('id')->addField('name')->addField('url')->addField('parent_id', 0, TYPE_INT)->addField('all_parents', '')
            ->addField('posts_count', 0, TYPE_INT)->addField('public_posts_count', 0, TYPE_INT)
            ->addField('public', 1, TYPE_INT)
            ->addField('creation_date', null, TYPE_INT)->addField('creator_id', null, TYPE_INT)
            ->addField('update_date', null, TYPE_INT)->addField('updater_id', null, TYPE_INT)
            ->addField('announce')->addField('content')
            ->addField('html_title')->addField('seo_keywords')->addField('seo_description')
            ->addAccessFlag(Kernel::ENTITY_UPDATE, 'a')
            ->addAccessFlag(Kernel::ENTITY_DELETE, 'a');

        $rel = $ent->createRelationship('Parent');
        $rel->setParentEntity('BlogCategory')->addParentColumn('parent_id')
            ->setForeignEntity('BlogCategory')->addForeignColumn('id')
            ->setType(Relationship::MANY_ONE)
            ->setJoinType(Relationship::LEFT_JOIN)
            ->setLoadOnFind(false);

        $rel = $ent->createRelationship('Creator');
        $rel->setParentEntity('BlogCategory')->addParentColumn('creator_id')
            ->setForeignEntity('User')->addForeignColumn('id')
            ->setType(Relationship::MANY_ONE)
            ->setJoinType(Relationship::LEFT_JOIN)
            ->setLoadOnFind(false);

        $rel = $ent->createRelationship('Updater');
        $rel->setParentEntity('BlogCategory')->addParentColumn('updater_id')
            ->setForeignEntity('User')->addForeignColumn('id')
            ->setType(Relationship::MANY_ONE)
            ->setJoinType(Relationship::LEFT_JOIN)
            ->setLoadOnFind(false);
        //---------

        //BlogPost
        $ent = $builder->createEntity('blog_post');
        $ent->addField('id')->addField('header')->addField('announce')->addField('content')->addField('image')->addField('thumbnail')->addField('category_id', null, TYPE_INT)
            ->addField('public', 1, TYPE_INT)
            ->addField('creation_date', null, TYPE_INT)->addField('creator_id', null, TYPE_INT)
            ->addField('update_date', null, TYPE_INT)->addField('updater_id', null, TYPE_INT)
            ->addField('html_title')->addField('seo_keywords')->addField('seo_description')
            ->addAccessFlag(Kernel::ENTITY_UPDATE, 'a')
            ->addAccessFlag(Kernel::ENTITY_DELETE, 'a');

        $rel = $ent->createRelationship('Category');
        $rel->setParentEntity('BlogPost')->addParentColumn('category_id')
            ->setForeignEntity('BlogCategory')->addForeignColumn('id')
            ->setType(Relationship::MANY_ONE)
            ->setJoinType(Relationship::INNER_JOIN)
            ->setLoadOnFind(false);

        $rel = $ent->createRelationship('Creator');
        $rel->setParentEntity('BlogPost')->addParentColumn('creator_id')
            ->setForeignEntity('User')->addForeignColumn('id')
            ->setType(Relationship::MANY_ONE)
            ->setJoinType(Relationship::LEFT_JOIN)
            ->setLoadOnFind(false);

        $rel = $ent->createRelationship('Updater');
        $rel->setParentEntity('BlogPost')->addParentColumn('updater_id')
            ->setForeignEntity('User')->addForeignColumn('id')
            ->setType(Relationship::MANY_ONE)
            ->setJoinType(Relationship::LEFT_JOIN)
            ->setLoadOnFind(false);
        //---------
    }

}