<?php
namespace Core\Geobaza;


abstract class AbstractObject extends Serializer {
    /**
     * @var string
     */
    protected $encoding = 'utf-8';

    /**
     * @var \stdClass
     */
    protected $utf;
}