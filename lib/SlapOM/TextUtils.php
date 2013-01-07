<?php
namespace SlapOM;

/** textUtils
 *  This work belongs to the symfony project
 *  and has been written by Fabien Potencier.
 *  (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 **/

class TextUtils
{
    /**
     * Returns a camelized string from a lower case and underscored string by replaceing slash with
     * double-colon and upper-casing each letter preceded by an underscore.
     *
     * @param  string $lower_case_and_underscored_word  String to camelize.
     *
     * @return string Camelized string.
     **/

    public static function camelize($lower_case_and_underscored_word)
    {
        $tmp = $lower_case_and_underscored_word;
        $tmp = static::pregtr($tmp, array('#/(.?)#e'    => "'::'.strtoupper('\\1')",
            '/(^|_|-)+(.)/e' => "strtoupper('\\2')"));

        return $tmp;
    }

    /**
     * Returns an underscore-syntaxed version or the CamelCased string.
     *
     * @param  string $camel_cased_word  String to underscore.
     *
     * @return string Underscored string.
     **/
    public static function underscore($camel_cased_word)
    {
        $tmp = $camel_cased_word;
        $tmp = str_replace('::', '/', $tmp);
        $tmp = static::pregtr($tmp, array('/([A-Z]+)([A-Z][a-z])/' => '\\1_\\2',
            '/([a-z\d])([A-Z])/'     => '\\1_\\2'));

        return strtolower($tmp);
    }

    /**
     * Returns subject replaced with regular expression matchs
     *
     * @param mixed $search        subject to search
     * @param array $replacePairs  array of search => replace pairs
     **/
    public static function pregtr($search, $replacePairs)
    {
        return preg_replace(array_keys($replacePairs), array_values($replacePairs), $search);
    }
}
