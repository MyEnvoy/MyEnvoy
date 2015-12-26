<?php

class Security {

    public static function htmloutput($string) {
        return stripslashes(htmlspecialchars($string, ENT_QUOTES | ENT_HTML5));
    }

    public static function trim($str) {
        return preg_replace("/^\\s+|\\s+$/", NULL, $str);
    }

}
