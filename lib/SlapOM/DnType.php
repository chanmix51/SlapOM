<?php
namespace SlapOM;

use SlapOM\Exception\SlapOM as Exception;

class DnType
{
    protected $dn;

    public function __construct($dn)
    {
        $this->dn = $dn;
    }

    public function __toString()
    {
        return $this->dn;
    }

    public function escape() 
    {

        $escaped_chars = [ "\\\\00", "\\", "(", ")", "*" ];
        $quoted_chars = [];

        foreach ($escaped_chars as $key => $value) 
        {
            $quoted_chars[$key] = '\\'. dechex (ord ($value));
        }

        return str_replace($escaped_chars, $quoted_chars, $this->dn);
    }

    public function extractCn()
    {
        if (!preg_match('/CN=(?P<cn>[^,]+)/', $this->dn, $matches))
        {
            throw new Exception(sprintf("Could not find leading CN in DN:'%s'.", $this->dn));
        }

        return $matches['cn'];
    }

    public function extract()
    {
        $parts = [];
        foreach(preg_split('/[^\\],/', $this->dn) as $container)
        {
            $subparts = preg_split('/=/', $container, 1);
            $parts[] = $subparts;
        }

        return $parts;
    }
}
