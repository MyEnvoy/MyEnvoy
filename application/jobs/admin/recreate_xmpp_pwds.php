<?php

require __DIR__ . '/../bootstrap_job.php';

$stmt = $db->prepare('SELECT user_id FROM user_data');
$stmt->execute();

$upd = $db->prepare('UPDATE user_data SET xmpp_pwd = ? WHERE user_id = ?');

$count = 0;

foreach ($stmt->fetchAll() as $row) {
    $xmpp_pwd = hash('sha512', uniqid('xmpp', TRUE));

    printf('Updating user %d ...%s', $row['user_id'], PHP_EOL);

    $upd->execute(array($xmpp_pwd, $row['user_id']));

    $count++;
}

printf('Updated %d users.%s', $count, PHP_EOL);
