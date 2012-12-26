<?php

$ini = parse_ini_file(dirname(__FILE__) . '/../config/config.ini');
define('LDAP_HOST', $ini['host']);
define('LDAP_BIND_DN', $ini['bindDn']);
define('LDAP_PASSWORD', $ini['password']);

spl_autoload_register(function ($class) {
    if (0 === strpos($class, 'SlapOM\\'))
    {
        $class = str_replace('\\', '/', $class);
        require sprintf("%s/lib/%s.php", dirname(dirname(__DIR__)), $class);
    }
});