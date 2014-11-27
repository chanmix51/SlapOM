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
        $tmp = preg_replace_callback(
            '#/(.?)#',
            function ($matches) {
                return sprintf('::%s', strtoupper($matches[1]));
            },
            $tmp
        );
        $tmp = preg_replace_callback(
            '/(^|_|-)+(.)/',
            function ($matches) {
                return strtoupper($matches[2]);
            },
            $tmp 
        );

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
        $tmp = preg_replace(
            array('/([A-Z]+)([A-Z][a-z])/', '/([a-z\d])([A-Z])/'),
            array('\\1_\\2', '\\1_\\2'),
            $tmp
        );

        return strtolower($tmp);
    }
}
