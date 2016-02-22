<?php

trait Hasmeta {

    protected $_meta = array();

    protected function loadMeta($table = self::DB_TABLE) {
        $id = $this->_id;
        if ($table === Currentuser::DB_USER_DATA || $table === Usersettings::DB_TABLE) {
            $stm = $this->_db->prepare('SELECT * FROM ' . $table . ' WHERE user_id = :id LIMIT 1');
        } else {
            $stm = $this->_db->prepare('SELECT * FROM ' . $table . ' WHERE id = :id LIMIT 1');
        }
        $stm->bindParam(':id', $id);
        $stm->execute();

        $this->_meta[$table] = $stm->fetch();

        if (empty($this->_meta[$table])) {
            throw new Exception('Data not found.', Errorcode::HASMETA_NODATA);
        }
    }

    protected function getWhatever($key, $table = self::DB_TABLE) {
        if (!isset($this->_meta[$table][$key])) {
            $this->loadMeta($table);
        }

        return $this->_meta[$table][$key];
    }

}
