<?
$TorrentID = (int)$_GET['torrentid'];

$DB->query("
	SELECT last_action, LastReseedRequest, UserID, Time, GroupID
	FROM torrents
	WHERE ID = '$TorrentID'");
list($LastActive, $LastReseedRequest, $UploaderID, $UploadedTime, $GroupID) = $DB->next_record();

if (!check_perms('users_mod')) {
    if (time() - strtotime($LastReseedRequest) < 864000) {
        error(t('server.torrents.already_a_re_seed_request'));
    }
    if ($LastActive == '0000-00-00 00:00:00' || time() - strtotime($LastActive) < 345678) {
        error(403);
    }
}

$DB->query("
	UPDATE torrents
	SET LastReseedRequest = NOW()
	WHERE ID = '$TorrentID'");

$usersToNotify = array();

$DB->query("
	SELECT s.uid AS id, MAX(s.tstamp) AS tstamp
	FROM xbt_snatched as s
	INNER JOIN users_main as u
	ON s.uid = u.ID
	WHERE s.fid = '$TorrentID'
	AND u.Enabled = '1'
	GROUP BY s.uid
       ORDER BY tstamp DESC
	LIMIT 100");
if ($DB->has_results()) {
    $Users = $DB->to_array();
    foreach ($Users as $User) {
        $UserID = $User['id'];
        $TimeStamp = $User['tstamp'];

        $usersToNotify[$UserID] = array("snatched", $TimeStamp);
    }
}

$usersToNotify[$UploaderID] = array("uploaded", strtotime($UploadedTime));
$Torrent = TOrrents::get_torrent($TorrentID);
$Name = Torrents::torrent_name($Torrent, false);

foreach ($usersToNotify as $UserID => $info) {
    $Username = Users::user_info($UserID)['Username'];
    list($action, $TimeStamp) = $info;
    Misc::send_pm_with_tpl(
        $UserID,
        'reseed_request',
        [
            'UserName' => $Username,
            'LoggedUserID' => $LoggedUser['ID'],
            'LoggedUserName' => $LoggedUser['Username'],
            'Date' => date('M d Y', $TimeStamp),
            'Action' => $action,
            'GroupID' => $GroupID,
            'TorrentID' => $TorrentID,
            'Name' => $Name,
        ]
    );
}

$NumUsers = count($usersToNotify);

View::show_header('', '', 'PageTorrentReseed');
?>
<div class="LayoutBody">
    <div class="BodyHeader">
        <h2 class="BodyHeader-nav"><?= t('server.torrents.successfully_sent_re_seed_request') ?></h2>
    </div>
    <div>
        <p><?= t('server.torrents.successfully_sent_re_seed_request_for_torrent') ?><?= Torrents::torrent_simple_view($Torrent['Group'], $Torrent) ?><?= t('server.torrents.space_to_space') ?><?= t('server.torrents.n_user', ['Count' => $NumUsers, 'Values' => [$NumUsers]]) ?></p>
    </div>
</div>
<?
View::show_footer();
?>