<?php

use Famework\Controller\Famework_Controller;

abstract class Controller extends Famework_Controller {

    protected $_session;

    public function init() {
        $this->_view->setFrameController(new FrameController($this->_view));
    }

}
