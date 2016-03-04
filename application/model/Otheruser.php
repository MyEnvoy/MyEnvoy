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

    /**
     * Construct Otheruser object
     * @param int $id
     * @param int $callerId
     */
    public function __construct($id, $callerId) {
        $this->initDb();
        $this->_id = (int) $id;
        $this->loadMeta();
        $this->_callerID = (int) $callerId;
    }

    public function getPicturePath($size) {
        $stm = $this->_db->prepare('SELECT g.id FROM user_groups g
                                        JOIN user_to_groups utg ON utg.group_id = g.id AND utg.user_id = ?
                                    WHERE g.user_id = ? LIMIT 1');
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

    public function getAllPossiblePicturePaths($size) {
        $stm = $this->_db->prepare('SELECT g.id FROM user_groups g
                                        JOIN user_to_groups utg ON utg.group_id = g.id AND utg.user_id = ?
                                    WHERE g.user_id = ? ORDER BY g.prio DESC');
        $stm->execute(array($this->_callerID, $this->getId()));

        $res = array();

        // add default pic
        $filename = Picture::getUserPicName($this->getId(), $size);
        $res['default'] = Picture::PROFILEPIC_PATH . $filename;

        foreach ($stm->fetchAll() as $groupinfo) {
            $filename = Picture::getUserPicName($this->getId(), $size, $groupinfo['id']);
            $path = Picture::PROFILEPIC_PATH . $filename;

            if (is_readable($path)) {
                $res[$groupinfo['id']] = $path;
            }
        }

        return $res;
    }

    public function getPublicPosts() {
        $groupID = $this->getPublicGroupId();
        $stm = $this->_db->prepare('SELECT p.id FROM user_posts p
                                        JOIN user_posts_data d ON p.id = d.post_id AND d.group_id = ?
                                    WHERE p.user_id = ?  AND p.post_id IS NULL
                                    GROUP BY p.id ORDER BY p.datetime DESC LIMIT 30');
        $stm->execute(array($groupID, $this->getId()));

        $res = array();

        foreach ($stm->fetchAll() as $row) {
            $res[] = Post::getById($row['id']);
        }

        return $res;
    }

    /**
     * Get posts which are viewable by given user (only works if there is any kind of friendship/followership)
     * @param Currentuser $user
     * @return array <b>array(array('post' => Post, 'comments' => array('comment' => Post, 'subcomments' => array(Post))))</b>
     */
    public function getViewablePosts(Currentuser $user) {
        $groups = $user->getMyMemberships();
        $stm = $this->_db->prepare('SELECT p.id FROM user_posts p
                                        JOIN user_posts_data d ON p.id = d.post_id AND p.user_id = ?
                                    WHERE p.post_id IS NULL AND d.group_id IN (' . implode(',', $groups) . ')
                                    GROUP BY p.id ORDER BY p.datetime DESC LIMIT 30');
        $stm->execute(array($this->getId()));

        $res = array();

        foreach ($stm->fetchAll() as $row) {
            $post = Post::getById($row['id']);
            $comments = $post->getEntireComments();
            $res[] = array('post' => $post, 'comments' => $comments);
        }

        return $res;
    }

    public function follow(Currentuser $user) {
        $user_id = $user->getId();
        $groupId = $this->getPublicGroupId();
        $stm = $this->_db->prepare('INSERT IGNORE INTO user_to_groups (user_id, group_id) VALUES (?, ?)');
        $stm->execute(array($user_id, $groupId));
    }

    public function unfollow(Currentuser $user) {
        $user_id = $user->getId();
        $groupIDs = array_flip($this->getGroupOverview());
        if ($groupIDs !== NULL) {
            $stm = $this->_db->prepare('DELETE FROM user_to_groups WHERE user_id = ? AND group_id IN (' . implode(',', $groupIDs) . ')');
            $stm->execute(array($user_id));
        }
    }

}
