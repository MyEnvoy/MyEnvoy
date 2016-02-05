<?php

use Famework\Registry\Famework_Registry;

class Envoycommunicator {

    const TIMEOUT = 4;
    const URL_GET_IDENTITY = 'https://%s/federation/getidentity';
    const URL_GET_USER_META = 'https://%s/federation/getusermeta';

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

    /**
     * Checks whether an envoy exists and imports it
     * @return bool TRUE if it exists, else FALSE
     */
    public function importEnvoy() {
        $url = sprintf(self::URL_GET_IDENTITY, $this->_domain);

        $this->initCurl($url);
        $result = $this->fetchCurl();
        $httpcode = $this->getInfo(CURLINFO_HTTP_CODE);

        if ($httpcode === 200) {
            $result = json_decode($result);
            $domain = Security::getRealEnvoyDomain($result->host);
            if ($domain === $this->_domain) {
                $stm = $this->_db->prepare('INSERT INTO hosts (gid, domain) VALUES (?, ?)');
                $stm->execute(array(Envoy::calculateGid($this->_domain), $this->_domain));

                $this->finishCurl();
                return TRUE;
            }
        } else {
            // server gave wrong answer
            $this->finishCurl();
            return FALSE;
        }
    }

    public function getUserMeta($gid) {
        $url = sprintf(self::URL_GET_USER_META, $this->_domain);

        $this->initCurl($url);
        $this->postCurl(http_build_query(array('gid' => $gid)));
        $result = $this->fetchCurl();

        if ($this->getInfo(CURLINFO_HTTP_CODE) === 200) {
            $result = json_decode($result);
            if (!empty($result) && Rsa::validatePublicKey($result->pub_key) === TRUE && $result->gid === $gid) {
                $meta = array();
                $meta['gid'] = $gid;
                $meta['name'] = $result->name;
                $meta['status'] = substr(trim($result->status), 0, 140);
                $meta['host_gid'] = Envoy::calculateGid($this->_domain);
                $meta['pub_key'] = $result->pub_key;
                return $meta;
            }
        }

        return NULL;
    }

    private function initCurl($url) {
        $this->_curl = curl_init();
        curl_setopt($this->_curl, CURLOPT_URL, $url);
        curl_setopt($this->_curl, CURLOPT_FOLLOWLOCATION, FALSE);
        curl_setopt($this->_curl, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($this->_curl, CURLOPT_TIMEOUT, self::TIMEOUT);
        curl_setopt($this->_curl, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($this->_curl, CURLOPT_SSL_VERIFYPEER, TRUE);
        curl_setopt($this->_curl, CURLOPT_ENCODING, 'gzip');
    }

    private function postCurl($data) {
        curl_setopt($this->_curl, CURLOPT_POST, true);
        curl_setopt($this->_curl, CURLOPT_POSTFIELDS, $data);
    }

    private function fetchCurl() {
        $res = curl_exec($this->_curl);
        return $res;
    }

    private function getInfo($type) {
        return curl_getinfo($this->_curl, $type);
    }

    private function finishCurl() {
        curl_close($this->_curl);
    }

}
