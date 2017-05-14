<?php

use Famework\Registry\Famework_Registry;

class Admin {

    public static function countActivatedUsers() {
        $stm = Famework_Registry::getDb()->prepare('SELECT COUNT(user_id) count FROM user_data WHERE activated = 1');
        $stm->execute();
        return (int) $stm->fetch()['count'];
    }

    public static function countOnlineUsers() {
        try {
            $prosody = new Prosody();
            return $prosody->countActiveUser(Server::getMyHost());
        } catch (Exception $e) {
            return -1;
        }
    }

    public static function getUserInformation() {
        $stm = Famework_Registry::getDb()->prepare('SELECT u.id, u.name, ud.email, ud.activated FROM user u
                                                        JOIN user_data ud ON u.id = ud.user_id ORDER BY id DESC');
        $stm->execute();

        $res = $stm->fetchAll();
        $lstm = Famework_Registry::getDb()->prepare('SELECT datetime FROM user_log 
                                                        WHERE user_id = ? AND action = "login_successs"
                                                        ORDER BY id DESC LIMIT 1');
        foreach ($res as &$row) {
            $id = $row['id'];
            $lstm->execute(array($id));
            $row['last_login'] = $lstm->fetch()['datetime'];
        }

        return $res;
    }

}
