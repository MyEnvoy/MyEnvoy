<?php

class Security {

    public static function htmloutput($string) {
        return htmlspecialchars($string, ENT_QUOTES | ENT_HTML5);
    }

}
