<?php

use Famework\LaCodon\Param\Paramhandler;
use Famework\Request\Famework_Request;
use Famework\Registry\Famework_Registry;

class SettingsController extends Controller {

    const GENERAL_TAB = 1;
    const LOG_TAB = 2;

    public function init() {
        parent::init();
        $this->_view->user = Currentuser::auth();
        $this->_paramHandler = new Paramhandler();
        $this->_view->title(t('settingscontroller_title') . ' | ' . $this->_view->user->getName() . '@MyEnvoy');
        $this->_view->addJS(HTTP_ROOT . 'js/jquery-2.1.4.min.js');
        $this->_view->addJS(HTTP_ROOT . 'js/popover.min.js');
        $this->_view->addJS(HTTP_ROOT . 'js/dropdown.js');
        $this->errorHandling();
    }

    private function errorHandling() {
        $this->_paramHandler->bindMethods(Paramhandler::GET);
        if ($this->_paramHandler->getInt('error', FALSE) !== 0) {
            $this->_view->error = 1;
        }
    }

    public function indexAction() {
        $this->_view->addJS(HTTP_ROOT . 'js/picturepreview.js');
        $this->_view->activeTab = self::GENERAL_TAB;
    }

    public function indexDoAction() {
        $this->_paramHandler->bindMethods(Paramhandler::POST);

        $display_name = $this->_paramHandler->getValue('display_name', FALSE, 3, 40);
        $city = $this->_paramHandler->getValue('city', FALSE, NULL, 100);

        $error = !$this->_view->user->getSettings()->setWeatherCity($city);
        $error = $error || !$this->_view->user->setDisplayName($display_name);

        if ($error === TRUE) {
            Famework_Request::redirect('/' . APPLICATION_LANG . '/settings?error=1');
        } else {
            Famework_Request::redirect('/' . APPLICATION_LANG . '/settings');
        }
    }

    public function pwdDoAction() {
        $this->_paramHandler->bindMethods(Paramhandler::POST);

        $oldpw = $this->_paramHandler->getValue('old_pwd', TRUE, 8);
        $newpw = $this->_paramHandler->getValue('new_pwd', TRUE, 8);
        $rptpw = $this->_paramHandler->getValue('new_pwd_rpt', TRUE, 8);

        if ($rptpw !== $newpw || User::generatePasswordHash($oldpw, $this->_view->user->getSalt()) !== $this->_view->user->getPwdHash()) {
            Famework_Request::redirect('/' . APPLICATION_LANG . '/settings?error=1');
        }

        if (Newuser::validatePassword($newpw, $this->_view->user->getName()) !== TRUE) {
            Famework_Request::redirect('/' . APPLICATION_LANG . '/settings?error=1');
        }

        $hash = 'reset_pwd_' . uniqid();
        $stm = Famework_Registry::getDb()->prepare('UPDATE user_data SET hash = ? WHERE user_id = ? LIMIT 1');
        $stm->execute(array($hash, $this->_view->user->getId()));

        if (User::resetPwd($hash, $this->_view->user->getEmail(), $newpw) === TRUE) {
            Famework_Request::redirect('/' . APPLICATION_LANG . '/settings');
        } else {
            Famework_Request::redirect('/' . APPLICATION_LANG . '/settings?error=1');
        }
    }

    public function logAction() {
        $this->_view->activeTab = self::LOG_TAB;
    }

    public function pictureDoAction() {
        try {
            $pic = Picture::getFromUpload('profilepic');
            if ($pic === NULL) {
                Famework_Request::redirect('/' . APPLICATION_LANG . '/settings?error=1');
            }
            $pic->makeProfilePics($this->_view->user->getId());
            $pic->remove();
        } catch (Exception $e) {
            // catch if no picture was uploaded
            if ($e->getCode() !== Errorcode::PICTURE_DISALLOWED_OPERATION) {
                throw $e;
            }
        }
        Famework_Request::redirect('/' . APPLICATION_LANG . '/settings');
    }

    public function pictureRemoveAction() {
        foreach (array(Currentuser::PIC_LARGE, Currentuser::PIC_SMALL) as $size) {
            try {
                $pic = new Picture();
                $pic->loadPictureFromPath($this->_view->user->getPicturePath($size));
                $pic->remove();
            } catch (Exception $e) {
                // ignore
            }
        }

        Famework_Request::redirect('/' . APPLICATION_LANG . '/settings');
    }

    public function statusDoAction() {
        $this->_paramHandler->bindMethods(Paramhandler::POST);

        $status = $this->_paramHandler->getValue('status', FALSE, 0, 140);

        $this->_view->user->setStatus($status);

        Famework_Request::redirect('/' . APPLICATION_LANG . '/settings');
    }

}
