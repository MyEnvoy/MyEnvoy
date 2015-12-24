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

    // Singleton pattern
    private function __construct($id) {
        $this->initDb();
        $this->_id = $id;
        $this->loadMeta();
    }

    public function generateAuthSession() {
        $session = new Famework_Session();
        $session->regenerateId(TRUE);
        $session->setNamespace('user');
        $session->set('uid', $this->getId());
    }

    public function getEmail() {
        return $this->getWhatever('email');
    }

    public function getAddDate() {
        return $this->getWhatever('adddate');
    }

    public function getPicturePath($size) {
        $size = intval($size);
        $filename = Picture::getUserPicName($this->getId(), $size);
        return Picture::PROFILEPIC_PATH . $filename;
    }

    public function logout() {
        Famework_Session::destroySession();
    }

    public function getGroupOverview() {
        $stm = $this->_db->prepare('SELECT id, name FROM user_groups WHERE user_id = ? ORDER BY id ASC');
        $stm->execute(array($this->getId()));

        $data = array();

        foreach ($stm->fetchAll() as $row) {
            $data[$row['id']] = $row['name'];
        }

        return $data;
    }

}
