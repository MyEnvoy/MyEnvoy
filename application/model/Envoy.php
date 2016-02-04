<?php

use Famework\Registry\Famework_Registry;

class Envoy {

    /**
     * Get an envoy from the local db
     * @param string $gid
     * @return \Envoy A already known envoy
     */
    public static function getByGid($gid) {
        $stm = Famework_Registry::getDb()->prepare('SELECT * FROM hosts WHERE gid = ? LIMIT 1');
        $stm->execute(array($gid));

        $data = $stm->fetch();
        if (empty($data)) {
            return NULL;
        }

        $envoy = new Envoy();
        $envoy->setDomain($data['domain']);
        $envoy->setVerified($data['verified']);
        $envoy->setGid($data['gid']);

        return $envoy;
    }

    /**
     * Checks whether an envoy has a local db entry
     * @param string $gid
     * @return boolean
     */
    public static function isKnownHost($gid) {
        $stm = Famework_Registry::getDb()->prepare('SELECT domain FROM hosts WHERE gid = ? LIMIT 1');
        $stm->execute(array($gid));

        $data = $stm->fetchAll();

        if (count($data) === 0) {
            return FALSE;
        }

        return TRUE;
    }

    public static function getByDomain($domain, $import = TRUE) {
        $domain = Security::getRealEnvoyDomain($domain);
        $gid = self::calculateGid($domain);
        $envoy = self::getByGid($gid);

        if ($envoy === NULL && $import === TRUE) {
            // import enovoy
            $communicator = new Envoycommunicator($domain);
            if ($communicator->importEnvoy() === TRUE) {
                $envoy = self::getByGid($gid);
            } else {
                return NULL;
            }
        }

        return $envoy;
    }

    /**
     * Calculate the unique gid for an envoy domain
     * @param string $domain
     * @return string
     */
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

    /**
     * @var Envoycommunicator
     */
    private $_com = NULL;

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

            $stm = $this->_db->prepare('INSERT INTO hosts (gid, domain, verified) VALUES (?,?,0)');
            $stm->execute(array($this->getGid(), $this->getDomain()));
            $this->_com = new Envoycommunicator($domain);
        }
    }

    public function getGid() {
        return $this->_gid;
    }

    public function getDomain() {
        return $this->_domain;
    }

    public function isVerified() {
        return $this->_verified;
    }

    public function setGid($gid) {
        if ($this->_gid === NULL) {
            $this->_gid = $gid;
        }
    }

    public function setDomain($domain) {
        if ($this->_domain === NULL) {
            $this->_domain = Security::getRealEnvoyDomain($domain);
            $this->_com = new Envoycommunicator($this->_domain);
        }
    }

    public function setVerified($verified) {
        if ($this->_verified === NULL) {
            $this->_verified = (bool) $verified;
        }
    }

    public function importUser($user_gid) {
        $meta = $this->_com->getUserMeta($user_gid);

        if ($meta !== NULL && !empty($meta)) {
            $stm = $this->_db->prepare('INSERT INTO user (gid, name, status, host_gid, pub_key) VALUES (:gid, :name, :status, :host_gid, :pub_key)');
            foreach ($meta as $key => $value) {
                $stm->bindParam(':' . $key, $value);
            }
            $stm->execute();
        }
    }

}
