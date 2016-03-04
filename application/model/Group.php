<?php

use Famework\Registry\Famework_Registry;

class Group {

    const MAX_NAME_LENGTH = 50;

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
        $stm = Famework_Registry::getDb()->prepare('SELECT user_id FROM user_to_groups g
                                                        JOIN user u ON u.id = g.user_id
                                                    WHERE g.group_id = ? ORDER BY u.name');
        $stm->execute(array((int) $id));

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
            $stm->execute(array((int) $id));
        }
    }

    public static function addMember($id, Otheruser $user, Currentuser $caller) {
        if (Group::getOwnerById($id, $caller)->getId() === $caller->getId()) {
            $stm = Famework_Registry::getDb()->prepare('INSERT IGNORE INTO user_to_groups (user_id, group_id) VALUES (?, ?)');
            $stm->execute(array($user->getId(), (int) $id));
        }
    }

    public static function removeMember($id, Otheruser $user, Currentuser $caller) {
        if (Group::getOwnerById($id, $caller)->getId() === $caller->getId()) {
            $stm = Famework_Registry::getDb()->prepare('DELETE FROM user_to_groups WHERE group_id = ? AND user_id = ?');
            $stm->execute(array((int) $id, $user->getId()));
        }
    }

    public static function create($name, Currentuser $owner) {
        $stm = Famework_Registry::getDb()->prepare('INSERT INTO user_groups (user_id, name) VALUES (?, ?)');
        $stm->execute(array($owner->getId(), $name));
    }

    public static function remove($id, Currentuser $owner) {
        $stm = Famework_Registry::getDb()->prepare('DELETE FROM user_groups WHERE user_id = ? AND id = ? LIMIT 1');
        $stm->execute(array($owner->getId(), (int) $id));
    }

    public static function removePic($id, Currentuser $owner) {
        foreach (array(Currentuser::PIC_SMALL, Currentuser::PIC_LARGE) as $size) {
            try {
                $pic = new Picture();
                $pic->loadPictureFromPath($owner->getPicturePath($size, $id));
                $pic->remove();
            } catch (Exception $e) {
                // no picture
            }
        }
    }

    public static function setPrio($id, $prio, Currentuser $owner) {
        $stm = Famework_Registry::getDb()->prepare('UPDATE user_groups SET prio = ? WHERE id = ? AND user_id = ? LIMIT 1');
        $stm->execute(array((int) $prio, (int) $id, $owner->getId()));
    }

    public static function setAsDefault($id, Currentuser $owner) {
        // remove old default
        $stm = Famework_Registry::getDb()->prepare('UPDATE user_groups SET isdefault = 0 WHERE user_id = ?');
        $stm->execute(array($owner->getId()));
        // set new
        $stm1 = Famework_Registry::getDb()->prepare('UPDATE user_groups SET isdefault = 1 WHERE id = ? AND user_id = ?');
        $stm1->execute(array((int) $id, $owner->getId()));
    }

}
