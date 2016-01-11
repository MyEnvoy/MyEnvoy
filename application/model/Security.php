<?php

class Security {

    public static function htmloutput($string) {
        return stripslashes(htmlspecialchars($string, ENT_QUOTES | ENT_HTML5));
    }

    public static function trim($str) {
        return preg_replace("/^\\s+|\\s+$/", NULL, $str);
    }

    public static function wbrusername($str, $short = FALSE) {
        if (strlen($str) > 15) {
            if ($short) {
                $str = preg_replace('/^([a-z0-9.]{15})([a-z0-9.]{1,15})([a-z0-9.]{1,})$/iu', '$1<wbr>$2<wbr>$3', $str);
            } else {
                $str = preg_replace('/^([a-z0-9.]{32})([a-z0-9.]{1,})$/iu', '$1<wbr>$2', $str);
            }

            $str = str_replace('.', '.<wbr>', $str);
            $str = str_replace('@', '<wbr>@', $str);
        }

        return $str;
    }

    public static function round($float, $precision, $strout = TRUE) {
        $float = round($float, $precision);
        if (APPLICATION_LANG === 'de' && $strout === TRUE) {
            $float = str_replace('.', ',', $float);
        }
        return $float;
    }

    public static function getRealEnvoyDomain($url) {
        // add http if no protocoll is set
        if (!preg_match('/^(http:\/\/)|^(https:\/\/)/ui', $url)) {
            $url = 'http://' . $url;
        }

        $parts = parse_url($url);
        if (empty($parts)) {
            return NULL;
        }

        // rearrange domain
        $url = (isset($parts['host']) ? $parts['host'] : NULL);

        return $url;
    }

}
