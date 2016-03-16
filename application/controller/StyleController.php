<?php

class StyleController extends Controller {

    /**
     * @var Currentuser
     */
    private $_user;

    public function init() {
        parent::init();
        $this->_user = Currentuser::auth();
    }

    public function customAction() {
        header('Content-type: text/css');
        $this->_view->turnLayoutOff();
        $css = $this->_user->getSettings()->getCustomCss();

        if ($css === NULL) {
            exit();
        }

        foreach ($css as $selector => $style) {
            echo $selector . ' {';
            echo str_replace(';', ' !important;', $style);
            echo '}' . PHP_EOL;
        }

        exit();
    }

}
