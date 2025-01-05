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
        error(t('server.torrents.error_downloaded_to_many_times'), true);
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
if ($_REQUEST['usetoken'] && in_array($FreeTorrent, [Torrents::Normal, Torrents::OneFourthOff, Torrents::TwoFourthOff, Torrents::ThreeFourthOff]) && !Torrents::global_freeleech()) {
    if (isset($LoggedUser)) {
        $FLTokens = $LoggedUser['FLTokens'];
        if ($LoggedUser['CanLeech'] != '1') {
            error(t('server.torrents.error_leech_disabled'));
        }
    } else {
        $UInfo = Users::user_heavy_info($UserID);
        if ($UInfo['CanLeech'] != '1') {
            error(t('server.torrents.error_leech_disabled_may'));
        }
        $FLTokens = $UInfo['FLTokens'];
    }

    // First make sure this isn't already FL, and if it is, do nothing

    if (!Torrents::has_token($TorrentID)) {
        $TokenUses = 1;
        if ($FLTokens < 1) {
            error(t('server.torrents.error_tokens_not_enough'));
        }
        // Let the tracker know about this
        if (!Tracker::update_tracker('add_token', array('info_hash' => rawurlencode($InfoHash), 'userid' => $UserID))) {
            error(t('server.torrents.error_occurred_to_token'));
        }
        G::$DB->begin_transaction();
        try {
            G::$DB->prepared_query("SELECT FLTokens FROM users_main where ID = $UserID");
            $TokenCount = G::$DB->collect('FLTokens');
            if ($TokenCount[0] < $TokenUses) {
                G::$DB->rollback();
                error(Lang::get('torrents', 'error_tokens_not_enough'));
            };
            G::$DB->prepared_query("
				INSERT INTO users_freeleeches (UserID, TorrentID, Time, Uses)
				VALUES ($UserID, $TorrentID, NOW(), $TokenUses)
				ON DUPLICATE KEY UPDATE
					Time = VALUES(Time),
					Expired = FALSE,
					Uses = Uses + $TokenUses");
            G::$DB->prepared_query("
				INSERT INTO users_freeleeches_time (UserID, TorrentID, Time)
				VALUES ($UserID, $TorrentID, NOW())");
            G::$DB->prepared_query("
				UPDATE users_main
				SET FLTokens = FLTokens - $TokenUses
				WHERE ID = $UserID");
            G::$DB->prepared_query("DELETE FROM tokens_typed WHERE ID in (SELECT tmp.ID FROM (SELECT ID FROM tokens_typed WHERE UserID = $UserID order by EndTime limit $TokenUses) tmp)");
        } catch (Exception $e) {
            error_log($e);
            G::$DB->rollback();
        }
        G::$DB->commit();
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

//Stupid Recent Snatches On User Page
if ($CategoryID == '1' && $Image != '' && $TorrentUploaderID != $UserID) {
    $RecentSnatches = $Cache->delete_value("recent_snatches_$UserID");
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
