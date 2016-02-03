<?php

use Famework\Registry\Famework_Registry;

class Log {

    private static $_log_normal;
    private static $_log_error;

    public static function init() {
        self::$_log_normal = Famework_Registry::get('\famework_config')->getValue('myenvoy', 'log');
        self::$_log_error = Famework_Registry::get('\famework_config')->getValue('myenvoy', 'log_error');
    }

    public static function info($message) {
        if (!empty(self::$_log_normal)) {
            $message = sprintf('%s [INFO] %s\n', date('Y-m-d H:i:s'), $message);
            self::writeToLog(self::$_log_normal, $message);
        }
    }

    public static function err($message) {
        if (!empty(self::$_log_error)) {
            $message = sprintf('%s [ERROR] %s', date('Y-m-d H:i:s'), $message . PHP_EOL);
            self::writeToLog(self::$_log_error, $message);
        }
    }

    private static function writeToLog($log, $message) {
        $logfile = fopen($log, 'a');
        fwrite($logfile, $message);
        fclose($logfile);
    }

}
