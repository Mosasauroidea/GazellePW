<?
if (!isset($_POST['reportid']) || !isset($_POST['torrentid']) || !isset($_POST['uploader_reply'])) {
    error(403);
}

$ReportID = intval($_POST['reportid']);
$TorrentID = intval($_POST['torrentid']);
$DB->query("select count(1) from torrents where id=$TorrentID and userid=" . $LoggedUser['ID']);
list($OwnTorrent) = $DB->next_record();
if ($OwnTorrent) {
    Reports::add_reports_messages($ReportID, $LoggedUser['ID'], $_POST['uploader_reply']);
    if ($DB->affected_rows()) {
        $Cache->delete_value("reports_torrent_$TorrentID");
        header("Location: torrents.php?torrentid=$TorrentID");
    } else {
        error(403);
    }
} else {
    error(403);
}
