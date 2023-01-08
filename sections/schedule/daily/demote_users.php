<?php

//------------- Demote users --------------------------------------------//

sleep(10);

$UserDemotes = [];

$DeadPeriod = TORRENT_DEAD_PERIOD;

foreach ($UserDemoteCriteria as $L) {
    G::$DB->query("SELECT ID
				FROM users_main AS um
				WHERE PermissionID = " . $L['From'] . "
					AND (um.Downloaded <> 0 and um.Uploaded / um.Downloaded < '$L[MinRatio]'
					OR (
						SELECT COUNT(ID)
						FROM torrents
						WHERE UserID = um.ID and date_sub(NOW(), INTERVAL $DeadPeriod DAY) < last_action
						) < '$L[MinUploads]')");
    $UserIDs = G::$DB->collect('ID');
    foreach ($UserIDs as $UserID) {
        $NewClass = $L['To'];
        echo "demote user: $UserID to $NewClass\n";
        G::$DB->query("UPDATE users_main
		SET
			PermissionID = " . $NewClass . "
		WHERE ID = $UserID");
        G::$DB->query("UPDATE users_info 
		SET
			AdminComment = CONCAT('" . sqltime() . " - Class changed to " . Users::make_class_string($NewClass) . " by System\n\n', AdminComment)
		WHERE UserID = $UserID");
        $Cache->delete_value("user_info_$UserID");
        $Cache->delete_value("user_info_heavy_$UserID");
        Misc::send_pm_with_tpl($UserID, 'demote_users', ['UserClass' => Users::make_class_string($NewClass),  'SiteName' => CONFIG['SITE_NAME']]);
        sleep(1);
    }
}
echo "demoted finish\n";
