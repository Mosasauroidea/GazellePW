<?
enforce_login();

$SubtitleID = $_REQUEST['id'];
if (empty($SubtitleID) || !is_number($SubtitleID)) {
    error(404);
}

$DB->query('SELECT name FROM subtitles WHERE id=' . $SubtitleID);
list($Name) = $DB->next_record(MYSQLI_NUM, false);
$DB->query("update subtitles set download_times=download_times+1 where id=$SubtitleID");
$DB->query("SELECT torrent_id FROM subtitles where id=$SubtitleID");
list($TorrentID) = $DB->next_record(MYSQLI_NUM, false);
$DB->query("SELECT File FROM subtitles_files WHERE id=$SubtitleID");
list($Contents) = $DB->next_record(MYSQLI_NUM, false);
header("Content-Type:text/html;charset=utf-8");
header('Content-disposition: attachment; filename="' . $Name . '"');
echo $Contents;
$Cache->delete_value("torrent_sub_title_$TorrentID");
define('SKIP_NO_CACHE_HEADERS', 1);
