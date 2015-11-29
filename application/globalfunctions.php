<?php

use Famework\Registry\Famework_Registry;

function t($var) {
    $var = trim(strtolower($var));

    if (empty($var) || strpos($var, ' ') !== FALSE) {
        throw new Exception('Misuse of t function.');
    }

    $apcKey = 'me_lang_' . APPLICATION_LANG . '_' . $var;
    $value = apc_fetch($apcKey);

    if ($value === FALSE) {
        $applang = APPLICATION_LANG;

        $db = Famework_Registry::getDb();
        $stm = $db->prepare('SELECT value FROM translate WHERE lang = :lang AND name = :name LIMIT 1');
        $stm->bindParam(':lang', $applang);
        $stm->bindParam(':name', $var);
        $stm->execute();

        $value = $stm->fetch();
        if (empty($value)) {
            $ins = $db->prepare('INSERT INTO translate (lang, name) VALUES (:lang, :name)');
            $ins->bindParam(':lang', $applang);
            $ins->bindParam(':name', $var);
            $ins->execute();
            $value = $var;
        } elseif (empty($value['value'])) {
            $value = $var;
        } else {
            $value = $value['value'];
            apc_store($apcKey, $value, 1800);
        }
    }

    return $value;
}

function var_dump_pre($var) {
    echo '<pre class="var_dump">';
    var_dump($var);
    echo '</pre>';
}

function getLangs() {
    $apcKey = 'me_available_langs';
    $value = apc_fetch($apcKey);

    if ($value === FALSE) {
        $db = Famework_Registry::getDb();
        $stm = $db->prepare('SELECT * FROM lang');
        $stm->execute();
        $value = array();

        foreach ($stm->fetchAll() as $row) {
            $value[$row['lang']] = $row['id'];
        }

        apc_store($apcKey, $value, 1800);
    }

    return $value;
}
