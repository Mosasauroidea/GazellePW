<?php
//------------------------ Update Bonus Points -------------------------//
// calcuation:
// Size (convert from bytes to GB) is in torrents
// Seedtime (convert from hours to days) is in xbt_snatched
// Seeders is in torrents
$DB->query("
	SELECT
		xfu.uid AS ID,
		SUM(IFNULL(t.Size / (1024 * 1024 * 1024) * 1 * (
			0.025 + (
				(0.06 * LN(1 + (xfh.seedtime / (24)))) / (POW(GREATEST(t.Seeders, 1), 0.6))
			)
		), 0)) AS NewPoints
	FROM
		(SELECT DISTINCT uid,fid FROM xbt_files_users WHERE active='1' AND remaining=0 AND mtime > unix_timestamp(NOW() - INTERVAL 1 HOUR)) AS xfu
		JOIN xbt_files_history AS xfh ON xfh.uid = xfu.uid AND xfh.fid = xfu.fid
		JOIN users_main AS um ON um.ID = xfu.uid
		JOIN users_info AS ui ON ui.UserID = xfu.uid
		JOIN torrents AS t ON t.ID = xfu.fid
	WHERE
		um.Enabled = '1' 
		AND ui.DisablePoints = '0'
	GROUP BY
		xfu.uid
");
$Bonus = $DB->to_array();
foreach ($Bonus as $B) {
	list($UserID, $NewPoints) = $B;
	if (empty($NewPoints)) {
		continue;
	}
	$DB->query("UPDATE users_main AS um SET um.BonusPoints=um.BonusPoints + ROUND($NewPoints, 5) where ID = $UserID");
	$Cache->delete_value('user_stats_' . $UserID);
}
