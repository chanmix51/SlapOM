<?php
namespace Slapom;

class LdapUtil
{
    public static function escape($str = '') {

        $metaChars = array ("\\\\00", "\\", "(", ")", "*");
        $quotedMetaChars = array ();
        foreach ($metaChars as $key => $value) {
            $quotedMetaChars[$key] = '\\'. dechex (ord ($value));
        }
        $str = str_replace (
            $metaChars, $quotedMetaChars, $str
        ); //replace them

        return ($str);
    }

    public static function activeDirectoryTimestampToUnix($ad_timestamp)
    {
        return (int) ((int) $ad_timestamp / 10000000) - 11644473600;
    }

    public static function unixTimestampToActiveDirectory($unix_timestamp)
    {
        return ((int) $unix_timestamp + 11644473600 ) * 10000000;
    }

    public static function ActiveDirectoryGuidToString($binary)
    {
        return sprintf("{%s}", join('-', sscanf(strtoupper(bin2hex($binary)), '%8s%4s%4s%4s%12s')));
    }
}
