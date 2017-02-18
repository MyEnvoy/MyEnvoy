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
        $res->youarehere = 'login';
        return $res;
    }

}
