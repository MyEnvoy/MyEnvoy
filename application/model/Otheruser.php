<?php

use Famework\Registry\Famework_Registry;

class Otheruser extends User {

    /**
     * Get only local user by name
     * @param string $name
     * @param int $callerId
     * @return \Otheruser
     */
    public static function getLocalByName($name, $callerId) {
        $gid = User::generateGid($name, Security::getRealEnvoyDomain(Server::getMyHost()));
        return self::getLocalByGid($gid, $callerId);
    }

    public static function getLocalByGid($gid, $callerId = NULL) {
        $stm = Famework_Registry::getDb()->prepare('SELECT id FROM user WHERE gid = ? AND host_gid IS NULL LIMIT 1');
        $stm->execute(array($gid));
        $res = $stm->fetch();
        if (!empty($res)) {
            return new Otheruser($res['id'], $callerId);
        }
        return NULL;
    }

    /**
     * Get foreign or local user by gid
     * @param string $gid
     * @param int $callerId
     * @return \Otheruser|\Foreignotheruser
     */
    public static function getByGid($gid, $callerId) {
        $stm = Famework_Registry::getDb()->prepare('SELECT u.id, u.host_gid FROM user u JOIN user_data d ON d.user_id = u.id WHERE u.gid = ? AND d.activated = 1 LIMIT 1');
        $stm->execute(array($gid));
        $res = $stm->fetch();
        if (!empty($res)) {
            if (empty($res['host_gid'])) {
                return new Otheruser($res['id'], $callerId);
            } else {
                return new Foreignotheruser($res['id'], $callerId);
            }
        }
        return NULL;
    }

    protected $_callerID;

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
        $groupID = $this->getPublicGroupId();
        $stm = $this->_db->prepare('SELECT id FROM user_posts WHERE user_id = ? AND group_id = ? AND post_id IS NULL');
        $stm->execute(array($this->getId(), $groupID));

        $res = array();

        foreach ($stm->fetchAll() as $row) {
            $res[] = Post::getFromId($row['id']);
        }

        return $res;
    }

}
