<?php

use Famework\LaCodon\Param\Paramhandler;

class ChatController extends Controller {

    public function init() {
        parent::init();
        $this->_view->user = Currentuser::auth();
        $this->_paramHandler = new Paramhandler();
        $this->_view->title(t('chatcontroller_title') . ' | ' . $this->_view->user->getName() . '@MyEnvoy');
        $this->_view->addJS(HTTP_ROOT . 'js/jquery-2.1.4.min.js');
        $this->_view->addJS(HTTP_ROOT . 'js/popover.min.js');
        $this->_view->addJS(HTTP_ROOT . 'js/dropdown.js');
        $this->_view->addJS('https://cdn.socket.io/socket.io-1.4.5.js');
        $this->_view->addJS(HTTP_ROOT . 'js/chat.js');
        $this->_view->addCSS(HTTP_ROOT . APPLICATION_LANG . '/style/custom');
    }

    public function indexAction() {
        
    }

}
