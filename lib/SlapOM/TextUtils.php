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
     * @param  string $word  String to camelize.
     *
     * @return string Camelized string.
     **/

    public static function camelize($word)
    {
        $word = preg_replace_callback('#/(.?)#', function($match) { return strtoupper($match[1]); }, $word);
        $word = preg_replace_callback('/(^|_|-)+(.)/' , function($match) { return strtoupper($match[2]); }, $word);

        return $word;
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
