<?php

use Famework\LaCodon\Param\Paramhandler;

class UploadController extends Controller {

    public function init() {
        parent::init();
        $this->_view->user = Currentuser::auth();
        $this->_paramHandler = new Paramhandler();
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
