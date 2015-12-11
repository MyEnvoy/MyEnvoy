<?php

class Email {

    public static function validate($email) {
        if (strpos($email, '@') === FALSE) {
            return FALSE;
        }

        $atpos = strrpos($email, '@');

        $localpart = substr($email, 0, $atpos);
        $hostpart = substr($email, $atpos + 1);

        var_dump_pre($localpart);
        var_dump_pre($hostpart);

        $localpartlen = strlen($localpart);
        $hostpartlen = strlen($hostpart);

        if ($localpartlen > 64 || $hostpartlen > 255 ||
                $localpartlen < 1 || $hostpartlen < 4) {
            return FALSE;
        }

        return TRUE;
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

    /**
     * Add "to" recipient
     * @param string $address
     */
    public function setTo($address) {
        $this->_to = $address;
    }

    /**
     * Send a mail to previously set recipients
     * @param string $subject
     * @param string $message
     * @throws Exception
     */
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
