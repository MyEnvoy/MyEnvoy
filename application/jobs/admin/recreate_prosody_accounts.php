<?php

require __DIR__ . '/../bootstrap_job.php';

$prosody = new Prosody();

$delcount = 0;
printf('Delete old prosody users ...%s', PHP_EOL);
// first: delete all prosody accounts
foreach ($prosody->getAllUser(Server::getMyHost()) as $jid) {
    if ($prosody->deleteUser($jid)) {
        $delcount++;
    } else {
        printf('ERROR while deleting prosody user %s ...%s', $jid, PHP_EOL);
    }
}
printf('Deleted %d prosody users.%s', $delcount, PHP_EOL);

$count = 0;
printf('Recreate prosody users ...%s', PHP_EOL);
// second: recreate all users
$stmt = $db->prepare('SELECT user_id, xmpp_pwd FROM user_data WHERE activated = 1 AND xmpp_pwd IS NOT NULL AND xmpp_pwd != ""');
$stmt->execute();

foreach ($stmt->fetchAll() as $row) {
    $user = Otheruser::getLocalById($row['user_id'], -1);
    $jid = $user->getName() . '@' . Server::getMyHost();

    if ($prosody->createUser($jid, $row['xmpp_pwd'])) {
        $count++;
    } else {
        printf('ERROR while recreating prosody user %s ...%s', $jid, PHP_EOL);
    }
}

printf('Recreated %d prosody users.%s', $count, PHP_EOL);
