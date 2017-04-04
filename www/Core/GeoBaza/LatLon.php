<?php
namespace Core\Geobaza;


class LatLon {
    /**
     * @var int
     */
    public $latitude, $longitude;

    /**
     * @param int|null $lat
     * @param int|null $lon
     */
    public function __construct($lat=NULL, $lon=NULL) {
        $this->latitude = $lat;
        $this->longitude = $lon;
    }
}