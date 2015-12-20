<?php

use Famework\Registry\Famework_Registry;
use Famework\Session\Famework_Session;
use Famework\Request\Famework_Request;

class Currentuser extends User {

    const PIC_LARGE = 256;
    const PIC_SMALL = 32;

    public static function getUserFromLogin($name, $pwd) {
        $db = Famework_Registry::getDb();
        $stm = $db->prepare('SELECT id, salt, pwd FROM user WHERE name = :name AND activated = 1 LIMIT 1');
        $stm->bindParam(':name', $name);
        $stm->execute();

        $salt = NULL;
        $uid = NULL;
        $dbpwd = NULL;

        foreach ($stm->fetchAll() as $row) {
            $uid = (int) $row['id'];
            $salt = $row['salt'];
            $dbpwd = $row['pwd'];
        }

        if ($uid === NULL) {
            return NULL;
        }

        $inputpwd = self::generatePasswordHash($pwd, $salt);

        if ($inputpwd !== $dbpwd) {
            return NULL;
        }

        return new Currentuser($uid);
    }

    public static function getEnsureLoggedInUser($strict = TRUE) {
        $session = new Famework_Session();
        $session->setNamespace('user');
        $uid = (int) $session->get('uid');

        if (empty($uid)) {
            if ($strict === TRUE) {
                throw new Exception('ACCESS DENIED.', Errorcode::USER_ACCESS_DENIED);
            } else {
                return NULL;
            }
        }

        return new Currentuser($uid);
    }

    public static function auth() {
        $user = self::getEnsureLoggedInUser(FALSE);

        if ($user === NULL) {
            Famework_Request::redirect('/' . APPLICATION_LANG . '/');
        } else {
            return $user;
        }
    }

    /**
     * @var PDO
     */
    private $_db;
    private $_id;
    private $_meta = NULL;

    // Singleton pattern
    private function __construct($id) {
        $this->_db = Famework_Registry::getDb();
        $this->_id = $id;
    }

    public function getId() {
        return (int) $this->_id;
    }

    public function generateAuthSession() {
        $session = new Famework_Session();
        $session->setNamespace('user');
        $session->set('uid', $this->getId());
    }

    public function getName() {
        return $this->getWhatever('name');
    }

    public function getEmail() {
        return $this->getWhatever('email');
    }

    public function getPictureUrl($size) {
        $size = intval($size);
        $filename = Picture::getUserPicName($this->getId(), $size);
        $path = Picture::PROFILEPIC_PATH . $filename;

        if (is_readable($path) === TRUE) {
            return '/' . APPLICATION_LANG . '/upload/userpic/?id=' . $this->getId() . '&size=' . $size;
        }

        return NULL;
    }

    public function loadMeta() {
        $id = $this->_id;
        $stm = $this->_db->prepare('SELECT * FROM user WHERE id = :id LIMIT 1');
        $stm->bindParam(':id', $id);
        $stm->execute();

        $this->_meta = $stm->fetch();

        $this->_email = $this->_meta['email'];
        $this->_username = $this->_meta['name'];
    }

    private function getWhatever($key) {
        if (!isset($this->_meta[$key])) {
            $this->loadMeta();
        }

        return $this->_meta[$key];
    }

    public function logout() {
        Famework_Session::destroySession();
    }

}
