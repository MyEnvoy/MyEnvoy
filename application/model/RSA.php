<?php

class RSA {

    public static function validatePublicKey($key) {
        if (strpos($key, '-----BEGIN PUBLIC KEY-----') === 0) {
            return TRUE;
        }

        return FALSE;
    }

}
