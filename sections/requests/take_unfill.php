<?
//******************************************************************************//
//--------------- Take unfill request ------------------------------------------//

authorize();

$RequestID = $_POST['id'];
if (!is_number($RequestID)) {
    error(0);
}

$DB->query("
	SELECT
		r.CategoryID,
		r.UserID,
		r.FillerID,
		r.Title,
		r.Title as Name,
		r.Subtitle as SubName,
		r.Year,
		u.Uploaded,
		r.GroupID
	FROM requests AS r
		LEFT JOIN users_main AS u ON u.ID = FillerID
	WHERE r.ID = $RequestID");
$Request = G::$DB->next_record(MYSQLI_ASSOC);
$FullName = Torrents::group_name($Request, false);
$CategoryID = $Request['CategoryID'];
$UserID = $Request['UserID'];
$FillerID = $Request['FillerID'];
$Uploaded = $Request['Uploaded'];
$GruopID = $Request['GroupID'];
if ((($LoggedUser['ID'] !== $UserID && $LoggedUser['ID'] !== $FillerID) && !check_perms('site_moderate_requests')) || $FillerID === '0') {
    error(403);
}

// Unfill
$DB->query("
	UPDATE requests
	SET TorrentID = 0,
		FillerID = 0,
		TimeFilled = '0000-00-00 00:00:00',
		Visible = 1
	WHERE ID = $RequestID");

$CategoryName = $Categories[$CategoryID - 1];


$RequestVotes = Requests::get_votes_array($RequestID);
if ($RequestVotes['TotalBounty'] > $Uploaded) {
    // If we can't take it all out of upload, zero that out and add whatever is left as download.
    $DB->query("
		UPDATE users_main
		SET Uploaded = 0
		WHERE ID = $FillerID");
    $DB->query('
		UPDATE users_main
		SET Downloaded = Downloaded + ' . ($RequestVotes['TotalBounty'] - $Uploaded) . "
		WHERE ID = $FillerID");
} else {
    $DB->query('
		UPDATE users_main
		SET Uploaded = Uploaded - ' . $RequestVotes['TotalBounty'] . "
		WHERE ID = $FillerID");
}
$FillerLang = Lang::getUserLang($FillerID);
Misc::send_pm_with_tpl(
    $FillerID,
    'request_filled_unfilled',
    [
        'RequestID' => $RequestID,
        'FullName' => $FullName,
        'Reason' => $_POST['reason'],
        'LoggedUserID' =>  $LoggedUser['ID'],
        'LoggedUserUsername' =>  $LoggedUser['Username'],
    ]
);

$Cache->delete_value("user_stats_$FillerID");

if ($UserID !== $LoggedUser['ID']) {
    $ToUserLang = Lang::getUserLang($UserID);
    Misc::send_pm_with_tpl($UserID, 'request_created_unfilled', [
        'Reason' => $_POST['reason'],
        'RequestID' => $RequestID,
        'FullName' => $FullName,
        'LoggedUserID' => $LoggedUser['ID'],
        'LoggedUserUsername' => $LoggedUser['Username'],
        'CONFIG' => CONFIG,
    ]);
}

Misc::write_log("Request $RequestID ($FullName), with a " . Format::get_size($RequestVotes['TotalBounty']) . ' bounty, was unfilled by user ' . $LoggedUser['ID'] . ' (' . $LoggedUser['Username'] . ') for the reason: ' . $_POST['reason']);

$Cache->delete_value("request_$RequestID");
$Cache->delete_value("request_artists_$RequestID");
if ($GroupID) {
    $Cache->delete_value("requests_group_$GroupID");
}

Requests::update_sphinx_requests($RequestID);

if (!empty($ArtistForm)) {
    foreach ($ArtistForm as $ArtistType) {
        foreach ($ArtistType as $Artist) {
            $Cache->delete_value('artists_requests_' . $Artist['id']);
        }
    }
}

$SphQL = new SphinxqlQuery();
$SphQL->raw_query("
		UPDATE requests, requests_delta
		SET torrentid = 0, fillerid = 0
		WHERE id = $RequestID", false);

header("Location: requests.php?action=view&id=$RequestID");
