<?php

abstract class User {

    protected $_username;
    protected $_email;
    
    public static function generatePasswordHash($pwd, $salt) {
        return hash_pbkdf2('sha256', $pwd, $salt, 1000, 64);
    }

}
