<?
//******************************************************************************//
//--------------- Delete request -----------------------------------------------//

authorize();

$SubtitleID = $_POST['id'];
if (!is_number($SubtitleID)) {
    error(0);
}

$DB->query("SELECT torrent_id, name, uploader from subtitles where ID=" . $SubtitleID);
list($TorrentID, $Name, $Uploader) = $DB->next_record(MYSQLI_BOTH, false);

if ($LoggedUser['ID'] != $Uploader && !check_perms('users_mod')) {
    error(403);
}

$DB->query("DELETE FROM subtitles WHERE id = $SubtitleID");
$DB->query("DELETE FROM subtitles_files WHERE id = $SubtitleID");

Misc::write_log("Subtitle $SubtitleID ($Name) was deleted by user " . $LoggedUser['ID'] . ' (' . $LoggedUser['Username'] . ') for the reason: ' . $_POST['reason']);
$DB->query("SELECT GroupID from torrents where ID=" . $TorrentID);
list($GroupID) = $DB->next_record(MYSQLI_BOTH, false);
$Cache->delete_value("torrents_details_$GroupID");
$Cache->delete_value("torrent_sub_title_$TorrentID");

header("Location: torrents.php?torrentid=$TorrentID#torrentid#$TorrentID");
