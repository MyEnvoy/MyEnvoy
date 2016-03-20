<?php

use Famework\Request\Famework_Request;

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

    public function darkthemeAction() {
        $this->_user->getSettings()->setCustomCss(array('body' => '}body {background-color: #333;color:#e6e6e6;} input, textarea, select {background-color: #292929 ;border-color: #242424 ;color:#e6e6e6;} select {color: #e6e6e6;} input[type=submit]:hover {background-color: #1f1f1f ;}  #dashboard_header_container {background-color: #151515 ;border: none ;}  ul.settings_groups_list {background-color: #333 ;} li.settings_groups_userrow {background-color: #333 ;} tr:nth-of-type(odd) {background-color: #1f1f1f;} td {border: none;}  ul.dropdown_list li a, ul.dropdown_list li span:not([class^=genericon]) {color:#e6e6e6 ;} div.dropdown:before {border: 9px solid #1f1f1f;border-right-color: transparent;border-left-color: transparent;border-top-color: transparent;} div.dropdown:after {border: 10px solid;border-color: #333;border-right-color: transparent;border-left-color: transparent;border-top-color: transparent;}  .alert.alert_success {color: #dff0d8 ;background-color: #3c763d;border-color: #3c763d;} .alert.alert_danger {color: #f2dede ;background-color: #a94442;border-color: #a94442;} .dashboard_post_comments {background-color: rgba(220, 220, 220, 0.1) ;} .dropdown {background-color: #333 ;color:#e6e6e6 ;border-color: #1f1f1f ;} .glowing_post {background-color: #63440B;} .popover {background-color: #333 ;'));
        Famework_Request::redirect('/' . APPLICATION_LANG . '/');
    }

}
