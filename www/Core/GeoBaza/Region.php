<?php
namespace Core\Geobaza;

class Region extends GeobazaObject {
    /**
     * @var string
     */
    public $language;

    /**
     * @param object $data
     * @param string $encoding
     */
    public function __construct($data, $encoding='utf-8') {
        $this->set_lang($data);
        parent::__construct($data, $encoding);
    }

    /**
     * @param object $data
     * @throws Exception\GeobazaException
     */
    protected function set_lang($data) {
        $this->language = NULL;
        if (isset($data->lang)) {
            $lang = strtolower($data->lang);
            if ($lang) {
                $this->language = Languages::get_language($lang);
            }
        }
    }

    protected function encode() {
        parent::encode();
        if ($this->language instanceof Language) {
            $this->utf->language = clone($this->language);
        } else {
            $this->utf->language = $this->language;
        }

        if ($this->encoding != 'utf-8') {
            $this->language->name = convert_encoding($this->language->name, $this->encoding);
        }
    }

    /**
     * @return \DOMDocument
     */
    public function as_xml() {
        $xml = parent::as_xml();
        $language = $xml->createElement('language');
        $xml->documentElement->appendChild($language);
        if (!empty($this->utf->language)) {
            $language->setAttribute('id', $this->utf->language->id);
            $language->appendChild($xml->createTextNode($this->utf->language->name));
        }
        return $xml;
    }

    /**
     * @return array
     */
    public function as_array() {
        $object = parent::as_array();
        $language = NULL;
        if (!empty($this->utf->language)) {
            $language = array(
                'name' => $this->utf->language->name,
                'id' => $this->utf->language->id
            );
        }
        $object['language'] = $language;
        return $object;
    }
}