<?php

use Famework\Registry\Famework_Registry;

abstract class User {

    const DB_TABLE = 'user';
    const DB_USER_DATA = 'user_data';

    use Hasmeta;

    public static function generatePasswordHash($pwd, $salt) {
        return hash_pbkdf2('sha256', $pwd, $salt, 1000, 64);
    }

    public static function verifyMailAddress($name, $email) {
        $stm = Famework_Registry::getDb()->prepare('SELECT email FROM user WHERE name = ? AND email = ? AND activated = 1 LIMIT 1');
        $stm->execute(array($name, $email));

        $res = NULL;

        foreach ($stm->fetchAll() as $row) {
            $res = $row['email'];
        }

        return $res;
    }

    public static function resetPwd($hash, $email, $pwd) {
        // use 32 bit salt
        $newsalt = bin2hex(mcrypt_create_iv(32, MCRYPT_DEV_URANDOM));
        // generate pwd hash
        $newpwd = self::generatePasswordHash($pwd, $newsalt);
        // update data
        $stm = Famework_Registry::getDb()->prepare('UPDATE user SET hash = NULL, pwd = ?, salt = ? WHERE email = ? AND hash = ?');
        $stm->execute(array($newpwd, $newsalt, $email, $hash));
        // check data
        $count = (int) $stm->rowCount();
        return ($count === 1 ? TRUE : FALSE);
    }

    public static function generateGid($name, $domain) {
        $domain = Security::getRealEnvoyDomain($domain);
        return hash('sha512', $name . '@' . $domain);
    }

    /**
     * @var PDO
     */
    protected $_db;
    protected $_id;

    protected function initDb() {
        if (!isset($this->_db)) {
            $this->_db = Famework_Registry::getDb();
        }

        return $this->_db;
    }

    public function getId() {
        return (int) $this->_id;
    }

    public function getName() {
        return $this->getWhatever('name');
    }

    public function getFullQualifiedName() {
        $name = $this->getName();
        $host = $this->getHost();

        if ($host === NULL) {
            return $name;
        }

        return $name . '@' . $host->getDomain();
    }

    private $_host = NULL;

    /**
     * @return Envoy
     * @throws Exception
     */
    public function getHost() {
        if ($this->_host === NULL) {
            $host_gid = $this->getWhatever('host_gid');
            if (empty($host_gid)) {
                return Server::getMyHost();
            }

            $stm = $this->_db->prepare('SELECT gid FROM hosts WHERE gid = ? LIMIT 1');
            $stm->execute(array($host_gid));

            $data = $stm->fetch();

            if (empty($data)) {
                throw new Exception('Error in database!', Errorcode::DATABASE_STRUCTURE_ERROR);
            }

            $this->_host = Envoy::getByGid($data['gid']);
        }

        return $this->_host;
    }

    public function getStatus() {
        return $this->getWhatever('status');
    }

    public function getPictureUrl($size) {
        $path = $this->getPicturePath($size);

        if (is_readable($path) === TRUE) {
            return '/' . APPLICATION_LANG . '/upload/userpic/?id=' . $this->getId() . '&size=' . $size;
        }

        return '/img/profile256.png';
    }

    public function countFollowers() {
        $stm = $this->_db->prepare('SELECT count(1) count FROM user_groups grp
                                        JOIN user_groups_members grpmbr ON grpmbr.group_id = grp.id
                                    WHERE grp.user_id = ?
                                    GROUP BY grpmbr.user_id');
        $stm->execute(array($this->getId()));
        $res = $stm->fetch();

        if (empty($res)) {
            return 0;
        }

        return (int) $res['count'];
    }

    public function countPosts() {
        $stm = $this->_db->prepare('SELECT count(1) count FROM user_posts WHERE user_id = ? AND post_id IS NULL');
        $stm->execute(array($this->getId()));
        $res = $stm->fetch();

        if (empty($res)) {
            return 0;
        }

        return (int) $res['count'];
    }

    protected abstract function getPicturePath($size);
}
