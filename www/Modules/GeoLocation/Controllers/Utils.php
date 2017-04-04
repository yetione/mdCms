<?php
namespace Modules\GeoLocation\Controllers;


use Core\Module\Base\Controller;
use Modules\GeoLocation\GeoLocation;

class Utils extends Controller{

    /**
     * @var GeoLocation
     */
    protected $module;

    public function getDefaultCity(array $data){
        $city = $this->module->getDefaultCity();
        if (is_null($city)){
            $view = $this->module->view('Error');
            $view->render(array('message'=>'Cant load default city'));
        }else{
            $view = $this->module->view('Entity');
            $view->render($city);
        }
    }

    public function changeCurrentCity(array $data){
        $em = $this->module->getCore()->getEntityManager();
        $input = $this->module->getCore()->getInput();

        $cityId = $input->get('Id', 0, TYPE_INT);
        if ($cityId <= 0){
            $view = $this->module->view('Error');
            $view->render(array('message'=>'City id must be positive integer'));
        }else{
            $query = $em->getEntityQuery('City');
            $query->findById($cityId);
            $ent = $query->loadOne();
            if (is_null($ent)){
                $view = $this->module->view('Error');
                $view->render(array('message'=>'City not found', 'code'=>404));
            }else{
                $this->module->setGeoData($ent);
                $view = $this->module->view('Entity');
                $view->render($ent);
            }
        }

    }
} 