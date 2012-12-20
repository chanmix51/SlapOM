<?php
namespace SlapOM\Exception;

class Ldap extends SlapOM
{
    public function __construct($message, $handler, $extra_error = null)
    {
        $err_no = ldap_errno($handler);
        $message = sprintf("ERROR %s. LDAP ERROR (%s) -- %s --. %s", $message, $err_no, ldap_err2str($err_no), ldap_error($handler), is_null($extra_error) ? '' : $extra_error);

        parent::_construct($message, $err_no);
    }
}
