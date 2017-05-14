<?php

use Famework\LaCodon\Param\Paramhandler;
use Famework\Controller\Famework_Controller;

class AdminController extends Famework_Controller {

    private $_paramHandler;

    public function init() {
        if (!isset($_SERVER['PHP_AUTH_USER'])) {
            header('HTTP/1.0 401 Unauthorized', TRUE, 401);
            exit;
        }
        $this->_paramHandler = new Paramhandler();
        $this->_view->setFrameController(new AdminframeController($this->_view));
        $this->_view->title('MyEnvoy | Admin');
    }

    public function indexAction() {
        $memUsage = Server::getServerMemoryUsage(FALSE);

        $this->_view->totalMem = $memUsage['total'] / 1024 / 1024;
        $this->_view->memUsagePercentage = (100 - ($memUsage['free'] * 100 / $memUsage['total']));

        $this->setOnlineUserValue();
    }

    private function setOnlineUserValue() {
        $onlineUsers = Admin::countOnlineUsers();
        $this->_view->onlineUsers = $onlineUsers;
        if ($onlineUsers === -1) {
            // server down
            $this->_view->onlineUsers = '<i style="padding: 19px 0px;" class="material-icons large red-text">report_problem</i>';
        }
    }

    public function userAction() {
        $this->_view->userData = Admin::getUserInformation();
    }

    public function prosodyAction() {
        $this->setOnlineUserValue();

        try {
            $prosoy = new Prosody();
            $this->_view->uptime = $prosoy->getServerStartupTime();
        } catch (Exception $e) {
            $this->_view->uptime = '<span class="red-text">DOWN (!)</span>';
        }
    }

    public function logoutDoAction() {
        $this->_view->ignoreView();
        header('Location: http://logout:logout@' . Server::getMyHost() . '/admin/index', TRUE, 302);
    }

}
