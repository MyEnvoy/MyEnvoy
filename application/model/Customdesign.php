<?php

class Customdesign {

    public static $_selectors = array(
        'body',
        'input',
        'textarea',
        '.btn',
        '.btn.btn_default',
        '.btn.btn_primary',
        '.btn.btn_danger',
        '.btn.btn_success',
        '.btn.btn_follow',
        '.profile_pic',
        '#dashboard_header_container',
        '.dashboard_realsize_container',
        '.dashboard_post_container',
        '.dashboard_post_comments'
    );

    public static function getLangSelectors() {
        $sel = self::$_selectors;
        $langsel = self::makeLangSelector($sel);
        return $langsel;
    }

    public static function makeLangSelector($selector) {
        return str_replace(array('.', '#', '-'), '_', $selector);
    }

}
