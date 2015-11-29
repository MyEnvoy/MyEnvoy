<?php

use Famework\Registry\Famework_Registry;

class Newuser extends User {

    const EMAIL_USED = 2;
    const NAME_USED = 3;

    private $_password = NULL;

    // Singleton pattern
    private function __construct($name, $email) {
        $this->_username = $name;
        $this->_email = $email;
    }

    /**
     * Get instance of Newuser
     * @param string $name The username
     * @param string $email The <b>checked</b> mail address
     * @return Newuser if name and email still unused else int
     */
    public static function initUserIfPossible($name, $email) {
        $db = Famework_Registry::getDb();
        $smt = $db->prepare('SELECT name FROM user WHERE name = :name OR email = :email LIMIT 1');
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
            throw new Exception('Newuser::register() misused!');
        }

        $name = $this->_username;
        $email = $this->_email;

        // use 32 bit salt
        $salt = bin2hex(mcrypt_create_iv(32, MCRYPT_DEV_URANDOM));
        // generate password hash
        $pwdAsHash = hash_pbkdf2('sha256', $this->_password, $salt, 1000, 64);
        // generate activation hash
        $hash = bin2hex(mcrypt_create_iv(32, MCRYPT_DEV_URANDOM));

        $db = Famework_Registry::getDb();
        $stm = $db->prepare('INSERT INTO user (name, email, pwd, salt, hash) VALUES (:name, :email, :pwd, :salt, :hash)');
        $stm->bindParam(':name', $name);
        $stm->bindParam(':email', $email);
        $stm->bindParam(':pwd', $pwdAsHash);
        $stm->bindParam(':salt', $salt);
        $stm->bindParam(':hash', $hash);
        $stm->execute();
    }

}
