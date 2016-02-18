<?php
namespace SlapOM\Exception;

class Ldap extends SlapOM
{
    const LDAP_SUCCESS = 0;
    const LDAP_OPERATIONS_ERROR = 1;
    const LDAP_PROTOCOL_ERROR = 2;
    const LDAP_TIMELIMIT_EXCEEDED = 3;
    const LDAP_SIZELIMIT_EXCEEDED = 4;
    const LDAP_COMPARE_FALSE = 5;
    const LDAP_COMPARE_TRUE = 6;
    const LDAP_AUTH_METHOD_NOT_SUPPORTED = 7;
    const LDAP_STRONG_AUTH_REQUIRED = 8;
    const LDAP_REFERRAL = 10;
    const LDAP_ADMINLIMIT_EXCEEDED = 11;
    const LDAP_UNAVAILABLE_CRITICAL_EXTENSION = 12;
    const LDAP_CONFIDENTIALITY_REQUIRED = 13;
    const LDAP_SASL_BIND_IN_PROGRESS = 14;
    const LDAP_NO_SUCH_ATTRIBUTE = 16;
    const LDAP_UNDEFINED_TYPE = 17;
    const LDAP_INAPPROPRIATE_MATCHING = 18;
    const LDAP_CONSTRAINT_VIOLATION = 19;
    const LDAP_TYPE_OR_VALUE_EXISTS = 20;
    const LDAP_INVALID_SYNTAX = 21;
    const LDAP_NO_SUCH_OBJECT = 32;
    const LDAP_ALIAS_PROBLEM = 33;
    const LDAP_INVALID_DN_SYNTAX = 34;
    const LDAP_IS_LEAF = 35;
    const LDAP_ALIAS_DEREF_PROBLEM = 36;
    const LDAP_INAPPROPRIATE_AUTH = 48;
    const LDAP_INVALID_CREDENTIALS = 49;
    const LDAP_INSUFFICIENT_ACCESS = 50;
    const LDAP_BUSY = 51;
    const LDAP_UNAVAILABLE = 52;
    const LDAP_UNWILLING_TO_PERFORM = 53;
    const LDAP_LOOP_DETECT = 54;
    const LDAP_NAMING_VIOLATION = 64;
    const LDAP_OBJECT_CLASS_VIOLATION = 65;
    const LDAP_NOT_ALLOWED_ON_NONLEAF = 66;
    const LDAP_NOT_ALLOWED_ON_RDN = 67;
    const LDAP_ALREADY_EXISTS = 68;
    const LDAP_NO_OBJECT_CLASS_MODS = 69;
    const LDAP_RESULTS_TOO_LARGE = 70;
    const LDAP_AFFECTS_MULTIPLE_DSAS = 71;
    const LDAP_OTHER = 80;

    private $handler;

    public function __construct($message, $handler, $extra_error = null)
    {
        $this->handler = $handler;

        $err_no = ldap_errno($handler);
        $message = sprintf("ERROR %s. LDAP ERROR (%s) -- %s --. %s", $message, $err_no, ldap_err2str($err_no), ldap_error($handler), is_null($extra_error) ? '' : $extra_error);

        parent::__construct($message, $err_no);
    }

    public function getErrorNo()
    {
        return ldap_errno($this->handler);
    }
}
