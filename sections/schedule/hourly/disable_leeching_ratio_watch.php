<?php

// If a user has downloaded more than 10 GiBs while on ratio watch,
// disable leeching privileges, and send the user a message

$DB->query("
		SELECT ID, torrent_pass
		FROM users_info AS i
			JOIN users_main AS m ON m.ID = i.UserID
		WHERE i.RatioWatchEnds != '0000-00-00 00:00:00'
			AND i.RatioWatchDownload + 10 * 1024 * 1024 * 1024 < m.Downloaded
			AND m.Enabled = '1'
			AND m.can_leech = '1'");
$Users = $DB->to_pair('torrent_pass', 'ID');

if (count($Users) > 0) {
    $DB->query("
			UPDATE users_info AS i
				JOIN users_main AS m ON m.ID = i.UserID
			SET m.can_leech = '0',
				i.AdminComment = CONCAT('$sqltime - Leeching privileges disabled by ratio watch system for downloading more than 10 GBs on ratio watch. - required ratio: ', m.RequiredRatio, '\n\n', i.AdminComment)
			WHERE m.ID IN(" . implode(',', $Users) . ')');
    foreach ($Users as $TorrentPass => $UserID) {
        $Cache->begin_transaction("user_info_heavy_$UserID");
        $Cache->update_row(false, array('CanLeech' => 0));
        $Cache->commit_transaction(0);

        Misc::send_pm_with_tpl($UserID, 'leeching_disabled');
        Tracker::update_tracker('update_user', array('passkey' => $TorrentPass, 'can_leech' => '0'));
    }
}
