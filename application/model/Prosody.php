<?php

class Prosody {

    const HOST = 'localhost';
    const PORT = 5582;

    /**
     * Get the Prosody IM server startup time
     * @return DateTime
     * @throws Exception
     */
    public function getServerStartupTime() {
        $data = $this->runCmd('server:uptime()')[0];
        $data = $this->cleanOutputLine($data);
        $data = preg_match('/^.+\(since\s(.+)\)/', $data, $matches);

        $date = new DateTime();
        $date->setTimestamp(strtotime($matches[1]));

        return $date;
    }

    /**
     * Create a new user on the Prosody server
     * @param string $jid
     * @param string $pwd
     * @return boolean
     */
    public function createUser($jid, $pwd) {
        $res = $this->runCmd(sprintf('user:create("%s", "%s")', $jid, $pwd));

        if (strpos($res[0], 'OK:') === 3) {
            return TRUE;
        }

        return FALSE;
    }

    /**
     * Get all Prosody users on this server
     * @param string $host
     * @return array
     */
    public function getAllUser($host) {
        $data = $this->runCmd(sprintf('user:list("%s")', $host));

        $data = array_slice($data, 0, -1);

        foreach ($data as &$usr) {
            $usr = str_replace('|', '', $usr);
            $usr = $this->cleanOutputLine($usr);
        }

        return $data;
    }

    /**
     * Permanently delete a Prosody user
     * @param string $jid
     * @return boolean
     */
    public function deleteUser($jid) {
        $res = $this->runCmd(sprintf('user:delete("%s")', $jid));

        if (strpos($res[0], 'OK:') === 3) {
            return TRUE;
        }

        return FALSE;
    }
    
    public function countActiveUser($host) {
        $res = $this->runCmd(sprintf('c2s:show("%s")', $host));
        
        if(count($res) === 1) {
            return 0;
        }
        
        $res = array_slice($res, 1);
        $res = array_slice($res, 0, -1);
        
        return count($res);
    }

    /**
     * Change the password of a user
     * @param string $jid
     * @param string $pwd
     */
    public function setPasswort($jid, $pwd) {
        $res = $this->runCmd(sprintf('user:password("%s", "%s")', $jid, $pwd));

        if (strpos($res[0], 'OK:') === 3) {
            return TRUE;
        }

        return FALSE;
    }

    /**
     * Run any Prosody IM telnet command and return output
     * @param string $cmd
     * @return array
     * @throws Exception
     */
    private function runCmd($cmd) {
        $socket = fsockopen(self::HOST, self::PORT, $errno, $errstr);

        if ($socket === FALSE) {
            throw new Exception('Prosody connection error: ' . $errstr, Errorcode::PROSODY_CONNECTION_ERROR);
        }

        // (!) double quotes are essential here
        fwrite($socket, $cmd . "\r\n");
        fwrite($socket, "quit\r\n");

        $out = '';
        while ($buf = fread($socket, 2028)) {
            $out .= $buf;
        }

        fclose($socket);

        return $this->extractData($out);
    }

    private function extractData($telnetRes) {
        $lines = explode(PHP_EOL, $telnetRes);

        $lines = array_slice($lines, 12);
        $lines = array_slice($lines, 0, -2);

        return $lines;
    }

    private function cleanOutputLine($line) {
        $line = str_replace('\n', '', $line);
        return trim($line);
    }

}
