<?php
namespace Core\Geobaza;


class Geography extends Serializer {
    /**
     * @var LatLon
     */
    public $center;

    /**
     * @param array $args
     */
    public function __construct($args) {
        foreach ($args as $key => $value) {
            $this->$key = $value;
        }
    }

    /**
     * @return \DOMDocument
     */
    public function as_xml() {
        $xml = new \DOMDocument('1.0', 'utf-8');
        $root = $xml->createElement('center');
        $root->setAttribute('latitude', $this->center->latitude);
        $root->setAttribute('longitude', $this->center->longitude);
        $xml->appendChild($root);
        return $xml;
    }

    /**
     * @return array
     */
    public function as_array() {
        return array('center' => array('latitude' => $this->center->latitude, 'longitude' => $this->center->longitude));
    }
}