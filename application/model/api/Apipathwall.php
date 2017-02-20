<?php

namespace Api;

class ApipathWall extends Apipath {

    protected function executeCall() {
        $this->throwExceptionOnTooLongPath(1);
        $user = \Currentuser::getEnsuredLoggedInUserByJwt($this->getBearer());

        $res = new \stdClass();

        $res->wall = array();

        foreach ($user->getWall() as $entry) {
            $res->wall[] = $entry;
        }

        return $res;
    }

    public function pathToAttr($pathParts) {
        return;
    }

}
