<?
authorize();

$TorrentID = $_POST['torrentid'];
if (!$TorrentID || !is_number($TorrentID)) {
    error(404);
}

if ($Cache->get_value("torrent_{$TorrentID}_lock")) {
    error('Torrent cannot be deleted because the upload process is not completed yet. Please try again later.');
}

$DB->query("
	SELECT
		t.UserID,
		t.GroupID,
		t.Size,
		t.info_hash,
		tg.Name,
		tg.SubName,
		tg.Year,
		t.Time,
        	t.Codec,
		t.Source,
		t.Resolution,
		t.Container,
		t.Processing,
		t.RemasterTitle,
		t.RemasterYear,
        t.FilePath,
		COUNT(x.uid)
	FROM torrents AS t
		LEFT JOIN torrents_group AS tg ON tg.ID = t.GroupID
		LEFT JOIN artists_group AS ag ON ag.ArtistID = tg.ArtistID
		LEFT JOIN xbt_snatched AS x ON x.fid = t.ID
	WHERE t.ID = '$TorrentID'");
$Data = G::$DB->next_record(MYSQLI_ASSOC, false);
list(
    $UserID,
    $GroupID,
    $Size,
    $InfoHash,
    $Name,
    $SubName,
    $Year,
    $Time,
    $Codec,
    $Source,
    $Resolution,
    $Container,
    $Processing,
    $RemasterTitle,
    $RemasterYear,
    $FilePath,
    $Snatches
) = array_values($Data);
$TorrentDetail = Torrents::get_torrent($TorrentID);
$RawName = Torrents::torrent_name($TorrentDetail, false);
if (($LoggedUser['ID'] != $UserID || time_ago($Time) > 3600 * 24 * 7 || $Snatches > 4) && !check_perms('torrents_delete')) {
    error(403);
}

if (isset($_SESSION['logged_user']['multi_delete'])) {
    if ($_SESSION['logged_user']['multi_delete'] >= 3 && !check_perms('torrents_delete_fast')) {
        error('You have recently deleted 3 torrents. Please contact a staff member if you need to delete more.');
    }
    $_SESSION['logged_user']['multi_delete']++;
} else {
    $_SESSION['logged_user']['multi_delete'] = 1;
}

$InfoHash = unpack('H*', $InfoHash);
Torrents::delete_torrent($TorrentID, $GroupID);
$Log = "Torrent $TorrentID ($RawName) (" . strtoupper($InfoHash[1]) . ") was deleted by " . $LoggedUser['Username'] . ': ' . $_POST['reason'] . ' ' . $_POST['extra'];
Torrents::send_pm($TorrentID, $UserID, $RawName, $Log, 0, G::$LoggedUser['ID'] != $UserID);
Misc::write_log($Log);
Torrents::write_group_log($GroupID, $TorrentID, $LoggedUser['ID'], 'deleted ' . $FilePath . ' (' . number_format($Size / (1024 * 1024 * 1024), 2) . ' GB) for reason: ' . $_POST['reason'] . ' ' . $_POST['extra'], 0);

View::show_header(t('server.torrents.torrent_deleted'), '', 'PageTorrentTakeDelete');
?>
<div class="LayoutBody">
    <h1><?= t('server.torrents.torrent_deleted_notice') ?></h1>
    <p><?= t('server.torrents.torrent_deleted_successfully') ?></p>
</div>
<?
View::show_footer();
