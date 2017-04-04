<?php
namespace Core\ORM\Entity;


class Metadata {

    const PHP_NAME = 'phpName';
    const SQL_NAME = 'sqlName';

    /**
     * Имя сущности
     * @var string
     */
    protected $name;

    /**
     * Имя таблицы сущности
     * @var string
     */
    protected $tableName;



}