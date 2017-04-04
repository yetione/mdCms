<?php
namespace Core\Pluralizer;

/**
 * Общий интерфейс для модуля создания множественного числа слова.
 *
 * @author Hans Lellelid <hans@xmpl.org>
 */
interface PluralizerInterface
{
    /**
     * Генерирует форму множественного числа слова.
     * @param  string $root Слово для образования формы мн. числа.
     * @return string Мн. форма слова $root.
     */
    public function getPluralForm($root);
}