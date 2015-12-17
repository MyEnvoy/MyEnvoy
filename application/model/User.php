<?php

use Famework\Registry\Famework_Registry;

abstract class User {

    protected $_username;
    protected $_email;

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

}
