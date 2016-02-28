<?php

use Famework\Registry\Famework_Registry;

class Group {

    /**
     * Get group name by ID
     * @param int $id
     * @return string
     */
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

    /**
     * Get Otheruser by groupID
     * @param int $id The groupID
     * @param Currentuser $caller
     * @return \Otheruser
     */
    public static function getOwnerById($id, Currentuser $caller) {
        $stm = Famework_Registry::getDb()->prepare('SELECT user_id FROM user_groups WHERE id = :id LIMIT 1');
        $stm->bindParam(':id', $id, PDO::PARAM_INT);
        $stm->execute();

        $res = $stm->fetch();

        if (!empty($res)) {
            return new Otheruser($res['user_id'], $caller->getId());
        }

        return NULL;
    }

    public static function getMembers($id, Currentuser $caller) {
        $stm = Famework_Registry::getDb()->prepare('SELECT user_id FROM user_to_groups
                                                    WHERE group_id = ?');
        $stm->execute(array($id));

        $res = array();

        foreach ($stm->fetchAll() as $row) {
            $res[] = new Otheruser($row['user_id'], $caller->getId());
        }

        return $res;
    }

    public static function deleteAllMembers($id, Currentuser $caller) {
        if (Group::getOwnerById($id, $caller)->getId() === $caller->getId()) {
            $stm = Famework_Registry::getDb()->prepare('DELETE FROM user_to_groups
                                                    WHERE group_id = ?');
            $stm->execute(array($id));
        }
    }

    public static function addMember($id, Otheruser $user, Currentuser $caller) {
        if (Group::getOwnerById($id, $caller)->getId() === $caller->getId()) {
            $stm = Famework_Registry::getDb()->prepare('INSERT IGNORE INTO user_to_groups (user_id, group_id) VALUES (?, ?)');
            $stm->execute(array($user->getId(), $id));
        }
    }

    public static function removeMember($id, Otheruser $user, Currentuser $caller) {
        if (Group::getOwnerById($id, $caller)->getId() === $caller->getId()) {
            $stm = Famework_Registry::getDb()->prepare('DELETE FROM user_to_groups WHERE group_id = ? AND user_id = ?');
            $stm->execute(array($id, $user->getId()));
        }
    }

}
