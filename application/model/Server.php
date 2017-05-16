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

        exec('free --si -b', $output);
        if (count($output) >= 3) {
            try {
                $statsTotal = $output[1];
                $statsTotal = explode(' ', $statsTotal);
                $memoryTotal = $statsTotal[4];

                $statsFree = $output[2];
                $statsFree = explode(' ', $statsFree);
                $memoryFree = $statsFree[count($statsFree) - 1];
            } catch (Exception $e) {
                // no result
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
