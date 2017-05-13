<?php

use Famework\Registry\Famework_Registry;

class Newuser extends User {

    const EMAIL_USED = 2;
    const NAME_USED = 3;

    private $_password = NULL;
    private $_username = NULL;

    // Singleton pattern
    private function __construct($name, $email) {
        $this->_username = $name;
        $this->_email = $email;
    }

    /**
     * Get instance of Newuser
     * @param string $name The <b>checked</b> username
     * @param string $email The <b>checked</b> mail address
     * @return Newuser if name and email still unused else int
     */
    public static function initUserIfPossible($name, $email) {
        $db = Famework_Registry::getDb();
        $smt = $db->prepare('SELECT name FROM user '
                . 'JOIN user_data ud ON ud.user_id = user.id '
                . 'WHERE name = :name OR email = :email LIMIT 1');
        $smt->bindParam(':name', $name);
        $smt->bindParam(':email', $email);
        $smt->execute();

        foreach ($smt->fetchAll() as $row) {
            // username or email is already used
            if ($row['name'] === $name) {
                return self::NAME_USED;
            } else {
                return self::EMAIL_USED;
            }
        }

        $user = new Newuser($name, $email);
        return $user;
    }

    public static function activate($name, $hash) {
        $db = Famework_Registry::getDb();
        $stm = $db->prepare('SELECT id FROM user '
                . 'JOIN user_data ud ON ud.user_id = user.id '
                . 'WHERE name = :name AND hash = :hash LIMIT 1');
        $stm->bindParam(':name', $name);
        $stm->bindParam(':hash', $hash);
        $stm->execute();

        $uid = NULL;
        foreach ($stm->fetchAll() as $row) {
            $uid = $row['id'];
        }

        if ($uid === NULL) {
            return FALSE;
        }

        $upd = $db->prepare('UPDATE user_data SET activated = 1, hash = NULL WHERE user_id = :id');
        $upd->bindParam(':id', $uid, PDO::PARAM_INT);
        $upd->execute();

        Userinfo::log($uid, Userinfo::MESSAGE_ACTIVATE_ACCOUNT);

        return TRUE;
    }

    public static function createProsodyAccount($name) {
        $user = Otheruser::getLocalByName($name, NULL);
        $uid = $user->getId();

        $stm = Famework_Registry::getDb()->prepare('SELECT xmpp_pwd FROM user_data WHERE user_id = ?');
        $stm->execute(array($uid));

        $xmpp_pwd = $stm->fetch()['xmpp_pwd'];

        $prosody = new Prosody();
        if ($prosody->createUser($user->getName() . '@' . Server::getMyHost(), $xmpp_pwd)) {
            Userinfo::log($uid, Userinfo::MESSAGE_CREATE_PROSODY_ACCOUNT);
            return TRUE;
        }

        Log::err('FATAL: Failed to create Prosody account for user #' . $uid);

        return FALSE;
    }

    public static function validatePassword($pwd, $name) {
        // https://www.youtube.com/watch?v=zUM7i8fsf0g
        // prevents from worst pattern
        if (preg_match("/^([A-Z][a-z]+[0-9]+)$/", $pwd)) {
            return FALSE;
        }

        // name shouldn't be part of password
        if (strpos($pwd, $name) !== FALSE) {
            return FALSE;
        }

        // MyEnvoy shouldn't be part of password
        if (strpos(strtolower($pwd), 'myenvoy') !== FALSE) {
            return FALSE;
        }

        // not only digits
        if (ctype_digit($pwd) === TRUE) {
            return FALSE;
        }

        return TRUE;
    }

    /**
     * Set password if both are equals
     * @param string $pwd The password
     * @param string $pwdrepeat The repeated password
     * @return boolean TRUE if passwords are equals
     */
    public function setPassword($pwd, $pwdrepeat) {
        if ($pwd !== $pwdrepeat) {
            return FALSE;
        }

        $this->_password = $pwd;
        return TRUE;
    }

