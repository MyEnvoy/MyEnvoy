<?php

use Famework\LaCodon\Param\Paramhandler;
use Famework\Request\Famework_Request;

class RegisterController extends Controller {

    private $_paramHandler;

    public function init() {
        parent::init();
        $this->_paramHandler = new Paramhandler();
    }

    public function indexAction() {
        $this->_view->title(t('register_title'));
        $this->_view->addJS(HTTP_ROOT . 'js/jquery-2.1.4.min.js');
        $this->_view->addJS(HTTP_ROOT . 'js/popover.min.js');
        $this->_view->addJS(HTTP_ROOT . 'js/picturepreview.js');
    }

    public function registerDoAction() {
        Famework_Request::redirect('/' . APPLICATION_LANG);
    }

}
