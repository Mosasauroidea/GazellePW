<?
/*
 * This is the AJAX page that gets called from the JavaScript
 * function NewReport(), any changes here should probably be
 * replicated on static.php.
 */
include(CONFIG['SERVER_ROOT'] . '/sections/torrents/functions.php');
include(CONFIG['SERVER_ROOT'] . '/classes/torrenttable.class.php');
include(CONFIG['SERVER_ROOT'] . '/sections/reportsv2/report_item.php');
if (!check_perms('admin_reports')) {
    error(403);
}


$DB->query("
	SELECT
		r.ID,
		r.ReporterID,
		reporter.Username,
		r.TorrentID,
		r.Type,
		r.UserComment,
        r.UploaderReply,
		r.ResolverID,
		resolver.Username,
		r.Status,
		r.ReportedTime,
		r.LastChangeTime,
		r.ModComment,
		r.Track,
		r.Image,
		r.ExtraID,
		r.Link,
		r.LogMessage,
		tg.Name,
		tg.ID,
		CASE COUNT(ta.GroupID)
			WHEN 1 THEN aa.ArtistID
			WHEN 0 THEN '0'
			ELSE '0'
		END AS ArtistID,
		CASE COUNT(ta.GroupID)
			WHEN 1 THEN aa.Name
			WHEN 0 THEN ''
			ELSE 'Various Artists'
		END AS ArtistName,
		tg.Year,
		tg.CategoryID,
		t.Time,
		t.RemasterTitle,
		t.RemasterYear,
        t.Codec,
		t.Source,
		t.Resolution,
		t.Container,
		t.Size,
		t.UserID AS UploaderID,
		uploader.Username,
        tg.SubName
	FROM reportsv2 AS r
		LEFT JOIN torrents AS t ON t.ID = r.TorrentID
		LEFT JOIN torrents_group AS tg ON tg.ID = t.GroupID
		LEFT JOIN torrents_artists AS ta ON ta.GroupID = tg.ID AND ta.Importance = '1'
		LEFT JOIN artists_alias AS aa ON aa.ArtistID = ta.ArtistID
		LEFT JOIN users_main AS resolver ON resolver.ID = r.ResolverID
		LEFT JOIN users_main AS reporter ON reporter.ID = r.ReporterID
		LEFT JOIN users_main AS uploader ON uploader.ID = t.UserID
	WHERE r.Status = 'New'
	GROUP BY r.ID
	ORDER BY ReportedTime ASC
	LIMIT 1");

if (!$DB->has_results()) {
    die();
}
$Report = G::$DB->next_record();
list(
    $ReportID, $ReporterID, $ReporterName, $TorrentID, $Type, $UserComment, $UploaderReply, $ResolverID, $ResolverName, $Status, $ReportedTime, $LastChangeTime,
    $ModComment, $Tracks, $Images, $ExtraIDs, $Links, $LogMessage, $GroupName, $GroupID, $ArtistID, $ArtistName, $Year, $CategoryID, $Time, $RemasterTitle,
    $RemasterYear, $Codec, $Source, $Resolution, $Container, $Size, $UploaderID, $UploaderName
) = Misc::display_array($Report, array('ModComment'));
$DB->query("
			UPDATE reportsv2
			SET Status = 'InProgress',
				ResolverID = " . $LoggedUser['ID'] . "
			WHERE ID = $ReportID");
// Mark status
$Report[9] = 'InProgress';
$Report[7] = $LoggedUser['ID'];
render_item($_GET['uniqurl'], $Report);
