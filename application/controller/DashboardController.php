<?php

use Famework\LaCodon\Param\Paramhandler;
use Famework\Request\Famework_Request;

class DashboardController extends Controller {

    public function init() {
        parent::init();
        $this->_view->user = Currentuser::auth();
        $this->_paramHandler = new Paramhandler();
        $this->_view->title($this->_view->user->getName() . '@MyEnvoy');
        $this->_view->addJS(HTTP_ROOT . 'js/jquery-2.1.4.min.js');
        $this->_view->addJS(HTTP_ROOT . 'js/popover.min.js');
        $this->_view->addJS(HTTP_ROOT . 'js/dropdown.js');
    }

    public function indexAction() {

    }

    public function logoutAction() {
        $this->_view->user->logout();
        Famework_Request::redirect('/' . APPLICATION_LANG . '/');
    }

}
