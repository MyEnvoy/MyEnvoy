<?php

use Famework\Registry\Famework_Registry;
use Famework\Session\Famework_Session;
use Famework\Request\Famework_Request;

class Currentuser extends User {

    const PIC_LARGE = 256;
    const PIC_SMALL = 32;
    const DB_USER_DATA = 'user_data';

    public static function getUserFromLogin($name, $pwd) {
        $db = Famework_Registry::getDb();
        $stm = $db->prepare('SELECT id, salt, pwd FROM user '
                . ' JOIN user_data ud ON ud.user_id = user.id '
                . 'WHERE name = :name AND activated = 1 AND host_gid IS NULL LIMIT 1');
        $stm->bindParam(':name', $name);
        $stm->execute();

        $salt = NULL;
        $uid = NULL;
        $dbpwd = NULL;

        foreach ($stm->fetchAll() as $row) {
            $uid = (int) $row['id'];
            $salt = $row['salt'];
            $dbpwd = $row['pwd'];
        }

        if ($uid === NULL) {
            return NULL;
        }

        $inputpwd = self::generatePasswordHash($pwd, $salt);

        if ($inputpwd !== $dbpwd) {
            return NULL;
        }

        return new Currentuser($uid);
    }

    public static function getIdByName($name) {
        $stm = Famework_Registry::getDb()->prepare('SELECT id FROM user WHERE name = ? AND host_gid IS NULL LIMIT 1');
        $stm->execute(array($name));
        $data = $stm->fetch();
        if (empty($data)) {
            return NULL;
        }
        return (int) $data['id'];
    }

    public static function getEnsureLoggedInUser($strict = TRUE) {
        $session = new Famework_Session();
        $session->setNamespace('user');
        $uid = (int) $session->get('uid');

        if (empty($uid)) {
            if ($strict === TRUE) {
                throw new Exception('ACCESS DENIED.', Errorcode::USER_ACCESS_DENIED);
            } else {
                return NULL;
            }
        }

        return new Currentuser($uid);
    }

    public static function auth() {
        $user = self::getEnsureLoggedInUser(FALSE);

        if ($user === NULL) {
            Famework_Request::redirect('/' . APPLICATION_LANG . '/');
        } else {
            return $user;
        }
    }

    // Singleton pattern
    private function __construct($id) {
        $this->initDb();
        $this->_id = $id;
        $this->loadMeta();
    }

    public function generateAuthSession() {
        $session = new Famework_Session();
        $session->regenerateId(TRUE);
        $session->setNamespace('user');
        $session->set('uid', $this->getId());
    }

    public function getEmail() {
        return $this->getWhatever('email', self::DB_USER_DATA);
    }

    public function getPrivateKey() {
        return $this->getWhatever('priv_key_enc', self::DB_USER_DATA);
    }

    public function getPwdHash() {
        return $this->getWhatever('pwd', self::DB_USER_DATA);
    }

    public function getSalt() {
        return $this->getWhatever('salt', self::DB_USER_DATA);
    }

    /**
     * Gets the register datetime of the user
     * @return \DateTime
     */
    public function getAddDate() {
        return Userinfo::getDateTime($this->getId(), Userinfo::MESSAGE_REGISTER, 1);
    }

    public function getPicturePath($size) {
        $size = intval($size);
        $filename = Picture::getUserPicName($this->getId(), $size);
        return Picture::PROFILEPIC_PATH . $filename;
    }

    /**
     * Logout the user: destroy session
     */
    public function logout() {
        Famework_Session::destroySession();
    }

    /**
     * Get all groups of this user
     * @return array All groups of the current user <b>array('#ID' => '#NAME')</b>
     */
    public function getGroupOverview() {
        $stm = $this->_db->prepare('SELECT id, name FROM user_groups WHERE user_id = ? ORDER BY isdefault DESC, id DESC');
        $stm->execute(array($this->getId()));

        $data = array();

        foreach ($stm->fetchAll() as $row) {
            $data[(int) $row['id']] = $row['name'];
        }

        return $data;
    }

