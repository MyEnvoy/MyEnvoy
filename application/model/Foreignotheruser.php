<?php

use Famework\Registry\Famework_Registry;

class Foreignotheruser extends Otheruser {

    /**
     * Get an \Foreignotheruser object from database or other envoy if $import = TRUE
     * @param string $name
     * @param string $domain
     * @param int $callerId
     * @param bool $import
     * @return \Foreignotheruser
     */
    public static function getForeignByName($name, $domain, $callerId, $import = FALSE) {
        $gid = User::generateGid($name, $domain);
        $stm = Famework_Registry::getDb()->prepare('SELECT id FROM user WHERE gid = ? AND name = ? AND host_gid IS NOT NULL LIMIT 1');
        $stm->execute(array($gid, $name));
        $res = $stm->fetch();

        if (!empty($res)) {
            return new Foreignotheruser($res['id'], $callerId);
        }
        if ($import === TRUE) {
            $envoy = Envoy::getByDomain($domain, TRUE);
            if ($envoy !== NULL) {
                $envoy->importUser($gid);
                return Foreignotheruser::getByGid($gid, $callerId);
            }
        }

        return NULL;
    }

    /**
     * @var Envoycommunicator
     */
    private $_com;

    public function __construct($id, $callerId) {
        parent::__construct($id, $callerId);
        $this->_com = new Envoycommunicator($this->getHost()->getDomain());
    }

    public function getPictureUrl($size) {
        return parent::getPictureUrl($size);
    }

    public function getPicturePath($size) {
        $path = Picture::PROFILEPIC_FOREIGN_CACHE . $this->getGid() . '-' . $size . '-' . $this->_callerID . '.jpg';

        if (is_readable($path)) {
            return $path;
        }
        
        // TODO: get picture via _com
        
        if (is_readable($path)) {
            return $path;
        }

        return NULL;
    }

}
