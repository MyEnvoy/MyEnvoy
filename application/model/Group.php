<?php

use Famework\Registry\Famework_Registry;

class Group {

    public static function getNameById($id) {
        $stm = Famework_Registry::getDb()->prepare('SELECT name FROM user_groups WHERE id = :id LIMIT 1');
        $stm->bindParam(':id', $id, PDO::PARAM_INT);
        $stm->execute();

        $res = $stm->fetch();

        if (!empty($res)) {
            return $res['name'];
        }

        return NULL;
    }

}
