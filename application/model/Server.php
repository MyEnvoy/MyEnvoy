<?php

use Famework\Famework;
use Famework\Registry\Famework_Registry;

class Server {

    public static function getMyHost() {
        $host = Famework_Registry::get('\famework_config')->getValue('myenvoy', 'host');
        if (empty($host)) {
            $host = $_SERVER['HTTP_HOST'];
            if (empty($host)) {
                return NULL;
            }
        }
        return $host;
    }

    public static function getRootLink() {
        $protocol = 'http://';
        if (APPLICATION_HTTPS === TRUE) {
            $protocol = 'https://';
        }

        $prefix = '';
        if (Famework_Registry::getEnv() !== Famework::ENV_DEV) {
            $prefix = 'www.';
        }

        return $protocol . $prefix . self::getMyHost() . '/';
    }

    public static function getClientIP() {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }

        if (filter_var($ip, FILTER_VALIDATE_IP) !== FALSE) {
            return $ip;
        }

        return NULL;
    }

    public static function getServerMemoryUsage($getPercentage = TRUE) {
        $memoryTotal = NULL;
        $memoryFree = NULL;

        if (is_readable("/proc/meminfo")) {
            $stats = file_get_contents("/proc/meminfo");
            if ($stats !== FALSE) {
                // Separate lines
                $stats = str_replace(array('\r\n', '\n\r', '\r'), PHP_EOL, $stats);
                $stats = explode(PHP_EOL, $stats);
                // Separate values and find correct lines for total and free mem
                foreach ($stats as $statLine) {
                    $statLineData = explode(':', trim($statLine));

                    // Total memory
                    if (count($statLineData) === 2 && trim($statLineData[0]) === "MemTotal") {
                        $memoryTotal = trim($statLineData[1]);
                        $memoryTotal = explode(" ", $memoryTotal);
                        $memoryTotal = $memoryTotal[0];
                        $memoryTotal *= 1000;  // convert from kbytes to bytes
                    }
                    // Free memory
                    if (count($statLineData) === 2 && trim($statLineData[0]) === "MemFree") {
                        $memoryFree = trim($statLineData[1]);
                        $memoryFree = explode(" ", $memoryFree);
                        $memoryFree = $memoryFree[0];
                        $memoryFree *= 1000;  // convert from kbytes to bytes
                    }
                    if ($memoryFree !== NULL && $memoryTotal !== NULL) {
                        break;
                    }
                }
            }
        }

        if (is_null($memoryTotal) || is_null($memoryFree)) {
            return NULL;
        } else {
            if ($getPercentage) {
                return (100 - ($memoryFree * 100 / $memoryTotal));
            } else {
                return array(
                    'total' => $memoryTotal,
                    'free' => $memoryFree,
                );
            }
        }
    }

}
