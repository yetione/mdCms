<?php
namespace Core\Geobaza;


class SpecialRange extends AbstractObject {
    /**
     * @param object $data
     * @param string $encoding
     */
    public function __construct($data, $encoding='utf-8') {
        $this->utf = new \stdClass();
        $this->type = GeobazaObject::SPECIAL;
        $this->encoding = strtolower($encoding);
        $name = $data->special ? $data->special : NULL;
        $this->name = $this->utf->name = $name;
        if (!empty($name) && $this->encoding != 'utf-8') {
            $this->name = convert_encoding($name, $this->encoding);
        }
    }

    /**
     * @return \DOMDocument
     */
    public function as_xml() {
        $xml = new \DOMDocument('1.0', $this->encoding);
        $root = $xml->createElement('object');
        $root->setAttribute('type', $this->type);
        $name = $xml->createElement('name');
        $name->appendChild($xml->createTextNode($this->utf->name));
        $root->appendChild($name);
        $xml->appendChild($root);
        return $xml;
    }

    /**
     * @return array
     */
    public function as_array() {
        $object = array('type' => $this->type, 'name' => $this->utf->name);

        return $object;
    }
}