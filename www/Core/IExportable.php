<?php
namespace Core;

/**
 * Интерфейс упаковываемого объекта
 * Interface QSIExportable
 * @package Core
 */
interface IExportable {
    /**
     * Этот статический метод вызывается для тех классов,
     * которые экспортируются функцией var_export() начиная с PHP 5.1.0.
     * Параметр этого метода должен содержать массив,
     * состоящий из экспортируемых свойств в виде array('property' => value, ...).
     * @see http://php.net/manual/ru/language.oop5.magic.php#object.set-state
     * @param array $array
     * @return mixed
     */
    public static function __set_state(array $array);
} 