<?php

use Famework\LaCodon\Param\Paramhandler;
use Famework\Request\Famework_Request;

class UploadController extends Controller {

    public function init() {
        parent::init();
        $this->auth();
        $this->_paramHandler = new Paramhandler();
    }

    private function auth() {
        $this->_view->user = Currentuser::getEnsureLoggedInUser(FALSE);

        if ($this->_view->user === NULL) {
            Famework_Request::redirect('/' . APPLICATION_LANG . '/');
        }
    }

    public function userpicAction() {
        $this->_paramHandler->bindMethods(Paramhandler::GET);

        $userid = $this->_paramHandler->getInt('id');
        $size = $this->_paramHandler->getInt('size');

        $filename = Picture::getUserPicName($userid, $size);
        $path = Picture::PROFILEPIC_PATH . $filename;

        if (is_readable($path) === TRUE) {
            header('Content-type: image/jpeg');
            imagejpeg(imagecreatefromjpeg($path));
        } else {
            header('HTTP/1.0 404 Not Found', TRUE, 404);
        }
        exit();
    }

}
