<?php

use Famework\Registry\Famework_Registry;

class Otheruser extends User {

    public static function getLocalByName($name, $callerId) {
        $stm = Famework_Registry::getDb()->prepare('SELECT id FROM user WHERE name = ? AND host_gid IS NULL LIMIT 1');
        $stm->execute(array($name));
        $res = $stm->fetch();
        if (!empty($res)) {
            return new Otheruser($res['id'], $callerId);
        }
        return NULL;
    }

    public static function getByGid($gid, $callerId) {
        $stm = Famework_Registry::getDb()->prepare('SELECT id FROM user WHERE gid = ? LIMIT 1');
        $stm->execute(array($gid));
        $res = $stm->fetch();
        if (!empty($res)) {
            return new Otheruser($res['id'], $callerId);
        }
        return NULL;
    }

    private $_callerID;

    public function __construct($id, $callerId) {
        $this->initDb();
        $this->_id = (int) $id;
        $this->loadMeta();
        $this->_callerID = (int) $callerId;
    }

    public function getPicturePath($size) {
        $stm = $this->_db->prepare('SELECT grp.id id FROM user_groups grp
                                        JOIN user_groups_members grpmbr ON grpmbr.group_id = grp.id AND grpmbr.user_id = ?
                                    WHERE grp.user_id = ? LIMIT 1');
        $stm->execute(array($this->_callerID, $this->getId()));

        $groupinfo = $stm->fetch();

        $path = NULL;

        if (!empty($groupinfo)) {
            // special group pic
            $filename = Picture::getUserPicName($this->getId(), $size, $groupinfo['id']);
            $path = Picture::PROFILEPIC_PATH . $filename;
        }

        if (!is_readable($path)) {
            // default pic
            $filename = Picture::getUserPicName($this->getId(), $size);
            $path = Picture::PROFILEPIC_PATH . $filename;
        }

        return $path;
    }

    public function getPublicPosts() {
        $groupID = $this->getDefaultGroupId();
        $stm = $this->_db->prepare('SELECT id FROM user_posts WHERE user_id = ? AND group_id = ? AND post_id IS NULL');
        $stm->execute(array($this->getId(), $groupID));

        $res = array();

        foreach ($stm->fetchAll() as $row) {
            $res[] = Post::getFromId($row['id']);
        }

        return $res;
    }

    public function getDefaultGroupId() {
        $stm = $this->_db->prepare('SELECT MIN(id) id FROM user_groups WHERE user_id = ? LIMIT 1');
        $stm->execute(array($this->getId()));
        $res = $stm->fetch();
        return (int) $res['id'];
    }

}
