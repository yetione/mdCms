<?php
namespace Modules\GeoLocation;


use Core\DataBase\Entities\City;
use Core\Debugger;
use Core\Event\EventVar;
use Core\GeoIp\GeoIpDatabase;
use Core\Module\Base\Module;

class GeoLocation extends Module{

    protected $moduleName = 'GeoLocation';

    protected $geoData;

    protected $defaultCityName;

    const COOKIE_KEY = 'GL_data_new';
    const SESSION_KEY = 'GeoLocation.geoData';
    const COOKIE_LIFETIME = 604800;

    protected $detectByIp = false;

    /**
     * Google API key
     * @var string
     */
    protected $key = 'AIzaSyAOpn4tzHGnahgdOFLx997qN8TnTJMWueo';


    protected function init(array $configs){
        $this->defaultCityName = $configs['DefaultCityName'];
        $this->core->getEventManager()->hook('Application.Load', array($this, 'onAppLoad'));
    }

    /**
     * @return \Core\DataBase\Entities\City
     */
    public function getDefaultCity(){
        $query = $this->getCore()->getEntityManager()->getEntityQuery('City');
        return $query->findByName($this->defaultCityName)->loadOne();
    }

    public function onAppLoad(EventVar $ev){
        $this->getGeoData();
    }

    /**
     * @param City $city
     * @throws \Core\Session\Exception\StateError
     */
    public function setGeoData(City $city){
        $this->geoData = $city;
        $this->getCore()->getSession()->set(self::SESSION_KEY, $city);
        $this->getCore()->getInput()->setCookie(self::COOKIE_KEY, json_encode($city->toArray()), time()+self::COOKIE_LIFETIME, BASE_URL);
    }

    /**
     * @return City
     */
    public function getGeoData(){
        if (!$this->geoData){
            $input = $this->getCore()->getInput();
            $cityData = $input->cookie(self::COOKIE_KEY, null, TYPE_RAW);
            if (!is_null($cityData)){
                $cityData = json_decode($cityData, true);
            }
            if (empty($cityData)){
                $cityName = $this->defaultCityName;
                if (null !== ($ip = $input->getIp()) && $this->detectByIp){
                    $geoIpDb = new GeoIpDatabase();
                    if (!is_null($ipData = $geoIpDb->getGeobaseData($ip)) && $ipData->isValid()){
                        $cityName = $ipData->getCity();
                    }
                }
                $query = $this->getCore()->getEntityManager()->getEntityQuery('City');
                $cityEntity = $query->findByName($cityName)
                    ->loadOne(false, true);
                if (is_null($cityEntity)){
                    Debugger::log('GeoLocation::getGeoData: City: '.$cityName.' not found');
                    //TODO: Город не найден в списке, надо куда-нибудь записать об этом
                    $cityEntity = $this->getDefaultCity();
                }
            }else{
                $cityEntity = $this->getCore()->getEntityManager()->getEntity('City');
                $cityEntity->fromArray($cityData);

            }
            //$cityEntity->_setEntityManager($this->getCore()->getEntityManager());
            //$cityEntity->_setEntityMetadata($this->getCore()->getEntityManager()->getEntityMetadata('City'));
            $this->setGeoData($cityEntity);
        }
        return $this->geoData;
    }

    /**
     * @param string $name
     * @return \Core\DataBase\Model\Entity|null
     */
    protected function getCityByName($name){
        $query = $this->getCore()->getEntityManager()->getEntityQuery('City');
        $city = $query->findByName($name)->loadOne();
        return $city;
    }
} 