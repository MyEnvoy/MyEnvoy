<?php

use Famework\LaCodon\Param\Paramhandler;
use Famework\Request\Famework_Request;

class UploadController extends Controller {

    /**
     * @var Currentuser
     */
    private $_user;

    public function init() {
        parent::init();
        $this->_user = Currentuser::getEnsureLoggedInUser(FALSE);
        $this->_paramHandler = new Paramhandler();
    }

    public function userpicAction() {
        $this->_paramHandler->bindMethods(Paramhandler::GET);

        $userid = $this->_paramHandler->getInt('id');
        $size = $this->_paramHandler->getInt('size');

        try {
            if (!empty($this->_user) && $userid !== $this->_user->getId()) {
                $other = new Otheruser($userid, $this->_user->getId());
                $possiblePics = $other->getAllPossiblePicturePaths($size);
                $path = array_pop($possiblePics);
            } elseif (!empty($this->_user)) {
                $path = $this->_user->getPicturePath($size);
            } else {
                $filename = Picture::getUserPicName($userid, $size, 'default');
                $path = Picture::PROFILEPIC_PATH . $filename;
            }
        } catch (Exception $e) {
            $path = NULL;
        }

        if (is_readable($path) === TRUE) {
            header('Content-type: image/jpeg');
            imagejpeg(imagecreatefromjpeg($path));
        } else {
            header('HTTP/1.0 404 Not Found', TRUE, 404);
        }
        // prevent from rendering view
        exit();
    }

    public function grouppicAction() {
        $this->_paramHandler->bindMethods(Paramhandler::GET);

        $groupId = $this->_paramHandler->getInt('id');
        $size = $this->_paramHandler->getInt('size');

        $owner = Group::getOwnerById($groupId, $this->_user);

        if ($owner === NULL || $owner->getId() !== $this->_user->getId()) {
            header('HTTP/1.0 404 Not Found', TRUE, 404);
            exit();
        }

        $path = $this->_user->getPicturePath($size, $groupId);

        if (is_readable($path) === TRUE) {
            header('Content-type: image/jpeg');
            imagejpeg(imagecreatefromjpeg($path));
        } else {
            Famework_Request::redirect('/' . APPLICATION_LANG . '/upload/userpic/?id=' . $this->_user->getId() . '&size=' . $size);
        }

        exit();
    }

}
