<?php
namespace Core\DataBase\Utils;


interface NameGeneratorInterface {
    /**
     * Символ, используемый в качестве разделителя имен, в большинстве случаев.
     */
    const STD_SEPARATOR_CHAR = '_';

    /**
     * Традиционный метод для перевода названий таблиц и колонок в PHP имена.
     * Имена, разделенные нижнем подчеркиванием, будут преобразованы в нижний регистр,
     * а первая буква каждого слова в верхний.
     * Константы CONV_METHOD_XXX определяют метод перевода названий схемы БД в
     * PHP имена.
     *
     * @see PhpNameGenerator::underscoreMethod()
     */
    const CONV_METHOD_UNDERSCORE = 'underscore';

    /**
     * Метод аналогичен предыдущему {@link #CONV_METHOD_UNDERSCORE}, но он будет обрабатывать только
     * цифры и латинские буквы, считая за разделитель, все, что не входит
     * в этот диапазон.
     *
     * @see PhpNameGenerator::cleanMethod()
     */
    const CONV_METHOD_CLEAN = 'clean';

    /**
     * Метод аналогичен {@link #CONV_METHOD_UNDERSCORE}, за исключение того,
     * что он не переводит символы в нижний регистр.
     *
     * @see PhpNameGenerator::phpnameMethod()
     */
    const CONV_METHOD_PHPNAME = 'phpname';

    /**
     * Оставляет строку без изменений.
     */
    const CONV_METHOD_NOCHANGE = 'nochange';

    /**
     * В метод передается массив строк.
     * 1-ый элемент: строка, которую надо преобразовать;
     * 2-ой элемент: метод преобразования;
     * 3-ий элемент (не обязательный): префикс, который будет отброшен от 1-го элемента.
     *
     * @param  string[]        $inputs Inputs used to generate a name.
     * @return string          The generated name.
     */
    public function generateName($inputs);
}