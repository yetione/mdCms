<?php
namespace Modules\GeoLocation\Controllers;


use Core\Module\Base\Controller;
use Modules\GeoLocation\GeoLocation;

class Index extends Controller{

    /**
     * @var GeoLocation
     */
    protected $module;


    public function setCity(array $data){
        $cityId = (int) $data['cityId'];
        if ($cityId > 0){
            $em = $this->module->getCore()->getEntityManager();
            $query = $em->getEntityQuery('City');
            $query->findById($cityId);
            $ent = $query->loadOne();
            if (!is_null($ent)){
                $this->module->setGeoData($ent);
            }
        }
        $response = $this->module->getResponse();
        $response->back();
    }
} 