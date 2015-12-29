<?php

use Famework\Controller\Famework_Controller;

abstract class Controller extends Famework_Controller {

    protected $_session;

    public function init() {
        $this->_view->setFrameController(new FrameController($this->_view));
        $this->_view->addMeta('viewport', 'width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no');
        $this->_view->addJs(HTTP_ROOT . 'js/fuckkaspersky.js');
    }

}
