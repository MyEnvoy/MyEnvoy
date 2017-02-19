<?php

namespace Api;

class ApiPathUser extends Apipath {

    private $_action = NULL;

    public function pathToAttr($pathParts) {
        $this->_action = isset($pathParts[0]) ? $pathParts[0] : NULL;
    }

    protected function executeCall() {
        $res = new \stdClass();

        switch ($this->_action) {
            case 'login':
                $res = $this->loginAction();
                break;
            default:
                throw new \Exception('API Endpoint not found.', \Errorcode::API_ENDPOINT_NOT_FOUND);
        }

        return $res;
    }

    private function loginAction() {
        $this->throwExceptionOnTooLongPath(1);
        $res = new \stdClass();

        try {
            $name = strtolower($this->_paramHandler->getValue('name', TRUE, 3, 40));
            $pwd = $this->_paramHandler->getValue('pwd', TRUE, 8);
        } catch (\Exception $e) {
            $res->error = 'wrong_credentials';
            return $res;
        }

        $idbyname = \Currentuser::getIdByName($name);

        // check against brute force attack
        if ($idbyname !== NULL && \Userinfo::isAccountLocked($idbyname, \Userinfo::MESSAGE_API_LOGIN_FAIL) === TRUE) {
            \Userinfo::log($idbyname, \Userinfo::MESSAGE_API_LOGIN_BLOCKED);
            $res->error = 'blocked';
            return $res;
        }

        $user = \Currentuser::getUserFromLogin($name, $pwd);

        if ($user === NULL || !($user instanceof \Currentuser)) {
            if ($idbyname !== NULL) {
                \Userinfo::log($idbyname, \Userinfo::MESSAGE_API_LOGIN_FAIL);
            }
            $res->error = 'wrong_credentials';
            return $res;
        }

        if ($idbyname !== NULL) {
            \Userinfo::log($idbyname, \Userinfo::MESSAGE_API_LOGIN_SUCCESS);
        }

        $jwt = new Jwt();

        $payload = new \stdClass();
        $payload->uid = $user->getId();

        $res->jwt = $jwt->encode($payload);
        $res->username = $user->getName();
        $res->displayname = $user->getDisplayName();
        $res->email = $user->getEmail();

        return $res;
    }

}
