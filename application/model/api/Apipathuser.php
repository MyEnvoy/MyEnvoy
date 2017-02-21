<?php

namespace Api;

class ApiPathUser extends Apipath {

    private $_action = NULL;
    private $_userGid = NULL;
    private $_userId = NULL;

    public function pathToAttr($pathParts) {
        $this->_action = isset($pathParts[0]) ? $pathParts[0] : NULL;
        $this->_userGid = isset($pathParts[1]) ? $pathParts[1] : NULL;

        if (ctype_digit(strval($this->_userGid))) {
            // got id, not gid
            $this->_userId = (int) $this->_userGid;
            $this->_userGid = NULL;
        }
    }

    protected function executeCall() {
        $res = new \stdClass();

        $action = $this->_action . 'Action';

        if (method_exists($this, $action)) {
            $res = $this->$action();
        } else {
            throw new \Exception('API Endpoint not found.', \Errorcode::API_ENDPOINT_NOT_FOUND);
        }

        return $res;
    }

    private function loginAction() {
        $this->throwExceptionOnTooLongPath(1);
        $this->hasToBePost();
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
        $res->gid = $user->getGid();
        $res->username = $user->getName();
        $res->displayname = $user->getDisplayName();
        $res->email = $user->getEmail();

        return $res;
    }

    private function infoAction() {
        $this->throwExceptionOnTooLongPath(2);
        $user = \Currentuser::getEnsuredLoggedInUserByJwt($this->getBearer());

        if ($this->_userGid !== NULL) {
            $user = \Otheruser::getByGid($this->_userGid, $user->getId());
        }

        if ($this->_userId !== NULL) {
            $user = \Otheruser::getLocalById($this->_userId, $user->getId());
        }

        $res = new \stdClass();

        if ($user === NULL) {
            $res->errorCode = \Errorcode::API_NOT_FOUND;
            $res->error = 'User not found.';
            return $res;
        }

        return $user;
    }

    private function friendsAction() {
        $this->throwExceptionOnTooLongPath(1);
        $user = \Currentuser::getEnsuredLoggedInUserByJwt($this->getBearer());

        $res = new \stdClass();

        $res->friends = array();

        foreach ($user->getMyFriends() as $friend) {
            $friend->addJsonData('connection', $user->getConnectionWith($friend));
            $res->friends[] = $friend;
        }

        $res->count = count($res->friends);

        return $res;
    }

    private function followersAction() {
        $this->throwExceptionOnTooLongPath(1);
        $user = \Currentuser::getEnsuredLoggedInUserByJwt($this->getBearer());

        $res = new \stdClass();

        $res->followers = array();

        foreach ($user->getMyFollowers() as $follower) {
            $follower->addJsonData('connection', $user->getConnectionWith($follower));
            $follower->addJsonData('groups', $follower->groupsImIn($user));
            $res->followers[] = $follower;
        }

        $res->count = count($res->followers);

        return $res;
    }

}
