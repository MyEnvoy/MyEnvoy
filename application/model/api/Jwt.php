<?php

namespace Api;

/**
 * JSON Webtoken support
 *
 * @author Fabi
 */
class Jwt {

    // duration in seconds
    const JWT_VALID_DURATION = 60 * 60 * 24 * 2;

    private $_payload;
    private $_issuer;
    private $_issuedAt;
    private $_expiresAt;

    public function __construct() {
        $this->_payload = new \stdClass();
        $this->_issuer = \Famework\Registry\Famework_Registry::get('\famework_config')->getValue('myenvoy', 'host');
    }

    /**
     * @param \stdClass $payload
     */
    private function setPayload($payload) {
        $this->_payload = $payload;
        $this->_payload->iss = $this->_issuer;
        $this->_payload->iat = $this->_issuedAt;
        $this->_payload->exp = $this->_expiresAt;
    }

    /**
     * @return \stdClass
     */
    private function getPayload() {
        return $this->_payload;
    }

    /**
     * @param \stdClass $payload
     */
    public function encode($payload) {
        $this->_issuedAt = time();
        $this->_expiresAt = time() + self::JWT_VALID_DURATION;

        $this->setPayload($payload);

        $header = base64_encode(json_encode($this->getHeader()));
        $body = base64_encode(json_encode($this->getPayload()));
        $signature = base64_encode($this->getSignature($header . '.' . $body));

        return $header . '.' . $body . '.' . $signature;
    }

    public function decode($jwt) {
        $jwtParts = explode('.', $jwt);

        if (count($jwtParts) !== 3) {
            throw new \Exception('Invalid JWT.', \Errorcode::API_INVALID_JWT);
        }

        list($headerEnc, $bodyEnc, $signatureEnc) = $jwtParts;
        
        $signature = base64_decode($signatureEnc);
        if (!$this->verify($headerEnc . '.' . $bodyEnc, $signature)) {
            throw new \Exception('Invalid JWT.', \Errorcode::API_INVALID_JWT);
        }

        $header = json_decode(base64_decode($headerEnc), FALSE, 512, JSON_BIGINT_AS_STRING);
        if ($header === NULL ||
                empty($header->alg) ||
                $header->alg !== 'HS256') {
            throw new \Exception('Invalid JWT header.', \Errorcode::API_INVALID_JWT_HEADER);
        }

        $body = json_decode(base64_decode($bodyEnc), FALSE, 512, JSON_BIGINT_AS_STRING);
        if ($body === NULL) {
            throw new \Exception('Invalid JWT payload.', \Errorcode::API_INVALID_JWT_BODY);
        }

        if (!isset($body->iss) ||
                $body->iss !== $this->_issuer ||
                !isset($body->iat) ||
                $body->iat > time()) {
            throw new \Exception('Invalid JWT.', \Errorcode::API_INVALID_JWT);
        }

        if (!isset($body->exp) ||
                $body->exp < time()) {
            throw new \Exception('Expired JWT.', \Errorcode::API_EXPIRED_JWT);
        }
        
        return $body;
    }

    private function verify($data, $signature) {
        $sigFromData = $this->getSignature($data);
        return hash_equals($signature, $sigFromData);
    }

    private function getSignature($data) {
        return hash_hmac('SHA256', $data, $this->getKey(), TRUE);
    }

    private function getHeader() {
        $header = new \stdClass();
        $header->typ = 'JWT';
        $header->alg = 'HS256';

        return $header;
    }

    private function getKey() {
        $key = \Famework\Registry\Famework_Registry::get('\famework_config')->getValue('api', 'myenvoy_jwtkey');

        if (empty($key)) {
            throw new \Exception('No JWT key provided!', \Errorcode::API_EMPTY_JWT_KEY);
        }

        return $key;
    }

}
