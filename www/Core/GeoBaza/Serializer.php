<?php
namespace Core\Geobaza;


abstract class Serializer {
    /**
     * @return string
     */
    public function to_xml() {
        $xml = $this->as_xml();
        return $xml->saveXML();
    }

    /**
     * @return \DOMDocument
     */
    abstract public function as_xml();

    /**
     * @return string
     */
    public function to_json() {
        return json_encode($this->as_array());
    }

    /**
     * @return array
     */
    abstract public function as_array();

    /**
     * @return string
     */
    public function to_pretty_xml() {
        $xml = $this->as_xml();
        $xml->formatOutput = true;
        return $xml->saveXML();
    }

    /**
     * PHP >= 5.4.0
     * @link http://php.net/manual/en/function.json-encode.php#refsect1-function.json-encode-changelog
     * @return string
     */
    public function to_pretty_json() {
        return json_encode($this->as_array(), JSON_PRETTY_PRINT);
    }
} 