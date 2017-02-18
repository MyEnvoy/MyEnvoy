<?php

use Famework\Controller\Famework_Controller;
use Famework\Registry\Famework_Registry;
use Api\ApiPathUser;

/**
 * The Controller for the Api
 *
 * @version 1.x
 * @author Fabi
 */
class Apiv1Controller extends Famework_Controller {

    const CURRENT_VERSION = '1.0.0';

    private $_responseObject;

    public function init() {
        $this->_responseObject = new stdClass();
    }

    public function versionAction() {
        $this->_responseObject->version = self::CURRENT_VERSION;
        $this->printOutput();
    }

    public function userAction() {
        $apiPath = Famework_Registry::get('\famework_sys')->getRequestParam('path');

        $userPath = new ApiPathUser();
        $this->_responseObject = $userPath->handlePath($apiPath);

        $this->printOutput();
    }

    private function printOutput() {
        $this->_view->turnLayoutOff();
        $this->_view->ignoreView();
        if (empty($this->_responseObject)) {
            $this->_responseObject = new stdClass();
        }

        echo json_encode($this->_responseObject, JSON_PRETTY_PRINT);
    }

}
