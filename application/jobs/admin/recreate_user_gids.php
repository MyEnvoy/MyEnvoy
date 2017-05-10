<?php

require __DIR__ . '/../bootstrap_job.php';

$stmt = $db->prepare('SELECT id, name FROM user');
$stmt->execute();

$upd = $db->prepare('UPDATE user SET gid = ? WHERE id = ? AND name = ?');

$count = 0;

foreach ($stmt->fetchAll() as $row) {
    $gid = Otheruser::generateGid($row['name'], Server::getMyHost());

    printf('Updating user %d (%s) ...%s', $row['id'], $row['name'], PHP_EOL);

    $upd->execute(array($gid, $row['id'], $row['name']));

    $count++;
}

printf('Updated %d users.%s', $count, PHP_EOL);
