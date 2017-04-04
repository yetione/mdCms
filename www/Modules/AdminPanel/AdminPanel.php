<?php
namespace Modules\AdminPanel;


use Core\DataBase\Model\Entity;
use Core\Module\Base\Module;

class AdminPanel extends Module {

    protected $moduleName = 'AdminPanel';

    /**
     * @var Entity[]
     */
    protected $adminMenuItems;

    const ADMIN_MENU_ITEMS_CACHE_KEY = 'AdminPanel.adminMenuItems';


    protected function init(array $configs){
        $adminMenu = null;
        if (is_null($adminMenu = $this->getAdminMenuFromCache())){
            $adminMenu = $this->getAdminMenuFromDB();
        }
        $this->adminMenuItems = $adminMenu;
        $this->sortAdminMenuItems();
    }

    protected function sortAdminMenuItems(){
        usort($this->adminMenuItems, function($a, $b){
            if ((int) $a->getOrder() === (int) $b->getOrder()){
                return 0;
            }
            return ((int) $a->getOrder() < (int) $b->getOrder()) ? -1 : 1;
        });
    }

    /**
     * @return null
     */
    protected function getAdminMenuFromCache(){
        $cache = $this->core->getCache();
        if (is_array($result = $cache->get(self::ADMIN_MENU_ITEMS_CACHE_KEY))){
            foreach ($result as &$menuItem){
                $ent = $this->core->getEntityManager()->getEntity('AdminMenu');
                $ent->fromArray($menuItem);
                $menuItem = $ent;
            }
        }
        return $result;
    }

    protected function saveAdminMenuToCache(){
        $cache = $this->core->getCache();
        return $cache->set(self::ADMIN_MENU_ITEMS_CACHE_KEY, array_map(function($item){
            return $item->toArray();
        }, $this->adminMenuItems));
    }

    /**
     * @return \Core\DataBase\Model\Entity[]
     */
    protected function getAdminMenuFromDB(){
        $query = $this->core->getEntityManager()->getEntityQuery('AdminMenu');
        $query->orderByOrder();
        return $query->load();
    }

    protected function isAdminMenuNameExists($name){
        foreach ($this->adminMenuItems as $item){
            if ($item->getName() == $name){
                return true;
            }
        }
        return false;
    }

    public function addAdminMenuItem($name, $icon, $action, $title,$order=1, $override=false){
        if ($this->isAdminMenuNameExists($name) && !$override){
            return true;
        }
        $ent = $this->core->getEntityManager()->getEntity('AdminMenu');
        $ent->setName($name)->setIcon($icon)->setAction($action)->setTitle($title)->setOrder($order);

        $this->adminMenuItems[] = $ent;
        $this->sortAdminMenuItems();
        $this->saveAdminMenuToCache();

        return $this->core->getEntityManager()->getEntityQuery('AdminMenu')->save($ent);
    }

    /**
     * @return array
     */
    public function getAdminMenuItems(){
        return $this->adminMenuItems;
    }
} 