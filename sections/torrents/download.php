<?
if (!isset($_REQUEST['authkey']) || !isset($_REQUEST['torrent_pass'])) {
    enforce_login();
    $TorrentPass = $LoggedUser['torrent_pass'];
    $DownloadAlt = $LoggedUser['DownloadAlt'];
    $UserID = $LoggedUser['ID'];
    $AuthKey = $LoggedUser['AuthKey'];
} else {
    if (strpos($_REQUEST['torrent_pass'], '_') !== false) {
        error(404);
    }

    $UserInfo = $Cache->get_value('user_' . $_REQUEST['torrent_pass']);
    if (!is_array($UserInfo)) {
        $DB->query("
			SELECT ID, DownloadAlt, la.UserID
			FROM users_main AS m
				INNER JOIN users_info AS i ON i.UserID = m.ID
				LEFT JOIN locked_accounts AS la ON la.UserID = m.ID
			WHERE m.torrent_pass = '" . db_string($_REQUEST['torrent_pass']) . "'
				AND m.Enabled = '1'");
        $UserInfo = $DB->next_record(MYSQLI_NUM, array(2));
        $SiteOptions = array_merge(Users::default_site_options(), unserialize_array($UserInfo[2]));
        $Cache->cache_value('user_' . $_REQUEST['torrent_pass'], $UserInfo, 3600);
    }
    $UserInfo = array($UserInfo);
    list($UserID, $DownloadAlt, $Locked) = array_shift($UserInfo);
    if (!$UserID) {
        error(0);
    }
    $TorrentPass = $_REQUEST['torrent_pass'];
    $AuthKey = $_REQUEST['authkey'];

    if ($Locked == $UserID) {
        header('HTTP/1.1 403 Forbidden');
        die();
    }
}

$TorrentID = $_REQUEST['id'];

if (!is_number($TorrentID)) {
    error(0);
}

/* uTorrent Remote and various scripts redownload .torrent files periodically.
    To prevent this retardation from blowing bandwidth etc., let's block it
    if the .torrent file has been downloaded four times before */
$ScriptUAs = array('BTWebClient*', 'Python-urllib*', 'python-requests*', 'uTorrent*');
if (Misc::in_array_partial($_SERVER['HTTP_USER_AGENT'], $ScriptUAs)) {
    $DB->query("
		SELECT 1
		FROM users_downloads
		WHERE UserID = $UserID
			AND TorrentID = $TorrentID
		LIMIT 4");
    if ($DB->record_count() === 4) {
        error(Lang::get('torrents', 'error_downloaded_to_many_times'), true);
        die();
    }
}

$Info = $Cache->get_value('torrent_download_' . $TorrentID);
if (!is_array($Info) || !array_key_exists('PlainArtists', $Info) || empty($Info[10])) {
    $DB->query("
		SELECT
			t.Source,
			t.Codec,
			t.Container,
			t.Resolution, 
			t.Processing,
			IF(t.RemasterYear = 0, tg.Year, t.RemasterYear),
			tg.ID AS GroupID,
			tg.Name,
			tg.WikiImage,
			tg.CategoryID,
			t.Size,
			t.FreeTorrent,
			t.info_hash,
			t.UserID,
            tg.SubName,
            tg.Year
		FROM torrents AS t
			INNER JOIN torrents_group AS tg ON tg.ID = t.GroupID
		WHERE t.ID = '" . db_string($TorrentID) . "'");
    if (!$DB->has_results()) {
        error(404);
    }
    $Info = array($DB->next_record(MYSQLI_NUM, array(7, 8, 12, 14)));
    $Artists = Artists::get_artist($Info[0][4]);
    $Info['Artists'] = Artists::display_artists($Artists, false, true);
    $Info['PlainArtists'] = Artists::display_artists($Artists, false, true, false);
    $Info['Sitename'] = '[' . CONFIG['SITE_NAME'] . ']  ';
    $Cache->cache_value("torrent_download_$TorrentID", $Info, 0);
}
if (!is_array($Info[0])) {
    error(404);
}
list($Source, $Codec, $Container, $Resolution, $Processing, $Year, $GroupID, $Name, $Image, $CategoryID, $Size, $FreeTorrent, $InfoHash, $TorrentUploaderID, $SubName, $Year) = array_shift($Info); // used for generating the filename
$Artists = $Info['Artists'];

// If he's trying use a token on this, we need to make sure he has one,
// deduct it, add this to the FLs table, and update his cache key.
if ($_REQUEST['usetoken'] && in_array($FreeTorrent, ['0', '11', '12', '13']) && !Torrents::global_freeleech()) {
    if (isset($LoggedUser)) {
        $FLTokens = $LoggedUser['FLTokens'];
        if ($LoggedUser['CanLeech'] != '1') {
            error(Lang::get('torrents', 'error_leech_disabled'));
        }
    } else {
        $UInfo = Users::user_heavy_info($UserID);
        if ($UInfo['CanLeech'] != '1') {
            error(Lang::get('torrents', 'error_leech_disabled_may'));
        }
        $FLTokens = $UInfo['FLTokens'];
    }

    // First make sure this isn't already FL, and if it is, do nothing

    if (!Torrents::has_token($TorrentID)) {
        $TokenUses = ceil($Size / (5 * 1024 * 1024 * 1024));
        if ($FLTokens < $TokenUses) {
            error(Lang::get('torrents', 'error_tokens_not_enough'));
        }
        /*
        if ($Size >= 2147483648) {
            error('This torrent is too large. Please use the regular DL link.');
        }
        */
        // Let the tracker know about this
        if (!Tracker::update_tracker('add_token', array('info_hash' => rawurlencode($InfoHash), 'userid' => $UserID))) {
            error(Lang::get('torrents', 'error_occurred_to_token'));
        }

        if (!Torrents::has_token($TorrentID)) {
            $DB->query("
				INSERT INTO users_freeleeches (UserID, TorrentID, Time)
				VALUES ($UserID, $TorrentID, NOW())
				ON DUPLICATE KEY UPDATE
					Time = VALUES(Time),
					Expired = FALSE,
					Uses = Uses + $TokenUses");
            $DB->query("
				INSERT INTO users_freeleeches_time (UserID, TorrentID, Time)
				VALUES ($UserID, $TorrentID, NOW())");
            $DB->query("
				UPDATE users_main
				SET FLTokens = FLTokens - $TokenUses
				WHERE ID = $UserID");
            for ($i = 0; $i < $TokenUses; $i++) {
                $DB->query(
                    "select ID from (SELECT ID FROM `tokens_typed` WHERE UserID='$UserID' and Type='time' ORDER BY `EndTime`) a
					union all
					select ID from (select ID FROM `tokens_typed` WHERE UserID='$UserID' and Type='count') b 
					limit 1"
                );
                $UsedTokenID = $DB->collect('ID');
                if (count($UsedTokenID) > 0) {
                    $DB->query("delete from `tokens_typed` WHERE ID = '$UsedTokenID[0]'");
                } else {
                    break;
                }
            }
            // Fix for downloadthemall messing with the cached token count
            $UInfo = Users::user_heavy_info($UserID);
            $FLTokens = $UInfo['FLTokens'];
            $TimedTokens = $UInfo['TimedTokens'];
            $Cache->begin_transaction("user_info_heavy_$UserID");
            $Cache->update_row(false, array('FLTokens' => ($FLTokens - $TokenUses), 'TimedTokens' => ($TimedTokens >= $TokenUses ? $TimedTokens - $TokenUses : 0)));
            $Cache->commit_transaction(0);

            $Cache->delete_value("users_tokens_$UserID");
        }
    }
}

//Stupid Recent Snatches On User Page
if ($CategoryID == '1' && $Image != '' && $TorrentUploaderID != $UserID) {
    $RecentSnatches = $Cache->get_value("recent_snatches_$UserID");
    if (!empty($RecentSnatches)) {
        $Snatch = array(
            'ID' => $GroupID,
            'Name' => $Name,
            'SubName' => $SubName,
            'Year' => $Year,
            'WikiImage' => $Image
        );
        if (!in_array($Snatch, $RecentSnatches)) {
            if (count($RecentSnatches) === 5) {
                array_pop($RecentSnatches);
            }
            array_unshift($RecentSnatches, $Snatch);
        } elseif (!is_array($RecentSnatches)) {
            $RecentSnatches = array($Snatch);
        }
        $Cache->cache_value("recent_snatches_$UserID", $RecentSnatches, 0);
    }
}

$DB->query("
	INSERT IGNORE INTO users_downloads (UserID, TorrentID, Time)
	VALUES ('$UserID', '$TorrentID', '" . sqltime() . "')");

$DB->query("
	SELECT File
	FROM torrents_files
	WHERE TorrentID = '$TorrentID'");

Torrents::set_snatch_update_time($UserID, Torrents::SNATCHED_UPDATE_AFTERDL);
list($Contents) = $DB->next_record(MYSQLI_NUM, false);

$FileName = TorrentsDL::construct_file_name($Name, $SubName, $Source, $Codec, $Container, $Resolution, $Processing, $Year, $TorrentID);
$AnnounceURL = CONFIG['ANNOUNCE_URL'];
if ($DownloadAlt) {
    header('Content-Type: text/plain; charset=utf-8');
} elseif (!$DownloadAlt || $Failed) {
    header('Content-Type: application/x-bittorrent; charset=utf-8');
}
header('Content-disposition: attachment; filename="[' . CONFIG['SITE_NAME'] . ']' . $FileName . '"');

echo TorrentsDL::get_file($Contents, $AnnounceURL . "/$TorrentPass/announce", $TorrentID);

define('SKIP_NO_CACHE_HEADERS', 1);
