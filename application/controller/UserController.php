<?php

use Famework\LaCodon\Param\Paramhandler;
use Famework\Request\Famework_Request;
use Famework\Registry\Famework_Registry;

class UserController extends Controller {

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
        // get username from URL via famework
        $famework = Famework_Registry::get('\famework_sys');
        $username = $famework->getRequestParam('username');
        unset($famework);

        if (preg_match('/^[a-z0-9.]{3,40}$/', $username) !== 1) {
            $this->_view->error = TRUE;
        }

        $this->_view->otheruser = Otheruser::getByName($username, $this->_view->user->getId());

        $this->_view->status = NULL;
        if (empty($this->_view->otheruser)) {
            $this->_view->error = TRUE;
        } else {
            $this->_view->title(sprintf(t('user_index_title'), $this->_view->otheruser->getName()));
            $this->_view->posts = $this->_view->otheruser->getPublicPosts();
            $this->_view->status = $this->_view->otheruser->getStatus();
        }

        if (empty($this->_view->status)) {
            $this->_view->status = t('user_index_emptystatus');
        }
    }

}
