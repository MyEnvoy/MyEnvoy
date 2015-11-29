<?php

use Famework\LaCodon\Param\Paramhandler;
use Famework\Request\Famework_Request;

class RegisterController extends Controller {

    const ERR_PWD_NOT_EQUALS = 0;
    const ERR_EMAIL_INVALID = 1;
    const ERR_NAME_USED = 2;
    const ERR_EMAIL_USED = 3;
    const SUCCESSFUL = 4;

    private $_paramHandler;

    public function init() {
        parent::init();
        $this->_paramHandler = new Paramhandler();
    }

    public function indexAction() {
        $this->_view->title(t('register_title'));
        $this->_view->addJS(HTTP_ROOT . 'js/jquery-2.1.4.min.js');
        $this->_view->addJS(HTTP_ROOT . 'js/popover.min.js');
        $this->_view->addJS(HTTP_ROOT . 'js/picturepreview.js');
    }

    public function registerDoAction() {
        $this->_paramHandler->bindMethods(Paramhandler::POST);

        $name = $this->_paramHandler->getValue('name', TRUE, 3, 40);
        $pwd = $this->_paramHandler->getValue('pwd', TRUE, 8);
        $pwdrepeat = $this->_paramHandler->getValue('pwdrepeat', TRUE, 8);
        $email = $this->_paramHandler->getValue('email');

        // validate E-Mail
        if (Email::validate($email) !== TRUE) {
            Famework_Request::redirect('/' . APPLICATION_LANG . '/register/?err=' . self::ERR_EMAIL_INVALID);
        }

        // check if name and email still free
        $newuser = Newuser::initUserIfPossible($name, $email);
        if ($newuser === Newuser::EMAIL_USED) {
            Famework_Request::redirect('/' . APPLICATION_LANG . '/register/?err=' . self::ERR_EMAIL_USED);
        } elseif ($newuser === Newuser::NAME_USED) {
            Famework_Request::redirect('/' . APPLICATION_LANG . '/register/?err=' . self::ERR_NAME_USED);
        }

        // check password
        if ($newuser->setPassword($pwd, $pwdrepeat) !== TRUE) {
            Famework_Request::redirect('/' . APPLICATION_LANG . '/register/?err=' . self::ERR_PWD_NOT_EQUALS);
        }

        // finally register
        $newuser->register();

        Famework_Request::redirect('/' . APPLICATION_LANG . '/?stat=' . self::SUCCESSFUL);
    }

}
