<?php

trait Hasmeta {

    protected $_meta = NULL;

    protected function loadMeta() {
        $id = $this->_id;
        $stm = $this->_db->prepare('SELECT * FROM ' . self::DB_TABLE . ' WHERE id = :id LIMIT 1');
        $stm->bindParam(':id', $id);
        $stm->execute();

        $this->_meta = $stm->fetch();

        if (empty($this->_meta)) {
            throw new Exception('Data not found.');
        }
    }

    protected function getWhatever($key) {
        if (!isset($this->_meta[$key])) {
            $this->loadMeta();
        }

        return $this->_meta[$key];
    }

}
