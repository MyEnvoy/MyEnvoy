<?php

use Famework\Registry\Famework_Registry;

class Notification {

    const DB_TABLE = 'user_notifications';

    use Hasmeta;

    /**
     * @var PDO
     */
    private $_db;
    private $_id;
    private $_link_templates;

    public function __construct($id = NULL) {
        $this->_id = (int) $id;
        $this->_db = Famework_Registry::getDb();
    }

    public function add(Otheruser $user, $type, $msg, $data) {
        $ins = $this->_db->prepare('INSERT INTO user_notifications (user_id, type, msg, data) VALUES(?, ?, ?, ?) '
                . 'ON DUPLICATE KEY '
                . 'UPDATE rec = 0');
        $ins->execute(array($user->getId(), (int) $type, $msg, (int) $data));

        $stm = $this->_db->prepare('SELECT id FROM user_notifications WHERE user_id = ? AND type = ? AND data = ? LIMIT 1');
        $stm->execute(array($user->getId(), (int) $type, (int) $data));
        $res = $stm->fetch();
        if (empty($res)) {
            throw new Exception('FATAL error while saving notification!', Errorcode::NOTIFICATION_CREATE_ERROR);
        }
        $this->_id = $res['id'];
    }

    const TYPE_NEW_COMMENT = 1;
    const TYPE_MENTIONED = 2;

    public function getType() {
        return (int) $this->getWhatever('type');
    }

    public function getMsg() {
        return $this->getWhatever('msg');
    }

    public function getData() {
        return (int) $this->getWhatever('data');
    }

    private function getLinkTemplate() {
        if ($this->_link_templates === NULL) {
            $this->_link_templates = array(
                self::TYPE_NEW_COMMENT => '/%s/post/show?id=%s',
                self::TYPE_MENTIONED => '/%s/post/show?id=%s'
            );
        }

        return $this->_link_templates[$this->getType()];
    }

    public function getLink() {
        return sprintf($this->getLinkTemplate(), APPLICATION_LANG, $this->getData());
    }

    const UNRECEIVED = 0;
    const RECEIVED = 1;

    public function getRecStatus() {
        return (int) $this->getWhatever('rec');
    }

    public function getId() {
        return $this->_id;
    }

    public function getGenericon() {
        switch ($this->getType()) {
            case self::TYPE_NEW_COMMENT:
                return 'genericon-comment';
            case self::TYPE_MENTIONED:
                return 'genericon-quote';
        }
    }

}
