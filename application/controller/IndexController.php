<?php

use Famework\LaCodon\Param\Paramhandler;
use Famework\Request\Famework_Request;

class IndexController extends Controller {

    private $_paramHandler;

    public function init() {
        parent::init();
        $this->_paramHandler = new Paramhandler();
    }

    public function indexAction() {
        $this->_view->title('Startseite');
        $this->_view->hint = $this->_paramHandler->getInt('stat', FALSE, 10, 13);

        // redirect logged in users
        if (Currentuser::getEnsureLoggedInUser(FALSE) !== NULL) {
            Famework_Request::redirect('/' . APPLICATION_LANG . '/dashboard/index');
        }
    }

    public function loginDoAction() {
        $this->_paramHandler->bindMethods(Paramhandler::POST);

        $name = $this->_paramHandler->getValue('name', TRUE, 3, 40);
        $pwd = $this->_paramHandler->getValue('pwd', TRUE, 8);

        $name = strtolower($name);

        $user = Currentuser::getUserFromLogin($name, $pwd);

        if ($user === NULL || !($user instanceof Currentuser)) {
            Famework_Request::redirect('/' . APPLICATION_LANG . '/?stat=' . RegisterController::LOGIN_ERROR);
        }

        $user->generateAuthSession();

        Famework_Request::redirect('/' . APPLICATION_LANG . '/dashboard/index');
    }

    /**
     * @todo REMOVE
     */
    public function apcAction() {
        apc_clear_cache();
        Famework_Request::redirect('/de');
    }

}
