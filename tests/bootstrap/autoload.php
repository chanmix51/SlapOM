<?php

spl_autoload_register(function ($class) {
    if (0 === strpos($class, 'SlapOM\\'))
    {
        $class = str_replace('\\', '/', $class);
        require sprintf("%s/lib/%s.php", dirname(dirname(__DIR__)), $class);
    }
});