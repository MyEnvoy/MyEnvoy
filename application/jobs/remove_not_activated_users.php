<?php

require __DIR__ . '/bootstrap_job.php';

Log::info('[DELETE_NOT_ACTIVE_JOB] Executing "remove_not_activated_users" ...');

$del = $db->prepare('DELETE u FROM user u
                        JOIN user_data ud ON ud.user_id = u.id
                        JOIN user_log ul ON ul.user_id = u.id
                    WHERE ul.action = "register"
                    AND ud.activated = 0
                    AND ul.datetime < DATE_SUB(NOW(), INTERVAL 10 HOUR)');
$del->execute();

$count = $del->rowCount();

if ($count > 0) {
    Log::info(sprintf('[DELETE_NOT_ACTIVE_JOB] Deleted %d not activated users from database.', $count));
}

Log::info('[DELETE_NOT_ACTIVE_JOB] Finished.');
