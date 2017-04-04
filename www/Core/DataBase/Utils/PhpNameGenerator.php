<?php
namespace Core\DataBase\Utils;


class PhpNameGenerator implements NameGeneratorInterface{

    /**
     * В метод передается массив строк.
     * 1-ый элемент: строка, которую надо преобразовать;
     * 2-ой элемент: метод преобразования;
     * 3-ий элемент (не обязательный): префикс, который будет отброшен от 1-го элемента.
     *
     * @param  string[]        $inputs Inputs used to generate a name.
     * @return string          The generated name.
     */
    public function generateName($inputs)
    {
        $schemaName = trim($inputs[0], " \t\n\r\0\x0B_");
        $method = $inputs[1];

        if (count($inputs) > 2) {
            $prefix = $inputs[2];
            if (!empty($prefix) && substr($schemaName, 0, strlen($prefix)) === $prefix) {
                $schemaName = substr($schemaName, strlen($prefix));
            }
        }
        $phpName = null;

        switch ($method) {
            case self::CONV_METHOD_CLEAN:
                $phpName = $this->cleanMethod($schemaName);
                break;
            case self::CONV_METHOD_PHPNAME:
                $phpName = $this->phpnameMethod($schemaName);
                break;
            case self::CONV_METHOD_NOCHANGE:
                $phpName = $this->nochangeMethod($schemaName);
                break;
            case self::CONV_METHOD_UNDERSCORE:
            default:
                $phpName = $this->underscoreMethod($schemaName);
        }

        return $phpName;
    }

    /**
     * Традиционный метод для перевода названий таблиц и колонок в PHP имена.
     * Имена, разделенные нижнем подчеркиванием, будут преобразованы в нижний регистр,
     * а первая буква каждого слова в верхний.
     *
     * my_CLASS_name -> MyClassName
     *
     * @param  string $schemaName имя для перевода
     * @return string Конвертированная строка.
     * @see QSNameGeneratorInterface
     * @see #underscoreMethod()
     */
    protected function underscoreMethod($schemaName)
    {
        $name = '';
        $tok = strtok($schemaName, self::STD_SEPARATOR_CHAR);
        while (false !== $tok) {
            $name .= ucfirst(strtolower($tok));
            $tok = strtok(self::STD_SEPARATOR_CHAR);
        }

        return $name;
    }

    /**
     * Метод будет обрабатывать только
     * цифры и латинские буквы, считая за разделитель, все, что не входит
     * в этот диапазон.
     *
     * T$NAMA$RFO_max => TNamaRfoMax
     *
     * @param  string $schemaName имя для перевода
     * @return string Конвертированная строка.
     * @see QSNameGeneratorInterface
     * @see #underscoreMethod()
     */
    protected function cleanMethod($schemaName)
    {
        $name = '';
        $regexp = '/([a-z0-9]+)/i';
        $matches = [];
        if (!preg_match_all($regexp, $schemaName, $matches)) {
            return $schemaName;
        }

        foreach ($matches[1] as $tok) {
            $name .= ucfirst(strtolower($tok));
        }

        return $name;
    }

    /**
     * Метод аналогичен {@link #cleanMethod()}, за исключение того,
     * что он не переводит символы в нижний регистр.
     *
     * my_CLASS_name -> MyCLASSName
     *
     * @param  string $schemaName имя для перевода
     * @return string Конвертированная строка.
     * @see QSNameGeneratorInterface
     * @see #underscoreMethod(String)
     */
    protected function phpnameMethod($schemaName)
    {
        $name = '';
        $tok = strtok($schemaName, self::STD_SEPARATOR_CHAR);
        while (false !== $tok) {
            $name .= ucfirst($tok);
            $tok = strtok(self::STD_SEPARATOR_CHAR);
        }

        return $name;
    }

    /**
     * Оставляет строку без изменений..
     *
     * @param  string $name имя для перевода
     * @return string Конвертированная строка.
     * @see QSNameGeneratorInterface
     */
    protected function nochangeMethod($name)
    {
        return $name;
    }
} 