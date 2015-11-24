<?php

use Famework\LaCodon\Param\Paramhandler;

class RegisterController extends Controller {

    private $_paramHandler;

    public function init() {
        parent::init();
        $this->_paramHandler = new Paramhandler();
    }

    public function indexAction() {
        $this->_view->title(t('register_title'));
    }

}
