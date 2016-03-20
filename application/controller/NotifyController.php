<?php

use Famework\Request\Famework_Request;
use Famework\LaCodon\Param\Paramhandler;
use Famework\Registry\Famework_Registry;

class NotifyController extends Controller {

    /**
     * @var Currentuser
     */
    private $_user;

    public function init() {
        parent::init();
        $this->_user = Currentuser::auth();
        $this->_paramHandler = new Paramhandler();
    }

    public function redirAction() {
        $notifyId = $this->_paramHandler->getInt('id');

        $notification = new Notification($notifyId);

        $upd = Famework_Registry::getDb()->prepare('UPDATE user_notifications SET rec = 1 WHERE id = ? AND user_id = ?');
        $upd->execute(array($notification->getId(), $this->_user->getId()));

        Famework_Request::redirect($notification->getLink());
    }

}
