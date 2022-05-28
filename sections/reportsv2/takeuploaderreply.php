<?
if (!isset($_POST['reportid']) || !isset($_POST['torrentid']) || !isset($_POST['uploader_reply'])) {
    error(403);
}

$ReportID = intval($_POST['reportid']);
$TorrentID = intval($_POST['torrentid']);
$DB->query("select count(1) from torrents where id=$TorrentID and userid=" . $LoggedUser['ID']);
list($OwnTorrent) = $DB->next_record();
if ($OwnTorrent) {
    $DB->query("UPDATE `reportsv2` SET `UploaderReply`='" . db_string($_POST['uploader_reply']) . "',`ReplyTime`=now() WHERE ID=$ReportID and TorrentID=$TorrentID");
    if ($DB->affected_rows()) {
        $Cache->delete_value("reports_torrent_$TorrentID");
        header("Location: torrents.php?torrentid=$TorrentID");
    } else {
        error(403);
    }
} else {
    error(403);
}
