<?php

use Famework\Registry\Famework_Registry;
use Famework\Session\Famework_Session;
use Famework\Request\Famework_Request;
use Api\Jwt;

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

    public static function isNotActivated($name) {
        $db = Famework_Registry::getDb();
        $stm = $db->prepare('SELECT id FROM user '
                . ' JOIN user_data ud ON ud.user_id = user.id '
                . 'WHERE name = :name AND activated = 0 AND host_gid IS NULL LIMIT 1');
        $stm->bindParam(':name', $name);
        $stm->execute();

        if (count($stm->fetchAll()) === 1) {
            return TRUE;
        }

        return FALSE;
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

    public static function getEnsuredLoggedInUserByJwt($token) {
        $jwt = new Jwt();
        $decToken = $jwt->decode($token);

        return new Currentuser((int) $decToken->uid);
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

    public function getXmppPwd() {
        return $this->getWhatever('xmpp_pwd', self::DB_USER_DATA);
    }

    /**
     * Gets the register datetime of the user
     * @return \DateTime
     */
    public function getAddDate() {
        return Userinfo::getDateTime($this->getId(), Userinfo::MESSAGE_REGISTER, 1);
    }

    public function getCertainGroupPicUrl($size, $groupId) {
        $path = $this->getPicturePath($size, $groupId);

        if (is_readable($path) === TRUE) {
            return '/' . APPLICATION_LANG . '/upload/grouppic/?id=' . $groupId . '&size=' . $size;
        }

        return $this->getPictureUrl($size);
    }

    public function getPicturePath($size, $groupId = 'default') {
        $size = intval($size);
        $filename = Picture::getUserPicName($this->getId(), $size, $groupId);
        return Picture::PROFILEPIC_PATH . $filename;
    }

    /**
     * Logout the user: destroy session
     */
    public function logout() {
        Famework_Session::destroySession();
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
     * @return array(\Otheruser)
     */
    public function getMyFriends() {
        $stm = $this->_db->prepare('SELECT g.id FROM user_to_groups utg
                                        JOIN user_groups g ON g.id = utg.group_id
                                    WHERE utg.user_id = ? GROUP BY g.id');
        $stm->execute(array($this->getId()));

        $res = array();
        $ownerIDs = array();
        foreach ($stm->fetchAll() as $row) {
            $owner = Group::getOwnerById($row['id'], $this);
            if (!in_array($owner->getGid(), $ownerIDs)) {
                $ownerIDs[] = $owner->getGid();
                $res[] = $owner;
            }
        }
        return $res;
    }

    /**
     * Get who follows this user
     * @return array(\Otheruser)
     */
    public function getMyFollowers() {
        $stm = $this->_db->prepare('SELECT utg.user_id id FROM user_groups g
                                        JOIN user_to_groups utg ON utg.group_id = g.id
                                    WHERE g.user_id = ? GROUP BY utg.user_id');
        $stm->execute(array($this->getId()));

        $res = array();
        foreach ($stm->fetchAll() as $row) {
            $res[] = new Otheruser($row['id'], $this->getId());
        }
        return $res;
    }

    /**
     * Get posts for the wall
     * @param type $page
     * @param type $limit
     * @return array <b>array(array('post' => Post, 'comments' => array('comment' => Post, 'subcomments' => array(Post))))</b>
     */
    public function getWall($page = 0, $limit = 20) {
        $offset = $page * $limit;

        $allowedGroups = $this->getMyMemberships();
        $sql = 'SELECT * FROM user_posts p
                    JOIN user_posts_data d ON p.id = d.post_id
                WHERE p.post_id IS NULL
                AND d.group_id IN (' . implode(',', $allowedGroups) . ')
                GROUP BY p.id
                ORDER BY p.datetime DESC LIMIT :offset, :limit';
        $stm = $this->_db->prepare($sql);
        $stm->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stm->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stm->execute();
        $res = array();

        foreach ($stm->fetchAll() as $row) {
            $post = Post::getById($row['id']);
            $comments = $post->getEntireComments();
            $count = 0;
            do {
                $key = strtotime($post->getCreationTime()) . '#' . rand(0, 1000);
                $count++;
            } while (isset($res[$key]) && $count < 1000);
            $res[$key] = array('post' => $post, 'comments' => $comments);
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
        $stm = $this->_db->prepare('SELECT count(1) count FROM (
                                        SELECT p.id FROM user_posts p
                                                JOIN user_posts_data d ON p.id = d.post_id AND d.group_id IN (' . implode(',', $allowedGroups) . ') AND p.id = :pid
                                        GROUP BY p.id
                                    ) x LIMIT 1');
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

        $res = 0;

        foreach ($friends as $friend) {
            if ($otheruser->getId() === $friend->getId()) {
                $res = $res | self::I_AM_FOLLOWING;
            }
        }

        foreach ($followers as $follower) {
            if ($otheruser->getId() === $follower->getId()) {
                $res = $res | self::FOLLOWS_ME;
            }
        }

        if ($res === 0) {
            return self::NO_CONNECTION;
        }

        return $res;
    }

    /**
     * @var Usersettings
     */
    private $_settings;

    /**
     * Get the user settigs object
     * @return Usersettings
     */
    public function getSettings() {
        if (!isset($this->_settings)) {
            $this->_settings = Usersettings::getByUserID($this->getId());
        }
        return $this->_settings;
    }

    public function setDisplayName($name) {
        $name = Security::trim($name);

        if (!empty($name) && preg_match('/^[a-zA-ZäöüÄÖÜß\s]{3,40}$/u', $name) !== 1) {
            return FALSE;
        }

        $stm = $this->_db->prepare('UPDATE user SET display_name = ? WHERE id = ?');
        $stm->execute(array($name, $this->getId()));

        return TRUE;
    }

    public function setStatus($status) {
        $status = Security::trim($status);

        $stm = $this->_db->prepare('UPDATE user SET status = ? WHERE id = ?');
        $stm->execute(array($status, $this->getId()));
    }

    public function setXmppPwd($pwd) {
        $upd = $this->_db->prepare('UPDATE user_data SET xmpp_pwd = ? WHERE user_id = ?');
        $upd->execute(array($pwd, $this->getId()));
        
        $prosody = new Prosody();
        $prosody->setPasswort($this->getName() . '@' . Server::getMyHost(), $pwd);
        
        Userinfo::log($this->getId(), Userinfo::MESSAGE_XMPP_PWD_CHANGED);
    }

    /**
     * @return array(\Notification)
     */
    public function getNotifications() {
        $stm = $this->_db->prepare('SELECT id FROM user_notifications WHERE user_id = ? ORDER BY rec ASC, id DESC LIMIT 100');
        $stm->execute(array($this->getId()));

        $res = array();

        foreach ($stm->fetchAll() as $row) {
            $res[] = new Notification($row['id']);
        }

        return $res;
    }

    public function countNewNotifications() {
        $stm = $this->_db->prepare('SELECT count(1) count FROM user_notifications WHERE user_id = ? AND rec = 0');
        $stm->execute(array($this->getId()));

        return (int) $stm->fetch()['count'];
    }

}
