<?php

use Famework\LaCodon\Param\Paramhandler;
use Famework\Request\Famework_Request;
use Famework\Registry\Famework_Registry;

class SettingsController extends Controller {

    const GENERAL_TAB = 1;
    const LOG_TAB = 2;
    const GROUPS_TAB = 3;
    const FRIENDS_TAB = 4;
    const DESIGN_TAB = 5;

    public function init() {
        parent::init();
        $this->_view->user = Currentuser::auth();
        $this->_paramHandler = new Paramhandler();
        $this->_view->title(t('settingscontroller_title') . ' | ' . $this->_view->user->getName() . '@MyEnvoy');
        $this->_view->addJS(HTTP_ROOT . 'js/jquery-2.1.4.min.js');
        $this->_view->addJS(HTTP_ROOT . 'js/popover.min.js');
        $this->_view->addJS(HTTP_ROOT . 'js/dropdown.js');
        $this->errorHandling();
        $this->_view->addCSS(HTTP_ROOT . APPLICATION_LANG . '/style/custom');
    }

    private function errorHandling() {
        $this->_paramHandler->bindMethods(Paramhandler::GET);
        if ($this->_paramHandler->getInt('error', FALSE) !== NULL) {
            $this->_view->error = $this->_paramHandler->getInt('error');
        }
    }

    public function indexAction() {
        $this->_view->addJS(HTTP_ROOT . 'js/picturepreview.js');
        $this->_view->addJS(HTTP_ROOT . 'js/localise.js');
        $this->_view->activeTab = self::GENERAL_TAB;
    }

