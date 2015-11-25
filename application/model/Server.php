<?php

class Server {

    protected $_ip;
    protected $_host;

    public function __construct($ip) {
        $this->_ip = $ip;
    }

    public function getHost() {
        if ($this->_host === NULL) {
            $this->_host = gethostbyaddr($this->_ip);
        }

        return $this->_host;
    }

    public static function getMyHost() {
        $host = $_SERVER['HTTP_HOST'];
        if (empty($host)) {
            return NULL;
        }
        return $host;
    }

}
