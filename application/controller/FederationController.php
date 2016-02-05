<?php

use Famework\Registry\Famework_Registry;
use Famework\LaCodon\Param\Paramhandler;

class FederationController extends Controller {

    private $_paramHandler;

    public function init() {
        parent::init();
        $this->_paramHandler = new Paramhandler();
    }

    public function getidentityAction() {
        $this->printResult(array('host' => Server::getMyHost()));
    }

    public function getusermetaAction() {
        $this->_paramHandler->bindMethods(Paramhandler::POST);
        $user_gid = $this->_paramHandler->getValue('gid');

        if (strlen($user_gid) !== 128) {
            $this->printResult();
        }

        $otheruser = Otheruser::getLocalByGid($user_gid);
        if ($otheruser === NULL) {
            $this->printResult();
        }

        $data = array('gid' => $otheruser->getGid(),
            'name' => $otheruser->getName(),
            'status' => $otheruser->getStatus(),
            'pub_key' => $otheruser->getPubKey());
        $this->printResult($data);
    }

    private function printResult($data = array()) {
        echo json_encode($data);
        exit();
    }

}
