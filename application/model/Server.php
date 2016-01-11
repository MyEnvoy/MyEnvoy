<?php

class Server {

    public static function getMyHost() {
        $host = $_SERVER['HTTP_HOST'];
        if (empty($host)) {
            return NULL;
        }
        return $host;
    }

    public static function getRootLink() {
        $protocoll = 'http://';
        if (APPLICATION_HTTPS === TRUE) {
            $protocoll = 'https://';
        }
        return $protocoll . self::getMyHost() . '/';
    }

    public static function getClientIP() {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }

        if (filter_var($ip, FILTER_VALIDATE_IP)) {
            return $ip;
        }

        return NULL;
    }

}
