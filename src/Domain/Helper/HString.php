<?php

namespace App\Domain\Helper;

/**
 * Class StringHelper
 *
 * The StringHelper class provides various utility methods for working with strings.
 */
class HString
{
    /**
     * Возвращает транслитерацию строки
     *
     * @param string $rus
     *
     * @return string
     */
    public static function rusToEng(string $rus): string
    {
        $map = [
            'А' => 'A',
            'Б' => 'B',
            'В' => 'V',
            'Г' => 'G',
            'Д' => 'D',
            'Е' => 'E',
            'Ё' => 'E',
            'Ж' => 'Zh',
            'З' => 'Z',
            'И' => 'I',
            'Й' => 'Y',
            'К' => 'K',
            'Л' => 'L',
            'М' => 'M',
            'Н' => 'N',
            'О' => 'O',
            'П' => 'P',
            'Р' => 'R',
            'С' => 'S',
            'Т' => 'T',
            'У' => 'U',
            'Ф' => 'F',
            'Х' => 'Kh',
            'Ц' => 'Ts',
            'Ч' => 'Ch',
            'Ш' => 'Sh',
            'Щ' => 'Shch',
            'Ъ' => '',
            'Ы' => 'Y',
            'Ь' => '',
            'Э' => 'E',
            'Ю' => 'Yu',
            'Я' => 'Ya',
            'а' => 'a',
            'б' => 'b',
            'в' => 'v',
            'г' => 'g',
            'д' => 'd',
            'е' => 'e',
            'ё' => 'e',
            'ж' => 'zh',
            'з' => 'z',
            'и' => 'i',
            'й' => 'y',
            'к' => 'k',
            'л' => 'l',
            'м' => 'm',
            'н' => 'n',
            'о' => 'o',
            'п' => 'p',
            'р' => 'r',
            'с' => 's',
            'т' => 't',
            'у' => 'u',
            'ф' => 'f',
            'х' => 'kh',
            'ц' => 'ts',
            'ч' => 'ch',
            'ш' => 'sh',
            'щ' => 'shch',
            'ъ' => '',
            'ы' => 'y',
            'ь' => '',
            'э' => 'e',
            'ю' => 'yu',
            'я' => 'ya',
        ];
        return str_replace(array_keys($map), array_values($map), $rus);
    }

    /**
     * Меняет раскладку клавиатуры используя раскладку мака
     *
     * @param string $str
     *
     * @return string
     */
    public static function changeEngKeyboardLayoutToRus(string $str): string
    {
        $eng = '`1234567890-=qwertyuiop[]\\asdfghjkl;\'zxcvbnm,./~!@#$%^&*()_+QWERTYUIOP{}|ASDFGHJKL:"ZXCVBNM<>?';
        $rus = ']1234567890-=йцукенгшщзхъёфывапролджэячсмитьбю/[!"№%:,.;()_+ЙЦУКЕНГШЩЗХЪЁФЫВАПРОЛДЖЭЯЧСМИТЬБЮ?';

        return str_replace(mb_str_split($eng), mb_str_split($rus), $str);
    }

    /**
     * Меняет раскладку клавиатуры используя раскладку мака
     *
     * @param string $str
     *
     * @return string
     */
    public static function changeRusKeyboardLayoutToEng(string $str): string
    {
        $eng = '`1234567890-=qwertyuiop[]\\asdfghjkl;\'zxcvbnm,./~!@#$%^&*()_+QWERTYUIOP{}|ASDFGHJKL:"ZXCVBNM<>?';
        $rus = ']1234567890-=йцукенгшщзхъёфывапролджэячсмитьбю/[!"№%:,.;()_+ЙЦУКЕНГШЩЗХЪЁФЫВАПРОЛДЖЭЯЧСМИТЬБЮ?';

        return str_replace(mb_str_split($rus), mb_str_split($eng), $str);
    }
}
