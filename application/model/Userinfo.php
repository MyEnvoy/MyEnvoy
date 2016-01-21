<?php

use Famework\Registry\Famework_Registry;

class Userinfo {

    const MAX_LOGIN_ATTEMPTS = 8;
    const LOCK_DELAY_MINUTES = 10;
    // max length for messages is 20 chars
    const MESSAGE_LOGIN_SUCCESS = 'login_successs';
    const MESSAGE_LOGIN_FAIL = 'login_fail';
    const MESSAGE_REGISTER = 'register';
    const MESSAGE_ACTIVATE_ACCOUNT = 'account_activation';
    const MESSAGE_LOGIN_BLOCKED = 'login_blocked';

    /**
     * Log a user action
     * @param int $user_id
     * @param string $message One of <b>Userinfo::MESSAGE_*</b>
     */
    public static function log($user_id, $message) {
        $stm = Famework_Registry::getDb()->prepare('INSERT INTO user_log (user_id, action, ip) VALUES (?, ?, ?)');
        $stm->execute(array((int) $user_id, $message, Server::getClientIP()));
    }

    /**
     * Get all datetimes where special user had special action
     * @param int $user_id
     * @param string $message One of <b>Userinfo::MESSAGE_*</b>
     * @param int $limit max amount of return data; default is 1000
     * @return \DateTime or array of DateTime objects or NULL if no action was found
     */
    public static function getDateTime($user_id, $message, $limit = 1000) {
        $stm = Famework_Registry::getDb()->prepare('SELECT datetime FROM user_log WHERE user_id = ? AND action = ? LIMIT ' . intval($limit));
        $stm->execute(array((int) $user_id, $message));

        $res = $stm->fetchAll();
        if (count($res) === 0) {
            return NULL;
        } elseif (count($res) === 1) {
            return new DateTime($res[0]['datetime']);
        }
        $data = array();
        foreach ($res as $r) {
            $data[] = new DateTime($r['datetime']);
        }

        return $data;
    }

    /**
     * Checks whether an account is locked because of too many login attempts
     * @param int $user_id
     * @return boolean <b>TRUE</b> if account is locked
     */
    public static function isAccountLocked($user_id) {
        $stm = Famework_Registry::getDb()->prepare('SELECT count(1) count FROM user_log WHERE user_id = ? '
                . 'AND action = "' . self::MESSAGE_LOGIN_FAIL . '" '
                . 'AND (user_log.datetime BETWEEN DATE_SUB(CURRENT_TIMESTAMP(), INTERVAL ' . intval(self::LOCK_DELAY_MINUTES) . ' MINUTE) AND CURRENT_TIMESTAMP()) '
                . 'GROUP BY user_id');
        $stm->execute(array((int) $user_id));

        $data = $stm->fetch();

        if (empty($data)) {
            return FALSE;
        }
        $attempts = $data['count'];

        if ($attempts >= self::MAX_LOGIN_ATTEMPTS) {
            return TRUE;
        }

        return FALSE;
    }

}
