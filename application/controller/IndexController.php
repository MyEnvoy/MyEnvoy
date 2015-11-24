<?php

use Famework\LaCodon\Param\Paramhandler;
use Famework\Request\Famework_Request;

class IndexController extends Controller {

    private $_paramHandler;

    public function init() {
        parent::init();
        $this->_paramHandler = new Paramhandler();
    }

    public function indexAction() {
        $this->_view->title('Startseite');
    }
    
    /**
     * @todo REMOVE
     */
    public function apcAction() {
        apc_clear_cache();
        Famework_Request::redirect('/de');
    }

}
