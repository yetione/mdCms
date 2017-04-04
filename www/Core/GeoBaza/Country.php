<?php
namespace Core\Geobaza;

class Country extends Region {
    /**
     * @var string
     */
    public $tld;

    /**
     * @param object $data
     * @param string $encoding
     */
    public function __construct($data, $encoding='utf-8') {
        parent::__construct($data, $encoding);
        $this->set_tld($data);
    }

    protected function set_tld() {
        $this->tld = NULL;
        if (isset($this->iso_id)) {
            if ($this->iso_id == 'GB') {
                $this->tld = 'uk';
            }
            else {
                $this->tld = strtolower($this->iso_id);
            }
        }
    }

    /**
     * @return \DOMDocument
     */
    public function as_xml() {
        $xml = parent::as_xml();
        $tld = $xml->createElement('tld');
        $xml->documentElement->appendChild($tld);
        $tld->appendChild($xml->createTextNode($this->tld));
        return $xml;
    }

    /**
     * @return array
     */
    public function as_array() {
        $object = parent::as_array();
        $object['tld'] = $this->tld;
        return $object;
    }
}