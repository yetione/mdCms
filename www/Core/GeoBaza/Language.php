<?php
namespace Core\Geobaza;


class Language {
    /**
     * @var string
     */
    public $id, $name;

    /**
     * @param string $id
     * @param string $name
     */
    public function __construct($id, $name) {
        $this->id = $id;
        $this->name = $name;
    }
}