    public function indexDoAction() {
        $this->_paramHandler->bindMethods(Paramhandler::POST);

        $display_name = $this->_paramHandler->getValue('display_name', FALSE, 3, 40);
        $city = $this->_paramHandler->getValue('city', FALSE, NULL, 100);

        $error = !$this->_view->user->getSettings()->setWeatherCity($city);
        if ($display_name !== $this->_view->user->getName()) {
            $error = $error || !$this->_view->user->setDisplayName($display_name);
        }

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
            Famework_Request::redirect('/' . APPLICATION_LANG . '/settings?error=' . RegisterController::ERR_BAD_PASSWORD);
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

    public function groupsChangeAction() {
        $this->_paramHandler->bindMethods(Paramhandler::POST);

        $groupId = $this->_paramHandler->getValue('group_id');

        if ($this->canEdit($groupId) === FALSE) {
            Famework_Request::redirect('/' . APPLICATION_LANG . '/settings/groups');
        }

        try {
            $pic = Picture::getFromUpload('profilepic');
            if ($pic === NULL) {
                Famework_Request::redirect('/' . APPLICATION_LANG . '/settings/groups/?error=1');
            }
            $pic->makeProfilePics($this->_view->user->getId(), $groupId);
            $pic->remove();
        } catch (Exception $e) {
            // catch if no picture was uploaded
            if ($e->getCode() !== Errorcode::PICTURE_DISALLOWED_OPERATION) {
                throw $e;
            }
        }
        Famework_Request::redirect('/' . APPLICATION_LANG . '/settings/groups');
    }

    public function groupspicRemoveAction() {
        $this->_paramHandler->bindMethods(Paramhandler::GET);

        $groupId = $this->_paramHandler->getValue('id');

        if ($this->canEdit($groupId) === FALSE) {
            Famework_Request::redirect('/' . APPLICATION_LANG . '/settings/groups');
        }

        Group::removePic($groupId, $this->_view->user);

        Famework_Request::redirect('/' . APPLICATION_LANG . '/settings/groups');
    }

    public function statusDoAction() {
        $this->_paramHandler->bindMethods(Paramhandler::POST);

        $status = $this->_paramHandler->getValue('status', FALSE, 0, 140);

        $this->_view->user->setStatus($status);

        Famework_Request::redirect('/' . APPLICATION_LANG . '/settings');
    }

    public function groupsAction() {
        $this->_view->addJS(HTTP_ROOT . 'js/sortable.min.js');
        $this->_view->addJS(HTTP_ROOT . 'js/groups.js');
        $this->_view->addJS(HTTP_ROOT . 'js/picturepreview.js');
        $this->_view->addJS(HTTP_ROOT . 'js/modal.js');
        $this->_view->activeTab = self::GROUPS_TAB;
    }

    public function groupsDoAction() {
        $this->_paramHandler->bindMethods(Paramhandler::POST);

        $usersArray = $this->_paramHandler->getValue('users', FALSE);

        // clear empty groups
        $emptyGroups = array_diff(array_flip($this->_view->user->getGroupOverview()), array_flip_multi($usersArray));
        foreach ($emptyGroups as $groupId) {
            if ($this->canEdit($groupId) === FALSE) {
                continue;
            }

            Group::deleteAllMembers($groupId, $this->_view->user);
            Group::setPrio($groupId, 100, $this->_view->user);
        }

        $prioCount = 1;

        // add/remove members
        foreach ($usersArray as $groupId => $users) {
            if ($this->canEdit($groupId) === FALSE) {
                continue;
            }

            Group::setPrio($groupId, $prioCount, $this->_view->user);
            $prioCount++;

            $users = array_unique($users);

            $currentGroup = array();
            foreach (Group::getMembers($groupId, $this->_view->user) as $mbr) {
                $currentGroup[] = $mbr->getId();
            }

            // diff will contain all user IDs which were not submitted (removed from group)
            $diff = array_diff($currentGroup, $users);

            foreach ($diff as $userId) {
                try {
                    $user = new Otheruser($userId, $this->_view->user);
                    Group::removeMember($groupId, $user, $this->_view->user);
                } catch (Exception $e) {
                    // no such user
                }
            }

            if ($users !== NULL) {
                foreach ($users as $userId) {
                    try {
                        $user = new Otheruser((int) $userId, $this->_view->user->getId());
                        Group::addMember($groupId, $user, $this->_view->user);
                    } catch (Exception $e) {
                        // no such user
                    }
                }
            }
        }

        Famework_Request::redirect('/' . APPLICATION_LANG . '/settings/groups');
    }

    private function canEdit($groupId) {
        if (Group::getOwnerById($groupId, $this->_view->user)->getId() !== $this->_view->user->getId()) {
            // check ownership
            return FALSE;
        }

        if ($this->_view->user->getPublicGroupId() === $groupId) {
            // one can not delete users from the public group
            return FALSE;
        }

        return TRUE;
    }

    public function groupAddAction() {
        $this->_paramHandler->bindMethods(Paramhandler::POST);
        $groupName = $this->_paramHandler->getValue('name', TRUE, 1, Group::MAX_NAME_LENGTH);
        $groupName = Security::trim($groupName);

        Group::create($groupName, $this->_view->user);

        Famework_Request::redirect('/' . APPLICATION_LANG . '/settings/groups');
    }

    public function groupRemoveAction() {
        $this->_paramHandler->bindMethods(Paramhandler::GET);
        $groupId = $this->_paramHandler->getInt('id');

        if ($this->canEdit($groupId) === TRUE) {
            Group::removePic($groupId, $this->_view->user);
            Group::remove($groupId, $this->_view->user);
        }

        Famework_Request::redirect('/' . APPLICATION_LANG . '/settings/groups');
    }

    public function friendsAction() {
        $this->_view->activeTab = self::FRIENDS_TAB;
    }

    public function designAction() {
        $this->_view->addCSS(HTTP_ROOT . 'css/jquery.minicolors.css');
        $this->_view->addJS(HTTP_ROOT . 'js/jquery.minicolors.js');
        $this->_view->activeTab = self::DESIGN_TAB;
    }

    public function designDoAction() {
        $this->_paramHandler->bindMethods(Paramhandler::POST);
        $design = $this->_paramHandler->getValue('design');

        $allowedSels = Customdesign::$_selectors;
        foreach ($design as $selector => $style) {
            if (!in_array($selector, $allowedSels) || strlen($style) > 2000) {
                Famework_Request::redirect('/' . APPLICATION_LANG . '/settings/design?error=1');
            } elseif (empty($style)) {
                unset($design[$selector]);
            }
        }

        $this->_view->user->getSettings()->setCustomCss($design);

        Famework_Request::redirect('/' . APPLICATION_LANG . '/settings/design');
    }

}
