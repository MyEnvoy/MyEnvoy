<?php

use Famework\LaCodon\Param\Paramhandler;

class UploadController extends Controller {

    /**
     * @var Currentuser
     */
    private $_user;

    public function init() {
        parent::init();
        $this->_user = Currentuser::auth();
        $this->_paramHandler = new Paramhandler();
    }

    public function userpicAction() {
        $this->_paramHandler->bindMethods(Paramhandler::GET);

        $userid = $this->_paramHandler->getInt('id');
        $size = $this->_paramHandler->getInt('size');
        
        try {
            if ($userid !== $this->_user->getId()) {
                $other = new Otheruser($userid, $this->_user->getId());
                $path = $other->getPicturePath($size);
            } else {
                $path = $this->_user->getPicturePath($size);
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

}
