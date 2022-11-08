<?php

// Send warnings to uploaders of torrents that will be deleted this week
$DB->query("
			SELECT
				t.ID,
			FROM torrents AS t
				JOIN users_info AS u ON u.UserID = t.UserID
			WHERE t.last_action < NOW() - INTERVAL 20 DAY
				AND t.last_action != 0
				AND u.UnseededAlerts = '1'
			ORDER BY t.last_action ASC");
$TorrentIDs = $DB->to_array();
$TorrentAlerts = array();
foreach ($TorrentIDs as $TorrentID) {
    list($ID) = $TorrentID;
    $TorrentInfo = Torrents::get_torrent($ID);
    $TorrentName = Torrents::torrent_name($TorrentInfo, false);
    $UserID = $TorrentInfo['UserID'];

    if (!array_key_exists($UserID, $TorrentAlerts))
        $TorrentAlerts[$UserID] = array('Count' => 0, 'Msg' => '');

    $TorrentAlerts[$UserID]['Msg'] .= "\n[url=" . site_url() . "torrents.php?torrentid=$ID]" . $TorrentName . "[/url]";
    $TorrentAlerts[$UserID]['Count']++;
}
foreach ($TorrentAlerts as $UserID => $MessageInfo) {
    Misc::send_pm_with_tpl($UserID, 'unseeded_notification', ['Count' => $MessagInfo['Count'], 'MessageInfo' => $MessageInfo['Msg']]);
}
