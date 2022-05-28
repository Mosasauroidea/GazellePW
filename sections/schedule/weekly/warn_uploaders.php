<?php

// Send warnings to uploaders of torrents that will be deleted this week
$DB->query("
			SELECT
				t.ID,
				t.GroupID,
				tg.Name,
				t.UserID
			FROM torrents AS t
				JOIN torrents_group AS tg ON tg.ID = t.GroupID
				JOIN users_info AS u ON u.UserID = t.UserID
			WHERE t.last_action < NOW() - INTERVAL 20 DAY
				AND t.last_action != 0
				AND u.UnseededAlerts = '1'
			ORDER BY t.last_action ASC");
$TorrentIDs = $DB->to_array();
$TorrentAlerts = array();
foreach ($TorrentIDs as $TorrentID) {
    list($ID, $GroupID, $Name,  $UserID) = $TorrentID;

    if (array_key_exists($UserID, $InactivityExceptionsMade) && (time() < $InactivityExceptionsMade[$UserID])) {
        // don't notify exceptions
        continue;
    }

    if (!array_key_exists($UserID, $TorrentAlerts))
        $TorrentAlerts[$UserID] = array('Count' => 0, 'Msg' => '');
    $ArtistName = Artists::display_artists(Artists::get_artist($GroupID), false, false, false, $UserID);
    if ($ArtistName) {
        $Name = "$ArtistName - $Name";
    }

    $TorrentAlerts[$UserID]['Msg'] .= "\n[url=" . site_url() . "torrents.php?torrentid=$ID]" . $Name . "[/url]";
    $TorrentAlerts[$UserID]['Count']++;
}
foreach ($TorrentAlerts as $UserID => $MessageInfo) {
    Misc::send_pm_with_tpl($UserID, 'unseeded_notification', ['Count' => $MessagInfo['Count'], 'MessageInfo' => $MessageInfo['Msg']]);
}
