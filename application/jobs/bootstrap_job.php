<?php

error_reporting(E_ALL | E_STRICT);

define('APPLICATION_PATH', __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR);

require __DIR__ . '/../../../Famework/Famework.php';
require APPLICATION_PATH . 'autoloader.php';

use Famework\Famework;
use Famework\Config\Famework_Config;
use Famework\Registry\Famework_Registry;

$config = new Famework_Config(APPLICATION_PATH . 'config' . DIRECTORY_SEPARATOR . 'config.ini');

require APPLICATION_PATH . 'globalfunctions.php';

$famework = new Famework($config, new Famework_Config(''));

Famework_Registry::set('\famework_config', $config);
Famework_Registry::set('\famework_sys', $famework);

$db = Famework_Registry::getDb();

Log::init();