    /**
     * Return the IDs of all groups from which the user is allowd to read posts
     */
    public function getMyMemberships() {
        $stm = $this->_db->prepare('SELECT * FROM user_groups grp
                                        LEFT JOIN user_to_groups utg ON utg.group_id = grp.id AND utg.user_id = :userid
                                    WHERE grp.user_id = :userid OR utg.user_id = :userid
                                    GROUP BY grp.id');
        $id = $this->getId();
        $stm->bindParam(':userid', $id, PDO::PARAM_INT);
        $stm->execute();

        $res = array();

        foreach ($stm->fetchAll() as $row) {
            $res[] = (int) $row['id'];
        }

        return $res;
    }

    /**
     * Get who this user is following
     */
    public function getMyFriends() {
        $stm = $this->_db->prepare('SELECT ug.user_id id FROM user_groups_members ugm
                                        JOIN user_groups ug ON ug.id = ugm.group_id
                                    WHERE ugm.user_id = ?');
        $stm->execute(array($this->getId()));

        $res = array();
        foreach ($stm->fetchAll() as $row) {
            $res[] = new Otheruser($row['id'], $this->getId());
        }
        return $res;
    }

    public function getMyFollowers() {
        $stm = $this->_db->prepare('SELECT grpmbr.user_id id FROM user_groups grp	
                                        JOIN user_groups_members grpmbr ON grpmbr.group_id = grp.id
                                    WHERE grp.user_id = ?');
        $stm->execute(array($this->getId()));

        $res = array();
        foreach ($stm->fetchAll() as $row) {
            $res[] = new Otheruser($row['id'], $this->getId());
        }
        return $res;
    }

    /**
     * 
     * @param type $page
     * @param type $limit
     * @return array <b>array(array('post' => Post, 'comments' => array('comment' => Post, 'subcomments' => array(Post))))</b>
     */
    public function getWall($page = 0, $limit = 10) {
        $offset = $page * $limit;

        $allowedGroups = $this->getMyMemberships();
        $sql = 'SELECT * FROM user_posts p
                    JOIN user_posts_data d ON p.id = d.post_id
                WHERE p.post_id IS NULL
                GROUP BY p.id
                HAVING d.group_id IN (' . implode(',', $allowedGroups) . ')
                ORDER BY p.datetime DESC LIMIT :offset, :limit';
        $stm = $this->_db->prepare($sql);
        $stm->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stm->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stm->execute();
        $res = array();

        foreach ($stm->fetchAll() as $row) {
            $post = Post::getFromId($row['id']);
            $comments = $post->getEntireComments();
            $res[] = array('post' => $post, 'comments' => $comments);
        }

        return $res;
    }

    public function hasFavourised($postID) {
        $stm = $this->_db->prepare('SELECT count(1) count FROM (SELECT * FROM user_posts_favs WHERE post_id = ? AND user_id = ? GROUP BY post_id) x');
        $stm->execute(array($postID, $this->getId()));

        return (bool) $stm->fetch()['count'];
    }

    public function canSeePost($postID) {
        $allowedGroups = $this->getMyMemberships();
        $stm = $this->_db->prepare('SELECT count(1) count FROM (SELECT * FROM user_posts WHERE group_id IN (' . implode(',', $allowedGroups) . ') AND id = :pid) x LIMIT 1');
        $stm->bindParam(':pid', $postID, PDO::PARAM_INT);
        $stm->execute();

        return (bool) $stm->fetch()['count'];
    }

    const I_AM_FOLLOWING = 1;
    const FOLLOWS_ME = 2;
    const NO_CONNECTION = 4;

    public function getConnectionWith(Otheruser $otheruser) {
        $friends = $this->getMyFriends();
        $followers = $this->getMyFollowers();

        foreach ($friends as $friend) {
            if ($otheruser->getId() === $friend->getId()) {
                $res = self::I_AM_FOLLOWING;
            }
        }

        foreach ($followers as $follower) {
            if ($otheruser->getId() === $follower->getId()) {
                $res = self::FOLLOWS_ME;
            }
        }
    }

}
