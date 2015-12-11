<?php

use Famework\Registry\Famework_Registry;
use Famework\Session\Famework_Session;

class Currentuser extends User {

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
        return $this->_id;
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
