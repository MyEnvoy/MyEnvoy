<?php

use Famework\Registry\Famework_Registry;

class Envoycommunicator {

    const TIMEOUT = 4;
    const URL_GET_PUBKEY = 'http://%s/federation/getpubkey';

    /**
     * @var PDO 
     */
    private $_db;
    private $_domain;
    private $_curl;

    public function __construct($domain) {
        $this->_db = Famework_Registry::getDb();
        $this->_domain = Security::getRealEnvoyDomain($domain);
    }

    public function getPubKey() {
        $url = sprintf(self::URL_GET_PUBKEY, $this->_domain);

        $this->initCurl($url);

        if ($this->getInfo(CURLINFO_HTTP_CODE) === 200) {
            $result = json_decode($this->fetchCurl());
            $pub_key = $result->pub_key;
            $domain = Security::getRealEnvoyDomain($result->host);
            if ($domain === $this->_domain && Rsa::validatePublicKey($pub_key) === TRUE) {
                $stm = $this->_db->prepare('INSERT INTO hosts (gid, domain, pub_key) VALUES (?, ?, ?)');
                $stm->execute(array(Envoy::calculateGid($this->_domain), $this->_domain, $pub_key));

                $this->finishCurl();
                return $pub_key;
            }
        }

        $this->finishCurl();
        return NULL;
    }

    private function initCurl($url) {
        $this->_curl = curl_init();
        curl_setopt($this->_curl, CURLOPT_URL, $url);
        curl_setopt($this->_curl, CURLOPT_FOLLOWLOCATION, FALSE);
        curl_setopt($this->_curl, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($this->_curl, CURLOPT_TIMEOUT, self::TIMEOUT);
    }

    private function postCurl($data) {
        curl_setopt($this->_curl, CURLOPT_POST, true);
        curl_setopt($this->_curl, CURLOPT_POSTFIELDS, $data);
    }

    private function fetchCurl() {
        return curl_exec($this->_curl);
    }

    private function getInfo($type) {
        return curl_getinfo($this->_curl, $type);
    }

    private function finishCurl() {
        curl_close($this->_curl);
    }

}
