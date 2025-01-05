<?
//******************************************************************************//
//--------------- Fill a request -----------------------------------------------//

use Gazelle\Manager\ActionTrigger;

$RequestID = $_REQUEST['requestid'];
if (!is_number($RequestID)) {
    error(0);
}

authorize();

$DB->query("
	SELECT
		Title,
		UserID,
		TorrentID,
		CategoryID,
		ReleaseType,
        Year,
        Title as Name,
        Subtitle as SubName,
        RequestType,
        SourceTorrent
	FROM requests
	WHERE ID = $RequestID");
$Data = $DB->next_record(MYSQLI_ASSOC, false);
list($Title, $RequesterID, $OldTorrentID, $RequestCategoryID, $RequestReleaseType, $Year, $Name, $SubName, $RequestType, $SourceTorrent) =  array_values($Data);

if (!empty($OldTorrentID)) {
    $Err = t('server.requests.already_filled');
}

$RequestGroupName = Torrents::group_name($Data, false);

$FillerID = $LoggedUser['ID'];
$FillerUsername = $LoggedUser['Username'];

if (!empty($_POST['user']) && check_perms('site_moderate_requests')) {
    $FillerUsername = $_POST['user'];
    $DB->query("
		SELECT ID
		FROM users_main
		WHERE Username LIKE '" . db_string($FillerUsername) . "'");
    if (!$DB->has_results()) {
        $Err = t('server.requests.no_such_user_to_fill');
    } else {
        list($FillerID) = $DB->next_record();
    }
}

if ($RequestType == 1) {
    //VALIDATION
    if (!empty($_GET['torrentid']) && is_number($_GET['torrentid'])) {
        $TorrentID = $_GET['torrentid'];
    } else {
        if (empty($_POST['link'])) {
            error(t('server.requests.forgot_link_fill_torrent'));
        } else {
            $Link = $_POST['link'];
            if (!preg_match('/' . TORRENT_REGEX . '/i', $Link, $Matches)) {
                error(t('server.requests.link_not_valid'));
            } else {
                $TorrentID = $Matches[2];
            }
        }
        if (!$TorrentID || !is_number($TorrentID)) {
            error(404);
        }
    }
} else {
    $Link =  $SourceTorrent;
    if (!preg_match('/' . TORRENT_REGEX . '/i', $Link, $Matches)) {
        error('invlaid request');
    }
    $SourceTorrentID = $Matches[2];
    $Result = $DB->query(
        "SELECT xu.uid
	        FROM xbt_files_users AS xu
	    WHERE xu.fid = '$SourceTorrentID' and xu.uid = $FillerID and active = 1 and xu.remaining = 0 "
    );
    if (!$DB->has_results()) {
        $Err = t('server.requests.cant_fill');
    }
    $TorrentID = $SourceTorrentID;
}
//Torrent exists, check it's applicable
$DB->query("
	SELECT
		t.UserID,
		t.Time,
		tg.ReleaseType,
		tg.CategoryID,
        t.GroupID
	FROM torrents AS t
		LEFT JOIN torrents_group AS tg ON t.GroupID = tg.ID
	WHERE t.ID = $TorrentID
	LIMIT 1");

if (!$DB->has_results()) {
    error(404);
}
list($UploaderID, $UploadTime, $TorrentReleaseType, $TorrentCategoryID, $GroupID) = $DB->next_record();
if ($RequestType == 1) {
    if (time_ago($UploadTime) < 3600 && $UploaderID !== $FillerID && !check_perms('site_moderate_requests')) {
        $Err = t('server.requests.one_hour_grace_period');
    }
}

$CategoryName = $Categories[$RequestCategoryID - 1];
if ($RequestCategoryID !== '0' && $TorrentCategoryID !== $RequestCategoryID) {
    $Err = t('server.requests.torrent_category_different_from_torrent');
}

// Fill request
if (!empty($Err)) {
    error($Err);
}

//We're all good! Fill!
$DB->query("
	UPDATE requests
	SET FillerID = $FillerID,
		TorrentID = $TorrentID,
		TimeFilled = '" . sqltime() . "'
	WHERE ID = $RequestID");


$FullName = $RequestGroupName;

$DB->query("
	SELECT UserID
	FROM requests_votes
	WHERE RequestID = $RequestID");
$UserIDs = $DB->to_array();
foreach ($UserIDs as $User) {
    list($VoterID) = $User;
    Misc::send_pm_with_tpl($VoterID, 'request_filled', ['FullName' => $FullName, 'RequestID' => $RequestID, 'TorrentID' => $TorrentID, 'RequestType' => $RequestType]);
}

$RequestVotes = Requests::get_votes_array($RequestID);
Misc::write_log("Request $RequestID ($FullName) was filled by user $FillerID ($FillerUsername) with the torrent $TorrentID for a " . Format::get_size($RequestVotes['TotalBounty']) . ' bounty.');

// Give bounty
$DB->query("
	UPDATE users_main
	SET Uploaded = (Uploaded + " . $RequestVotes['TotalBounty'] . ")
	WHERE ID = $FillerID");

// free

if ($FreeHour = CONFIG['REQUEST_FILL_FREE_HOUR']) {
    Torrents::freeleech_torrents($TorrentID, 1, 0, false, date('Y-m-d H:i:s', strtotime("+$FreeHour Hour")));
}


$Cache->delete_value("user_stats_$FillerID");
$Cache->delete_value("request_$RequestID");
if ($GroupID) {
    $Cache->delete_value("requests_group_$GroupID");
}



$DB->query("
	SELECT ArtistID
	FROM requests_artists
	WHERE RequestID = $RequestID");
$ArtistIDs = $DB->to_array();
foreach ($ArtistIDs as $ArtistID) {
    $Cache->delete_value("artists_requests_$ArtistID");
}
$trigger = new ActionTrigger;
$trigger->triggerFillRequest($RequestID, $TorrentID);

Requests::update_sphinx_requests($RequestID);
$SphQL = new SphinxqlQuery();
$SphQL->raw_query("UPDATE requests, requests_delta SET torrentid = $TorrentID, fillerid = $FillerID WHERE id = $RequestID", false);




header("Location: requests.php?action=view&id=$RequestID");
