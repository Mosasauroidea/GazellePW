<?php

ini_set('max_file_uploads', 1);
define('MAX_FILENAME_LENGTH', 255);

include(CONFIG['SERVER_ROOT'] . '/classes/file_checker.class.php');

enforce_login();

$File = $_FILES['file_input']; // This is our torrent file
$SubtitleName = $File['tmp_name'];
$Name = $File['name'];
$SubtitleFormat = get_file_extension($Name);
$AllowedFormat = ["sub", "idx", "sup", "srt", "vtt", "ass", "smi", "ssa", "rar", "7z", "zip", "tar", "tar.gz", ".tgz"];
preg_match('/torrentid=(\d+)/', $_POST['torrent_pl_link'], $IDMatch);
if ($IDMatch[1]) {
    $TorrentID = $IDMatch[1];
} else {
    $Err = t('server.subtitles.lack_of_torrent_permalink');
}
if (empty($_POST['languages'])) {
    $Err = t('server.subtitles.please_select_language');
}
$Source = $_POST['source'] ? $_POST['source'] : '';
$Languages = implode(',', $_POST['languages']);
$Size = filesize($SubtitleName);
if (empty($SubtitleName) || !is_uploaded_file($SubtitleName) || !filesize($SubtitleName)) {
    $Err = t('server.subtitles.please_choose_a_subtitle_file');
} elseif (!in_array($SubtitleFormat, $AllowedFormat)) {
    $Err = t('server.subtitles.please_upload_supported_subtitle_formats');
}
if ($Err) {
    include(CONFIG['SERVER_ROOT'] . '/sections/subtitles/upload.php');
    die($Err);
}
$file_data = file_get_contents($SubtitleName);
$DB->query(
    "INSERT INTO subtitles (languages, torrent_id, `source`, download_times, format, size, uploader, upload_time, name) VALUES(
    '" . db_string($Languages) . "', " . $TorrentID . ", '" . db_string($Source) . "', 0, '" . addslashes($SubtitleFormat) . "', $Size, " . $LoggedUser['ID'] . ", '" . sqltime() . "', '" . db_string($Name) . "')"
);
$SubtitleID = $DB->inserted_id();
$DB->query("INSERT INTO subtitles_files (ID, File) VALUES($SubtitleID, '" . db_string($file_data) . "')");
$DB->query("SELECT GroupID from torrents where ID=" . $TorrentID);
list($GroupID) = $DB->next_record(MYSQLI_BOTH, false);
$Cache->delete_value("torrents_details_$GroupID");
$Cache->delete_value("torrent_sub_title_$TorrentID");
header("Location: torrents.php?torrentid=$TorrentID#torrentid$TorrentID");
