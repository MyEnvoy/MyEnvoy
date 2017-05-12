<?php

use Famework\Controller\Famework_Controller;

abstract class Controller extends Famework_Controller {

    protected $_session;

    public function init() {
        if (defined('APPLICATION_LANG')) {
            $this->_view->setLang(APPLICATION_LANG);
        }
        $this->_view->setFrameController(new FrameController($this->_view));
        $this->_view->addMeta('viewport', 'width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no');
        $this->_view->addJs(HTTP_ROOT . 'js/fuckkaspersky.js');
        $this->_view->addJS(HTTP_ROOT . 'js/main.js');
    }

    public function includeStdJs() {
        $this->_view->addJS(HTTP_ROOT . 'js/dropdown.js');
        $this->_view->addJS(HTTP_ROOT . 'js/strophe.min.js');
        $this->_view->addJS(HTTP_ROOT . 'js/jquery.cookie.js');
        $this->_view->addJS(HTTP_ROOT . 'js/chat.js');
    }

}
