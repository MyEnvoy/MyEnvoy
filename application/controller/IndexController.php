<?php

use Famework\LaCodon\Param\Paramhandler;
use Famework\Request\Famework_Request;
use Famework\Registry\Famework_Registry;

class IndexController extends Controller {

    private $_paramHandler;

    public function init() {
        parent::init();
        $this->_paramHandler = new Paramhandler();
    }

    public function indexAction() {
        $this->_view->title(t('html_title_index_index'));
        $this->_view->hint = $this->_paramHandler->getInt('stat', FALSE, 9, 15);

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

        $idbyname = Currentuser::getIdByName($name);

        // check against brute force attack
        if ($idbyname !== NULL && Userinfo::isAccountLocked($idbyname) === TRUE) {
            Userinfo::log($idbyname, Userinfo::MESSAGE_LOGIN_BLOCKED);
            Famework_Request::redirect('/' . APPLICATION_LANG . '/?stat=' . RegisterController::LOGIN_BLOCKED);
        }

        $user = Currentuser::getUserFromLogin($name, $pwd);

        if ($user === NULL || !($user instanceof Currentuser)) {
            if ($idbyname !== NULL) {
                Userinfo::log($idbyname, Userinfo::MESSAGE_LOGIN_FAIL);
            }
            Famework_Request::redirect('/' . APPLICATION_LANG . '/?stat=' . RegisterController::LOGIN_ERROR);
        }

        $user->generateAuthSession();

        if ($idbyname !== NULL) {
            Userinfo::log($idbyname, Userinfo::MESSAGE_LOGIN_SUCCESS);
        }

        Famework_Request::redirect('/' . APPLICATION_LANG . '/dashboard/index');
    }

    public function resetpwAction() {
        $this->_view->title(t('html_title_index_resetpw'));
        $this->_view->hint = $this->_paramHandler->getInt('stat', FALSE, 1, 1);
    }

    public function resetpwDoAction() {
        $this->_paramHandler->bindMethods(Paramhandler::POST);

        try {
            $name = strtolower($this->_paramHandler->getValue('name', TRUE, 3, 40));
            $email = strtolower($this->_paramHandler->getValue('email'));
        } catch (Exception $e) {
            Famework_Request::redirect('/' . APPLICATION_LANG . '/index/resetpw');
        }

        // validate E-Mail and Name
        if (Email::validate($email) !== TRUE || preg_match('/^[a-z0-9.]{3,40}$/', $name) !== 1) {
            Famework_Request::redirect('/' . APPLICATION_LANG . '/index/resetpw/?stat=1');
        }

        // check if user with given data really exists
        $mailaddr = User::verifyMailAddress($name, $email);

        // send reset mail
        if ($mailaddr !== NULL) {
            // generate activation hash
            $hash = bin2hex(mcrypt_create_iv(32, MCRYPT_DEV_URANDOM));
            // save hash
            $stm = Famework_Registry::getDb()->prepare('UPDATE user SET hash = ? WHERE email = ?');
            $stm->execute(array($hash, $email));
            // send mail
            $mail = new Email();
            $mail->setTo($mailaddr);
            $hashlink = Server::getRootLink() . APPLICATION_LANG . '/index/restpwform/?hash=' . urlencode($hash) . '&email=' . urlencode($mailaddr);
            $message = sprintf(t('resetpw_mail_body'), $name, $hashlink);
            $mail->send(t('resetpw_mail_subject'), $message);
        }

        Famework_Request::redirect('/' . APPLICATION_LANG . '/index/resetpw/?stat=1');
    }

    public function restpwformAction() {
        $this->_view->title(t('html_title_index_resetpwform'));
        $this->_paramHandler->bindMethods(Paramhandler::GET);

        $this->_view->hash = $this->_paramHandler->getValue('hash');
        $this->_view->email = strtolower($this->_paramHandler->getValue('email'));
    }

    public function resetpwformDoAction() {
        $this->_paramHandler->bindMethods(Paramhandler::POST);

        $hash = $this->_paramHandler->getValue('hash');
        $email = $this->_paramHandler->getValue('email');
        $newpw = $this->_paramHandler->getValue('newpw');

        if (User::resetPwd($hash, $email, $newpw) === TRUE) {
            Famework_Request::redirect('/' . APPLICATION_LANG . '/?stat=' . RegisterController::RESET_PWD_SUCCESSFUL);
        } else {
            Famework_Request::redirect('/' . APPLICATION_LANG . '/?stat=' . RegisterController::RESET_PWD_FAIL);
        }
    }

    /**
     * @todo REMOVE
     */
    public function apcAction() {
        apc_clear_cache();
        Famework_Request::redirect('/de');
    }

}
