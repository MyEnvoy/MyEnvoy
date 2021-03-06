<?php

class Rsa {

    const RSA_PRIV_KEY = 0;
    const RSA_PUB_KEY = 1;

    public static function validatePublicKey($key) {
        if (strpos($key, '-----BEGIN PUBLIC KEY-----') === 0) {
            return TRUE;
        }

        return FALSE;
    }

    public static function getNewKeyPair($pwd) {
        set_time_limit(60);

        $default_limit = ini_get('memory_limit');
        ini_set('memory_limit', '128M');

        // generate 2048-bit RSA key
        $pkGenerate = openssl_pkey_new(array(
            'private_key_bits' => 2048,
            'private_key_type' => OPENSSL_KEYTYPE_RSA
        ));

        // get the private key
        openssl_pkey_export($pkGenerate, $pkGeneratePrivate, $pwd);
        // get the public key
        $pkGeneratePublic = openssl_pkey_get_details($pkGenerate)['key'];
        // free resources
        openssl_pkey_free($pkGenerate);

        ini_set('memory_limit', $default_limit);

        return array(
            self::RSA_PRIV_KEY => $pkGeneratePrivate,
            self::RSA_PUB_KEY => $pkGeneratePublic);
    }

    /**
     * Sign data with private key
     * @param string $priv_key
     * @param string $pwd The password which was used to encrypt the private key
     * @param string $data
     * @return string base64 encoded hash of $data
     * @throws Exception
     */
    public static function signData($priv_key, $pwd, $data) {
        $hash = hash('sha256', $data);                                  // hash data
        $key = openssl_pkey_get_private($priv_key, $pwd);               // get private key

        if (openssl_private_encrypt($hash, $crypted, $key) !== TRUE) {  // encrypt hash with private key
            throw new Exception('Unable to sign data.', Errorcode::RSA_FAILED_TO_SIGN);
        }

        return base64_encode($crypted);
    }

}
