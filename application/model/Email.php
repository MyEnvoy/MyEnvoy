<?php

class Email {

    // better don't ask
    public static function validate($email) {
        $dot_string = '(?:[A-Za-z0-9!#$%&*+=?^_`{|}~\'\\/-]|(?<!\\.|\\A)\\.(?!\\.|@))';
        $quoted_string = '(?:\\\\\\\\|\\\\"|\\\\?[A-Za-z0-9!#$%&*+=?^_`{|}~()<>[\\]:;@,. \'\\/-])';
        $ipv4_part = '(?:[0-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])';
        $ipv6_part = '(?:[A-fa-f0-9]{1,4})';
        $fqdn_part = '(?:[A-Za-z](?:[A-Za-z0-9-]{0,61}?[A-Za-z0-9])?)';
        $ipv4 = "(?:(?:{$ipv4_part}\\.){3}{$ipv4_part})";
        $ipv6 = '(?:' .
                "(?:(?:{$ipv6_part}:){7}(?:{$ipv6_part}|:))" . '|' .
                "(?:(?:{$ipv6_part}:){6}(?::{$ipv6_part}|:{$ipv4}|:))" . '|' .
                "(?:(?:{$ipv6_part}:){5}(?:(?::{$ipv6_part}){1,2}|:{$ipv4}|:))" . '|' .
                "(?:(?:{$ipv6_part}:){4}(?:(?::{$ipv6_part}){1,3}|(?::{$ipv6_part})?:{$ipv4}|:))" . '|' .
                "(?:(?:{$ipv6_part}:){3}(?:(?::{$ipv6_part}){1,4}|(?::{$ipv6_part}){0,2}:{$ipv4}|:))" . '|' .
                "(?:(?:{$ipv6_part}:){2}(?:(?::{$ipv6_part}){1,5}|(?::{$ipv6_part}){0,3}:{$ipv4}|:))" . '|' .
                "(?:(?:{$ipv6_part}:){1}(?:(?::{$ipv6_part}){1,6}|(?::{$ipv6_part}){0,4}:{$ipv4}|:))" . '|' .
                "(?::(?:(?::{$ipv6_part}){1,7}|(?::{$ipv6_part}){0,5}:{$ipv4}|:))" .
                ')';
        $fqdn = "(?:(?:{$fqdn_part}\\.)+?{$fqdn_part})";
        $local = "({$dot_string}++|(\"){$quoted_string}++\")";
        $domain = "({$fqdn}|\\[{$ipv4}]|\\[{$ipv6}]|\\[{$fqdn}])";
        $pattern = "/\\A{$local}@{$domain}\\z/";
        return preg_match($pattern, $email, $matches) && (!empty($matches[2]) && !isset($matches[1][66]) && !isset($matches[0][256]) ||
                !isset($matches[1][64]) && !isset($matches[0][254]));
    }

    private $_myaddress = NULL;
    private $_to = NULL;

    /**
     * Always validate E-Mail addresses with Email::validate($email) !!!
     */
    public function __construct() {
        $host = Server::getMyHost();
        $host = ($host === NULL ? 'localhost' : $host);
        $this->_myaddress = 'noreply@' . $host;
    }

    public function setTo($address) {
        $this->_to = $address;
    }

    public function send($subject, $message) {
        if ($this->_to === NULL) {
            throw new Exception('No E-Mail recipient is set!', Errorcode::EMAIL_NO_RECIPIENT);
        }

        $headers = array();
        $headers[] = 'MIME-Version: 1.0';
        $headers[] = 'Content-type: text/plain; charset=UTF-8';
        $headers[] = 'From: ' . $this->_myaddress;
        $headers[] = 'Subject: ' . $subject;

        mail($this->_to, $subject, $message, implode("\r\n", $headers));
    }

}
