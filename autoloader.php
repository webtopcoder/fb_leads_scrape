<?php

spl_autoload_register(function ($class) {
    if (substr($class, 0, 4) === 'KLD\\') {
        $class = __DIR__ . '/' .  substr($class, 4);
    }
    include_once($class . '.php');
});
