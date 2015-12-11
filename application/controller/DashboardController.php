<?php

use Famework\LaCodon\Param\Paramhandler;
use Famework\Request\Famework_Request;

class DashboardController extends Controller {

    public function init() {
        parent::init();
        $this->auth();
        $this->_paramHandler = new Paramhandler();
        $this->_view->title('MyEnvoy');
    }

    private function auth() {
        $this->_view->user = Currentuser::getEnsureLoggedInUser(FALSE);

        if ($this->_view->user === NULL) {
            Famework_Request::redirect('/' . APPLICATION_LANG . '/');
        }
    }

    public function indexAction() {
        $this->_view->user->logout();
    }

}
