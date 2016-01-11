<?php

use Famework\LaCodon\Param\Paramhandler;
use Famework\LaCodon\Param\Exception_Param;
use Famework\Request\Famework_Request;

class RegisterController extends Controller {

    const ERR_EMAIL_INVALID = 1;
    const ERR_NAME_INVALID = 2;
    const ERR_NAME_USED = 3;
    const ERR_EMAIL_USED = 4;
    const ERR_PWD_NOT_EQUALS = 5;
    const ERR_BAD_PASSWORD = 6;
    const RESET_PWD_SUCCESSFUL = 9;
    const SUCCESSFUL = 10;
    const ACTIVATED = 11;
    const NOT_ACTIVATED = 12;
    const LOGIN_ERROR = 13;
    const RESET_PWD_FAIL = 14;
    const LOGIN_BLOCKED = 15;

    private $_paramHandler;

    public function init() {
        parent::init();
        $this->_paramHandler = new Paramhandler();
    }

    public function indexAction() {
        $this->_view->title(t('html_title_register_index'));
        $this->_view->addJS(HTTP_ROOT . 'js/jquery-2.1.4.min.js');
        $this->_view->addJS(HTTP_ROOT . 'js/popover.min.js');
        $this->_view->addJS(HTTP_ROOT . 'js/picturepreview.js');
        // there is a link to YouTube in one of the original lang vars and
        // we don't want to send the users email (in url as GET) to Google ^^
        $this->_view->addMeta('referrer', 'origin');

        $this->_view->hint = $this->_paramHandler->getInt('err', FALSE, 1, 6);
        $this->_view->name = $this->_paramHandler->getValue('name', FALSE);
        $this->_view->email = $this->_paramHandler->getValue('email', FALSE);
    }

    public function registerDoAction() {
        $this->_paramHandler->bindMethods(Paramhandler::POST);

        try {
            $name = $this->_paramHandler->getValue('name', TRUE, 3, 40);
            $pwd = $this->_paramHandler->getValue('pwd', TRUE, 8);
            $pwdrepeat = $this->_paramHandler->getValue('pwdrepeat', TRUE, 8);
            $email = $this->_paramHandler->getValue('email');
        } catch (Exception_Param $e) {
            Famework_Request::redirect('/' . APPLICATION_LANG . '/register/?err=' . self::ERR_EMAIL_INVALID);
        }

        $email = strtolower($email);
        // validate E-Mail
        if (Email::validate($email) !== TRUE) {
            Famework_Request::redirect('/' . APPLICATION_LANG . '/register/?err=' . self::ERR_EMAIL_INVALID);
        }

        $name = strtolower($name);
        // validate username
        if (preg_match('/^[a-z0-9.]{3,40}$/', $name) !== 1) {
            Famework_Request::redirect('/' . APPLICATION_LANG . '/register/?err=' . self::ERR_NAME_INVALID);
        }

        // check if name and email are still unused
        $newuser = Newuser::initUserIfPossible($name, $email);
        if ($newuser === Newuser::EMAIL_USED) {
            Famework_Request::redirect('/' . APPLICATION_LANG . '/register/?err=' . self::ERR_EMAIL_USED . '&name=' . urlencode($name));
        } elseif ($newuser === Newuser::NAME_USED) {
            Famework_Request::redirect('/' . APPLICATION_LANG . '/register/?err=' . self::ERR_NAME_USED . '&email=' . urlencode($email));
        }

        // validate password
        if (Newuser::validatePassword($pwd, $name) !== TRUE) {
            Famework_Request::redirect('/' . APPLICATION_LANG . '/register/?err=' . self::ERR_BAD_PASSWORD . '&name=' . urlencode($name) . '&email=' . urlencode($email));
        }

        // check password
        if ($newuser->setPassword($pwd, $pwdrepeat) !== TRUE) {
            Famework_Request::redirect('/' . APPLICATION_LANG . '/register/?err=' . self::ERR_PWD_NOT_EQUALS . '&name=' . urlencode($name) . '&email=' . urlencode($email));
        }

        // register user
        $userid = $newuser->register();

        // set picture
        $newuser->setPicture('profilepic', $userid);

        Famework_Request::redirect('/' . APPLICATION_LANG . '/?stat=' . self::SUCCESSFUL);
    }

    public function activateAction() {
        $this->_paramHandler->bindMethods(Paramhandler::GET);

        $hash = $this->_paramHandler->getValue('hash');
        $name = $this->_paramHandler->getValue('name');

        if (Newuser::activate($name, $hash) === TRUE) {
            Famework_Request::redirect('/' . APPLICATION_LANG . '/?stat=' . self::ACTIVATED);
        } else {
            Famework_Request::redirect('/' . APPLICATION_LANG . '/?stat=' . self::NOT_ACTIVATED);
        }
    }

}
