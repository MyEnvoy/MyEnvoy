<?php

use Famework\Registry\Famework_Registry;

define('APPLICATION_LANG', 'de');

function t($var) {
    $var = trim(strtolower($var));

    if (empty($var)) {
        throw new Exception('Misuse of t function.');
    }

    $value = apc_fetch('lang_' . APPLICATION_LANG . '_' . $var);

    if ($value === FALSE) {
        $applang = APPLICATION_LANG;

        $db = Famework_Registry::getDb();
        $stm = $db->prepare('SELECT value FROM lang WHERE lang = :lang AND name = :name LIMIT 1');
        $stm->bindParam(':lang', $applang);
        $stm->bindParam(':name', $var);
        $stm->execute();

        $value = $stm->fetch();
        if (empty($value)) {
            $ins = $db->prepare('INSERT INTO lang (lang, name) VALUES (:lang, :name)');
            $ins->bindParam(':lang', $applang);
            $ins->bindParam(':name', $var);
            $ins->execute();
            $value = $var;
        } elseif (empty($value['value'])) {
            $value = $var;
        } else {
            $value = $value['value'];
            apc_store('lang_' . APPLICATION_LANG . '_' . $var, $value);
        }
    }

    return $value;
}

function var_dump_pre($var) {
    echo '<pre class="var_dump">';
    var_dump($var);
    echo '</pre>';
}
