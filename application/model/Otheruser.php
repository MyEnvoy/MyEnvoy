<?php

class Otheruser extends User {

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

}
