<?php

$ini = parse_ini_file(dirname(__FILE__) . '/tests/config/config.ini');
@define('LDAP_HOST', $ini['host']);
@define('LDAP_PORT', $ini['port']);
@define('LDAP_BIND_DN', $ini['bindDn']);
@define('LDAP_PASSWORD', $ini['password']);

require_once __DIR__ . '/vendor/autoload.php';