    /**
     * Finally save user to DB and send verification e-mail
     * @throws Exception
     */
    public function register() {
        if ($this->_password === NULL) {
            throw new Exception('Newuser::register() misused!', Errorcode::NEWUSER_REGISTER_MISUSED);
        }

        $name = $this->_username;
        $email = $this->_email;

        // use 32 bit salt
        $salt = bin2hex(mcrypt_create_iv(32, MCRYPT_DEV_URANDOM));
        // generate password hash
        $pwdAsHash = self::generatePasswordHash($this->_password, $salt);
        // generate activation hash
        $hash = bin2hex(mcrypt_create_iv(32, MCRYPT_DEV_URANDOM));
        // generate gid
        $gid = User::generateGid($name, Server::getMyHost());
        // generate public and private key
        $keypair = Rsa::getNewKeyPair(User::generatePrivKeyPwd($pwdAsHash));
        $priv_key_enc = $keypair[RSA::RSA_PRIV_KEY];
        $pub_key = $keypair[RSA::RSA_PUB_KEY];

        $db = Famework_Registry::getDb();
        $stm = $db->prepare('INSERT INTO user (name, gid, pub_key) VALUES (:name, :gid, :pub_key)');
        $stm->bindParam(':name', $name);
        $stm->bindParam(':gid', $gid);
        $stm->bindParam(':pub_key', $pub_key);
        $stm->execute();

        // get new user's ID
        $idstm = $db->prepare('SELECT id FROM user WHERE name = ? AND gid = ?');
        $idstm->execute(array($name, $gid));
        $myid = (int) $idstm->fetch()['id'];

        $xmpp_pwd = hash('sha512', uniqid('xmpp', TRUE));

        $stm1 = $db->prepare('INSERT INTO user_data (user_id, email, pwd, salt, hash, priv_key_enc, xmpp_pwd) VALUES (?, ?, ?, ?, ?, ?, ?)');
        $stm1->execute(array(
            $myid, $email, $pwdAsHash, $salt, $hash, $priv_key_enc, $xmpp_pwd
        ));

        // setup dependencies
        $this->createDependencies($myid);

        // send activation mail
        $this->sendActivationEmail($email, $name, $hash);

        Userinfo::log($myid, Userinfo::MESSAGE_REGISTER);

        return $myid;
    }

    private function createDependencies($myid) {
        $this->createDefaultGroup($myid);
    }

    private function createDefaultGroup($myid) {
        $stm = Famework_Registry::getDb()->prepare('INSERT INTO user_groups (user_id, name, isdefault) VALUES (:id, :name, 1)');
        $stm->bindParam(':id', $myid, PDO::PARAM_INT);
        $groupname = t('user_defaultgroup_name');
        $stm->bindParam(':name', $groupname);
        $stm->execute();
    }

    private function sendActivationEmail($email, $name, $hash) {
        $mail = new Email();
        $mail->setTo($email);
        $hashlink = Server::getRootLink() . APPLICATION_LANG . '/register/activate/?hash=' . urlencode($hash) . '&name=' . urlencode($name);
        $message = sprintf(t('register_mail_body'), $name, $hashlink);
        $mail->send(t('register_mail_subject'), $message);
    }

    public function setPicture($htmlname, $userid) {
        try {
            $pic = Picture::getFromUpload($htmlname);
            if ($pic === NULL) {
                return FALSE;
            }
            $pic->makeProfilePics($userid);
            $pic->remove();
        } catch (Exception $e) {
            // catch if no picture was uploaded
            if ($e->getCode() !== Errorcode::PICTURE_DISALLOWED_OPERATION) {
                throw $e;
            }
        }
    }

    protected function getPicturePath($size) {
        return NULL;
    }

    public function getPictureUrl($size) {
        return NULL;
    }

    public function countFollowers() {
        return NULL;
    }

    public function countPosts() {
        return NULL;
    }

    public function getFullQualifiedName() {
        return NULL;
    }

    public function getHost() {
        return NULL;
    }

    public function getId() {
        return NULL;
    }

    public function getName() {
        return $this->_username;
    }

    public function getStatus() {
        return NULL;
    }

}
