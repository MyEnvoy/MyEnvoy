<?php

require __DIR__ . '/bootstrap_job.php';

Log::info('[BLACKEN_IP_LOG_JOB] Executing "blacken_ip_log" ...');

// select log entries from one week ago
$relevant = $db->prepare('SELECT * FROM user_log WHERE datetime > DATE_SUB(NOW(), INTERVAL 14 DAY) AND datetime < DATE_SUB(NOW(), INTERVAL 6 DAY)');
$relevant->execute();

$count = $relevant->rowCount();

$upd = $db->prepare('UPDATE user_log SET ip = ? WHERE id = ?');
foreach ($relevant->fetchAll() as $row) {
    $id = $row['id'];
    $fullIP = $row['ip'];
    $parts = explode('.', $fullIP, 4);

    if (count($parts) !== 4) {
        Log::err(sprintf('[BLACKEN_IP_LOG_JOB] FAILED to blacken IP "%s".', $fullIP));
        continue;
    }

    $parts[3] = 'XXX';
    $blackenedIP = implode('.', $parts);

    $upd->execute(array($blackenedIP, $id));
}

if ($count > 0) {
    Log::info(sprintf('[BLACKEN_IP_LOG_JOB] Blackened %d IPs from database.', $count));
}

Log::info('[BLACKEN_IP_LOG_JOB] Finished.');
