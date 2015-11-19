<?php

require '../../Famework/Famework.php';
require '../application/autoloader.php';

use Famework\Famework;
use Famework\Config\Famework_Config;
use Famework\Registry\Famework_Registry;

define('APPLICATION_PATH', __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR);
define('HTTP_ROOT', str_replace(basename(__FILE__), '', $_SERVER['PHP_SELF']) . '/');
define('VIEW_PATH', APPLICATION_PATH . 'view');

Famework::registerDeafaultHandler();

$config = new Famework_Config(APPLICATION_PATH . 'config' . DIRECTORY_SEPARATOR . 'config.ini');
$routes = new Famework_Config(APPLICATION_PATH . 'config' . DIRECTORY_SEPARATOR . 'routes.ini');

if ($config->getValue('myenvoy', 'env') === 'dev') {
    Famework_Registry::setEnv(Famework::ENV_DEV);
    error_reporting(E_ALL | E_STRICT);
}

$famwork = new Famework($config, $routes);
$famwork->handleRequest();
$famwork->loadController();


