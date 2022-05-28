<?php

//------------- Delete dead torrents ------------------------------------//

sleep(5);
$DB->query("SELECT UserID, count(*) Count FROM `invites_typed` WHERE Type='time' and Used=0 and EndTime < DATE_FORMAT(NOW(),'%Y-%m-%d') GROUP BY UserID");
$Users = $DB->to_array(false, MYSQLI_NUM, false);
foreach ($Users as $User) {
    $DB->query("update users_main set Invites=Invites-$User[1] where ID=$User[0]");
    $Cache->delete_value('user_info_heavy_' . $User[0]);
}
$DB->query("delete from invites_typed where Type='time' and Used=0 and EndTime < DATE_FORMAT(NOW(),'%Y-%m-%d')");

$DB->query("SELECT UserID, count(*) Count FROM `tokens_typed` WHERE Type='time' and EndTime < DATE_FORMAT(NOW(),'%Y-%m-%d') GROUP BY UserID");
$Users = $DB->to_array(false, MYSQLI_NUM, false);
foreach ($Users as $User) {
    $DB->query("update users_main set FLTokens=FLTokens-$User[1] where ID=$User[0]");
    $Cache->delete_value('user_info_heavy_' . $User[0]);
}
$DB->query("delete from tokens_typed where Type='time' and EndTime < DATE_FORMAT(NOW(),'%Y-%m-%d')");
