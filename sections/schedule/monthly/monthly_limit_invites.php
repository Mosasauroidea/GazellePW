<?php

//------------- Delete dead torrents ------------------------------------//
sleep(10);
$Criteria = array();
$Criteria[] = array('Class' => TORRENT_MASTER, 'Limit' => 1);
$Criteria[] = array('Class' => POWER_TM, 'Limit' => 2);
$Criteria[] = array('Class' => ELITE_TM, 'Limit' => 3);
foreach ($Criteria as $L) {
    $SQL = "select ID from users_main where PermissionID=" . $L['Class'];
    $DB->query($SQL);
    $Users = $DB->to_array(false, MYSQLI_NUM, false);
    foreach ($Users as $User) {
        list($UserID) = $User;
        $DB->query("update users_main set Invites=Invites+" . $L['Limit'] . " where ID=$UserID");
        for ($i = 0; $i < $L['Limit']; $i++) {
            $DB->query("INSERT into invites_typed (`UserID`,`EndTime`,`Type`) 
                VALUES ($UserID, date_add(now(), INTERVAL 14 DAY), 'time')");
        }
        $Cache->delete_value("user_info_heavy_$UserID");
    }
}
