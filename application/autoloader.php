<?php

spl_autoload_register(function ($class) {
    $paths = array();

    $paths[] = APPLICATION_PATH . 'controller' . DIRECTORY_SEPARATOR;
    $paths[] = APPLICATION_PATH . 'model' . DIRECTORY_SEPARATOR;
    $paths[] = APPLICATION_PATH . 'model' . DIRECTORY_SEPARATOR . 'traits' . DIRECTORY_SEPARATOR;
    $paths[] = APPLICATION_PATH . 'model' . DIRECTORY_SEPARATOR . 'api' . DIRECTORY_SEPARATOR;
    $paths[] = APPLICATION_PATH . 'model' . DIRECTORY_SEPARATOR . 'thirdparty' . DIRECTORY_SEPARATOR;

    if (strpos($class, '\\') !== FALSE) {
        // remove namesapce
        $split = explode('/', str_replace('\\', '/', $class));
        $class = $split[count($split) - 1];
    }
    
    $class = ucfirst(strtolower($class));

    // capitalize "Controller" for Famework
    $class = str_replace('controller', 'Controller', $class);

    foreach ($paths as $path) {
        $path = $path . $class . '.php';
        if (is_readable($path)) {
            require $path;
            return TRUE;
        }
    }
});
