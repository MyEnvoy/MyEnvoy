<?php

spl_autoload_register(function ($class) {
    $paths = array();

    $paths[] = APPLICATION_PATH . 'controller' . DIRECTORY_SEPARATOR;
    $paths[] = APPLICATION_PATH . 'model' . DIRECTORY_SEPARATOR;
    
    $class = ucfirst(strtolower($class));
    
    // capitalize "Controller" for Famework
    $class = str_replace('controller', 'Controller', $class);
    
    foreach ($paths as $path) {
        $path = $path . $class . '.php';
        if(is_readable($path)) {
            require $path;
            return TRUE;
        }
    }
});