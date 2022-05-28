<?php

//------------- Delete dead torrents ------------------------------------//

sleep(5);
$DB->query("SELECT UserID, count(*) Count FROM `invites_typed` WHERE Type='time' and Used=0 and EndTime < NOW() GROUP BY UserID");
$Users = $DB->to_array(false, MYSQLI_NUM, false);
foreach ($Users as $User) {
    $DB->query("update users_main set Invites=Invites-$User[1] where ID=$User[0]");
    $Cache->delete_value('user_info_heavy_' . $User[0]);
}
$DB->query("DELETE from invites_typed where Type='time' and Used=0 and EndTime < NOW()");
