<?php

trait Hasmeta {

    protected $_meta = array();

    protected function loadMeta($table = self::DB_TABLE) {
        $id = $this->_id;
        $stm = $this->_db->prepare('SELECT * FROM ' . $table . ' WHERE id = :id LIMIT 1');
        $stm->bindParam(':id', $id);
        $stm->execute();

        $this->_meta[$table] = $stm->fetch();

        if (empty($this->_meta[$table])) {
            throw new Exception('Data not found.');
        }
    }

    protected function getWhatever($key, $table = self::DB_TABLE) {
        if (!isset($this->_meta[$table][$key])) {
            $this->loadMeta();
        }

        return $this->_meta[$table][$key];
    }

}
