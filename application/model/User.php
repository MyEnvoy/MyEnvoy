<?php

use Famework\Registry\Famework_Registry;

abstract class User implements JsonSerializable {

    const DB_TABLE = 'user';
    const DB_USER_DATA = 'user_data';

    use Hasmeta;

    public static function generatePasswordHash($pwd, $salt) {
        return hash_pbkdf2('sha256', $pwd, $salt, 1000, 64);
    }

    public static function generatePrivKeyPwd($pwdHash) {
        $salt = Famework_Registry::get('\famework_config')->getValue('myenvoy', 'unique_salt');

        return hash_pbkdf2('sha256', $pwdHash, $salt, 1000, 64);
    }

    public static function verifyMailAddress($name, $email) {
        $stm = Famework_Registry::getDb()->prepare('SELECT d.email email FROM user u
                                                        JOIN user_data d ON d.user_id = u.id
                                                    WHERE u.name = ? AND d.email = ? AND d.activated = 1 LIMIT 1');
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
        $stm = Famework_Registry::getDb()->prepare('UPDATE user_data SET hash = NULL, pwd = ?, salt = ? WHERE email = ? AND hash = ?');
        $stm->execute(array($newpwd, $newsalt, $email, $hash));
        // check data
        $count = (int) $stm->rowCount();

        if ($count === 1) {
            $stmt = Famework_Registry::getDb()->prepare('SELECT user_id FROM user_data WHERE email = ? LIMIT 1');
            $stmt->execute(array($email));
            Userinfo::log($stmt->fetch()['user_id'], Userinfo::MESSAGE_PWD_CHANGED);

            return TRUE;
        }

        return FALSE;
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

    public function getGid() {
        return $this->getWhatever('gid');
    }

    public function getName() {
        return $this->getWhatever('name');
    }

    public function getPubKey() {
        return $this->getWhatever('pub_key');
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
                return NULL;
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
        $stm = $this->_db->prepare('SELECT count(1) count FROM (
                                        SELECT utg.user_id id FROM user_groups g
                                                JOIN user_to_groups utg ON utg.group_id = g.id
                                        WHERE g.user_id = ? GROUP BY utg.user_id) x LIMIT 1');
        $stm->execute(array($this->getId()));
        $res = $stm->fetch();

        if (empty($res)) {
            return 0;
        }

        return (int) $res['count'];
    }

    public function countPosts() {
        $stm = $this->_db->prepare('SELECT count(1) count FROM (SELECT p.id FROM user_posts p JOIN user_posts_data d ON d.post_id = p.id WHERE p.user_id = ? AND p.post_id IS NULL GROUP BY p.id) x');
        $stm->execute(array($this->getId()));
        $res = $stm->fetch();

        if (empty($res)) {
            return 0;
        }

        return (int) $res['count'];
    }

    protected abstract function getPicturePath($size);

    private $_publicGroupId;

    public function getPublicGroupId() {
        if ($this->_publicGroupId === NULL) {
            $stm = $this->_db->prepare('SELECT MIN(id) id FROM user_groups WHERE user_id = ? LIMIT 1');
            $stm->execute(array($this->getId()));
            $res = $stm->fetch();
            $this->_publicGroupId = (int) $res['id'];
        }

        return $this->_publicGroupId;
    }

    /**
     * Get all groups of this user
     * @return array All groups of the current user <b>array('#ID' => '#NAME')</b>
     */
    public function getGroupOverview($orderByPrio = FALSE) {
        if ($orderByPrio === TRUE) {
            $stm = $this->_db->prepare('SELECT id, name FROM user_groups WHERE user_id = ? ORDER BY prio ASC, id ASC');
        } else {
            $stm = $this->_db->prepare('SELECT id, name FROM user_groups WHERE user_id = ? ORDER BY isdefault DESC, id DESC');
        }
        $stm->execute(array($this->getId()));

        $data = array();

        foreach ($stm->fetchAll() as $row) {
            $data[(int) $row['id']] = $row['name'];
        }

        return $data;
    }

    public function getDisplayName() {
        $name = $this->getWhatever('display_name');
        if (empty($name)) {
            return $this->getFullQualifiedName();
        }
        return $name;
    }
    
    private $_jsonAdditionals = array();

    public function addJsonData($name, $value) {
        $this->_jsonAdditionals[$name] = $value;
    }

    public function jsonSerialize() {
        $res = new stdClass();

        $res->id = $this->getId();
        $res->gid = $this->getGid();
        $res->username = $this->getName();
        $res->displayname = $this->getDisplayName();

        if (!empty($this->_callerID) || $this instanceof Currentuser) {
            $res->pic = array(
                'small' => $this->getPictureUrl(\Currentuser::PIC_SMALL),
                'large' => $this->getPictureUrl(\Currentuser::PIC_LARGE)
            );
            $res->status = $this->getStatus();
        }
        
        foreach ($this->_jsonAdditionals as $key => $value) {
            $res->$key = $value;
        }

        return $res;
    }

}
