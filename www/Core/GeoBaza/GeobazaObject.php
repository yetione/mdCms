<?php
namespace Core\Geobaza;


class GeobazaObject extends AbstractObject {
    /**
     * @var GeobazaObject|null
     */
    protected $parent = NULL;

    /**
     * @var GeobazaQuery
     */
    protected $query;

    /**
     * @var string
     */
    protected$encoding;

    /**
     * @var int
     */
    public $id;

    /**
     * @var int
     */
    public $level;

    /**
     * @var string
     */
    public $type;

    /**
     * @var string
     */
    public $iso_id;

    /**
     * @var string
     */
    public $name;

    /**
     * @var array
     */
    public $translations;

    /**
     * @var GeobazaObject
     */
    public $child;

    /**
     * @var int
     */
    public $population;

    /**
     * @var Geography
     */
    public $geography;

    const COUNTRY = 'country';
    const REGION = 'region';
    const LOCALITY = 'locality';
    const SPECIAL = 'special';

    /**
     * @param object $data
     * @param string $encoding
     */
    public function __construct($data, $encoding='utf-8') {
        $this->encoding = strtolower($encoding);
        $this->id = (int)$data->id;
        $this->level = (int)$data->level;
        $this->type = $data->type;
        if (isset($data->iso_id)) {
            $this->iso_id = $data->iso_id;
        }

        $this->set_geography($data);
        $this->set_population($data);
        $this->set_translation($data);
        $this->set_name();
        $this->encode();
    }

    /**
     * @param string $name
     * @param $value
     */
    public function __set($name, $value) {
        switch ($name) {
            case 'query':
                $this->query = $value;
            case 'parent':
                $this->parent = $value;
        }
    }

    public function __get($name) {
        switch ($name) {
            case 'parent':
                $this->fetch_parent();
                return $this->parent;
        }
    }

    protected function fetch_parent() {
        if (is_int($this->parent)) {
            $data = $this->query->get_from($this->parent);

            if (count($data) == 2) {
                $obj = $data[0];
                $parent = $data[1]->parent;
            }
            else {
                $obj = $data[0];
                $parent = NULL;
            }
            $this->parent = Geobaza::create_instance($obj, $this->encoding, $this, $parent);
            $this->parent->query = $this->query;
        }
    }

    /**
     * @param object $data
     */
    protected function set_geography($data) {
        $lat = $lon = NULL;
        if (isset($data->lat)) {
            $lat = $data->lat;
        }
        if (isset($data->lon)) {
            $lon = $data->lon;
        }
        $this->geography = new Geography(array('center' => new LatLon($lat, $lon)));
    }

    /**
     * @param object $data
     */
    protected function set_population($data) {
        $this->population = NULL;
        if (isset($data->population)) {
            $this->population = (int)$data->population;
        }
    }

    /**
     * @param object $data
     */
    protected function set_translation($data) {
        $RAW_MAP = array(
            'official' => array(0, 'name_official'),
            'alt' => array(1, 'name'),
        );

        $this->translations = array();
        foreach ($RAW_MAP as $type => $value) {
            $priority = $value[0];
            $raw_key = $value[1];

            $obj = array('type' => $type);
            foreach ($data->$raw_key as $lang => $name) {
                $obj[strtolower($lang)] = $name;
            }
            $this->translations[] = (object)$obj;
        }
    }

    protected function set_name() {
        $this->name = $this->translations[0]->en;
    }

    protected function encode() {
        $this->utf = new \stdClass();
        $this->utf->translations = array();
        foreach ($this->translations as $item) {
            $this->utf->translations[] = clone($item);
        }
        $this->utf->name = $this->name;

        for ($i = 0; $i < count($this->translations); $i++) {
            foreach ($this->translations[$i] as $key => $value) {
                if ($key != 'type') {
                    $value = convert_encoding($value, $this->encoding);
                }
                $this->translations[$i]->$key = $value;
            }
        }
    }

    /**
     * @return \DOMDocument
     */
    public function as_xml() {
        $xml = new \DOMDocument('1.0', $this->encoding);
        $root = $xml->createElement('object');
        $xml->appendChild($root);
        $root->setAttribute('type', $this->type);
        $root->setAttribute('id', $this->id);
        $root->setAttribute('level', $this->level);
        $this->fetch_parent();
        if (!empty($this->parent)) {
            $root->setAttribute('parent', $this->parent->id);
        }
        if (!empty($this->child)) {
            $root->setAttribute('child', $this->child->id);
        }

        $name = $xml->createElement('name');
        $name->appendChild($xml->createTextNode($this->utf->name));
        $root->appendChild($name);

        $iso_id = $xml->createElement('iso-id');
        $iso_id->appendChild($xml->createTextNode($this->iso_id));
        $root->appendChild($iso_id);

        $population = $xml->createElement('population');
        $population->appendChild($xml->createTextNode($this->population));
        $root->appendChild($population);

        $translations = $xml->createElement('translations');
        foreach($this->utf->translations as $item) {
            $group = $xml->createElement('group');
            $translations->appendChild($group);
            $group->setAttribute('type', $item->type);
            foreach($item as $key => $value) {
                if ($key != 'type') {
                    $translation = $xml->createElement('item');
                    $translation->setAttribute('language', $key);
                    $translation->appendChild($xml->createTextNode($value));
                    $group->appendChild($translation);
                }
            }
        }
        $root->appendChild($translations);

        $geography = $xml->createElement('geography');
        $geography_xml = $this->geography->as_xml();
        $node = $xml->importNode($geography_xml->documentElement, true);
        $geography->appendChild($node);
        $root->appendChild($geography);

        return $xml;
    }

    /**
     * @return array
     */
    public function as_array() {
        $object = array(
            'type' => $this->type,
            'name' => $this->utf->name,
            'iso_id' => $this->iso_id,
            'id' => $this->id,
            'level' => $this->level,
        );

        $this->fetch_parent();
        if (!empty($this->parent)) {
            $object['parent'] = $this->parent->id;
        }
        if (!empty($this->child)) {
            $object['child'] = $this->child->id;
        }

        $object['geography'] = $this->geography->as_array();
        $object['population'] = $this->population;
        $object['translations'] = $this->utf->translations;

        return $object;
    }
}