<?php
namespace Core\GeoIp;


class GeoIpRecord {

    /**
     * @var string
     */
    protected $ip;

    /**
     * @var string
     */
    protected $country;

    /**
     * @var string
     */
    protected $city;

    /**
     * @var string
     */
    protected $region;

    /**
     * @var string
     */
    protected $district;

    /**
     * @var float
     */
    protected $lat;

    /**
     * @var float
     */
    protected $lng;

    /**
     * @var string
     */
    protected $inetnum;

    /**
     * @var string
     */
    protected $message;

    public function __construct(array $options=array()){
        $this->fromArray($options);
    }

    /**
     * @param array $data
     * @return $this
     */
    public function fromArray(array $data){
        foreach ($data as $prop => $val){
            if (property_exists($this, $prop)){
                $this->$prop = $val;
            }
        }
        return $this;
    }

    /**
     * @return array
     */
    public function toArray(){
        return get_object_vars($this);
    }

    /**
     * @param string $jsonStr
     * @return GeoIpRecord
     */
    public function fromJSON($jsonStr){
        return $this->fromArray(json_decode($jsonStr, true));
    }

    /**
     * @return string
     */
    public function toJSON(){
        return json_encode($this->toArray());
    }

    /**
     * @return bool
     */
    public function isValid(){
        return $this->message != 'Not found';
    }

    /**
     * @return string
     */
    public function getIp(){
        return $this->ip;
    }

    /**
     * @return string
     */
    public function getCountry(){
        return $this->country;
    }

    /**
     * @return string
     */
    public function getCity(){
        return $this->city;
    }

    /**
     * @return string
     */
    public function getRegion(){
        return $this->region;
    }

    /**
     * @return string
     */
    public function getDistrict(){
        return $this->district;
    }

    /**
     * @return float
     */
    public function getLat(){
        return $this->lat;
    }

    /**
     * @return float
     */
    public function getLng(){
        return $this->lng;
    }

    /**
     * @return string
     */
    public function getInetnum(){
        return $this->inetnum;
    }

    /**
     * @return string
     */
    public function getMessage(){
        return $this->message;
    }


}