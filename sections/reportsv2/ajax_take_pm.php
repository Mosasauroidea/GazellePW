<?
/*
 * This is the AJAX backend for the SendNow() function.
 */

authorize();

if (!check_perms('admin_reports')) {
    die();
}

$TorrentID = $_POST['torrentid'];
$ReportID = $_POST['reportid'];

Reports::add_reports_messages($ReportID, $LoggedUser['ID'], $_POST['uploader_pm']);
if ($DB->affected_rows()) {
    $Cache->delete_value("reports_torrent_$TorrentID");
} else {
    error(403);
}

$UploaderID = $_POST['uploaderid'];
$ReporterID = $_POST['reporterid'];

Misc::send_pm_with_tpl($UploaderID, 'report_reply', ['TorrentID' => $TorrentID, 'Content' => $_POST['uploader_pm']]);
