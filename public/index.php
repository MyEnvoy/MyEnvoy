<?php

require '../../Famework/Famework.php';
require '../application/autoloader.php';

use Famework\Famework;
use Famework\Config\Famework_Config;
use Famework\Registry\Famework_Registry;
use Famework\Request\Famework_Request;
use Famework\Session\Famework_Session;

define('APPLICATION_PATH', __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR);
define('HTTP_ROOT', str_replace(basename(__FILE__), '', $_SERVER['PHP_SELF']) . '/');
define('VIEW_PATH', APPLICATION_PATH . 'view');

Famework::registerDefaultHandler();

$config = new Famework_Config(APPLICATION_PATH . 'config' . DIRECTORY_SEPARATOR . 'config.ini');
$routes = new Famework_Config(APPLICATION_PATH . 'config' . DIRECTORY_SEPARATOR . 'routes.ini');

if ($config->getValue('myenvoy', 'env') === 'dev') {
    Famework_Registry::setEnv(Famework::ENV_DEV);
    error_reporting(E_ALL | E_STRICT);
} else {
    error_reporting(0);
}

$famework = new Famework($config, $routes);

require '../application/globalfunctions.php';

$famework->handleRequest();

$lang = $famework->getRequestParam('lang');
if ($lang !== NULL) {
    if (isset(getLangs()[$lang])) {
        define('APPLICATION_LANG', $lang);
    } else {
        // unavailable language --> 404 Error
        $famework->truncateRequest();
    }
} else {
    // check if page is found
    if ($famework->getController() !== NULL) {
        $default = $config->getValue('myenvoy', 'default_lang');
        $default = ($default === NULL ? 'en' : $default);
        Famework_Request::redirect('/' . $default, Famework_Request::CODE_TEMPORARYREDIRECT);
    }
}

if ($config->getValue('myenvoy', 'support_https') == 1) {
    define('APPLICATION_HTTPS', TRUE);
} else {
    define('APPLICATION_HTTPS', FALSE);
}

Famework_Session::start(APPLICATION_HTTPS);
Famework_Registry::set('\famework_config', $config);
Famework_Registry::set('\famework_sys', $famework);

$famework->loadController();


