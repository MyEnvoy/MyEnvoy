<?php

use Famework\Registry\Famework_Registry;

class Envoy {

    public static function getByGid($gid) {
        $stm = Famework_Registry::getDb()->prepare('SELECT * FROM hosts WHERE gid = ? LIMIT 1');
        $stm->execute(array($gid));

        $data = $stm->fetch();
        if (empty($data)) {
            return NULL;
        }

        $envoy = new Envoy();
        $envoy->setPub_key($data['pub_key']);
        $envoy->setDomain($data['domain']);
        $envoy->setVerified($data['verified']);
        $envoy->setGid($data['gid']);

        return $envoy;
    }

    public static function isKnownHost($gid) {
        $stm = Famework_Registry::getDb()->prepare('SELECT domain FROM hosts WHERE gid = ? LIMIT 1');
        $stm->execute(array($gid));

        $data = $stm->fetchAll();

        if (count($data) === 0) {
            return FALSE;
        }

        return TRUE;
    }

    public static function calculateGid($domain) {
        $domain = Security::getRealEnvoyDomain($domain);
        return hash('sha512', $domain . '@myenvoy');
    }

    /**
     * @var PDO
     */
    private $_db;
    private $_gid = NULL;
    private $_pub_key = NULL;
    private $_domain = NULL;
    private $_verified = NULL;

    public function __construct($domain = NULL, $pub_key = NULL) {
        $this->_db = Famework_Registry::getDb();
        if ($domain !== NULL && $pub_key !== NULL) {
            // create new host entry
            $this->setDomain($domain);
            $this->setGid(self::calculateGid($this->getDomain()));

            if (self::isKnownHost($this->getGid()) === TRUE) {
                throw new Exception('Host is already a known host in database!', Errorcode::ENVOY_CANT_OVERWRITE_KNOWN_HOST);
            }

            $this->setVerified(FALSE);
            $this->setPub_key($pub_key);

            $stm = $this->_db->prepare('INSERT INTO hosts (gid, domain, pub_key, verified) VALUES (?,?,?)');
            $stm->execute(array($this->getGid(), $this->getDomain(), $this->getPub_key()));
        }
    }

    public function getGid() {
        return $this->_gid;
    }

    public function getPub_key() {
        return $this->_pub_key;
    }

    public function getDomain() {
        return $this->_domain;
    }

    public function isVerified() {
        return $this->_verified;
    }

    public function setGid($gid) {
        $this->_gid = $gid;
    }

    public function setPub_key($pub_key) {
        if (Rsa::validatePublicKey($pub_key) === FALSE) {
            throw new Exception('Incorrect Public Key format!', Errorcode::ENVOY_WRONG_PUBKEY_FORMAT);
        }
        $this->_pub_key = $pub_key;
    }

    public function setDomain($domain) {
        $this->_domain = Security::getRealEnvoyDomain($domain);
    }

    public function setVerified($verified) {
        $this->_verified = (bool) $verified;
    }

}
