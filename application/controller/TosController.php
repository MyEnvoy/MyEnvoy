<?php

class TosController extends Controller {

    public function init() {
        parent::init();
    }

    public function indexAction() {
        $this->_view->title('Terms of Service | MyEnvoy');
    }
    
    public function privacyAction() {
        $this->_view->title('Data privacy | MyEnvoy');
    }

}
