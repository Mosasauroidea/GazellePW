<?
/*
 * This page handles the backend from when a user submits a report.
 * It checks for (in order):
 * 1. The usual POST injections, then checks that things.
 * 2. Things that are required by the report type are filled
 * 	('1' in the report_fields array).
 * 3. Things that are filled are filled with correct things.
 * 4. That the torrent you're reporting still exists.
 *
 * Then it just inserts the report to the DB and increments the counter.
 */

use Gazelle\Manager\ActionTrigger;

authorize();

if (!is_number($_POST['torrentid'])) {
    error(404);
} else {
    $TorrentID = $_POST['torrentid'];
}

if (!is_number($_POST['categoryid'])) {
    error(404);
} else {
    $CategoryID = $_POST['categoryid'];
}

if (!isset($_POST['type'])) {
    error(404);
} elseif (array_key_exists($_POST['type'], $Types[$CategoryID])) {
    $Type = $_POST['type'];
    $ReportType = $Types[$CategoryID][$Type];
} elseif (array_key_exists($_POST['type'], $Types['master'])) {
    $Type = $_POST['type'];
    $ReportType = $Types['master'][$Type];
} else {
    //There was a type but it wasn't an option!
    error(403);
}


foreach ($ReportType['report_fields'] as $Field => $Value) {
    if ($Value == '1') {
        if (empty($_POST[$Field])) {
            $Err = t('server.reportsv2.you_are_missing_a_required_filed_for_a_report', ['Values' => [$Field, $ReportType['title']]]);
        }
    }
}

if (!empty($_POST['sitelink'])) {
    if (preg_match_all('/' . TORRENT_REGEX . '/i', $_POST['sitelink'], $Matches)) {
        $ExtraIDs = implode(' ', $Matches[2]);
        if (in_array($TorrentID, $Matches[2])) {
            $Err = t('server.reportsv2.the_extra_pl_you_gave_included_the_link_to_the_torrent_you_are_reporting');
        }
    } else {
        $Err = t('server.reportsv2.the_pl_was_incorrect_it_should_look_like_torrents_php_torrentid_12345') . site_url() . "torrents.php?torrentid=12345";
    }
} else {
    $ExtraIDs = '';
}

if (!empty($_POST['link'])) {
    //resource_type://domain:port/filepathname?query_string#anchor
    //                  http://     www         .foo.com                                /bar
    if (preg_match_all('/' . URL_REGEX . '/is', $_POST['link'], $Matches)) {
        $Links = implode(' ', $Matches[0]);
    } else {
        $Err = t('server.reportsv2.the_extra_links_you_provided_were_not_links');
    }
} else {
    $Links = '';
}

if (!empty($_POST['image'])) {
    if (preg_match("/^(" . IMAGE_REGEX . ")( " . IMAGE_REGEX . ")*$/is", trim($_POST['image']), $Matches)) {
        $Images = $Matches[0];
    } else {
        $Err = t('server.reportsv2.the_extra_image_links_you_provided_were_not_links_to_images');
    }
} else {
    $Images = '';
}

if (!empty($_POST['track'])) {
    if (preg_match('/([0-9]+( [0-9]+)*)|All/is', $_POST['track'], $Matches)) {
        $Tracks = $Matches[0];
    } else {
        $Err = t('server.reportsv2.tracks_should_be_given_in_a_space_separated_list_of_numbers_with_no_other_characters');
    }
} else {
    $Tracks = '';
}

if (!empty($_POST['extra'])) {
    $Extra = db_string($_POST['extra']);
} else {
    $Err = t('server.reportsv2.as_useful_as_blank_reports_are_could_you_be_a_tiny_bit_more_helpful_leave_a_comment');
}

$DB->query("
	SELECT GroupID
	FROM torrents
	WHERE ID = $TorrentID");
if (!$DB->has_results()) {
    $Err = t('server.reportsv2.a_torrent_with_that_id_does_not_exist');
}
list($GroupID) = $DB->next_record();

if (!empty($Err)) {
    error($Err);
    include(CONFIG['SERVER_ROOT'] . '/sections/reportsv2/report.php');
    die();
}

$DB->query("
	SELECT ID
	FROM reportsv2
	WHERE TorrentID = $TorrentID
		AND ReporterID = " . db_string($LoggedUser['ID']) . "
		AND ReportedTime > '" . time_minus(3) . "'");
if ($DB->has_results()) {
    header("Location: torrents.php?torrentid=$TorrentID");
    die();
}

$DB->query("
	INSERT INTO reportsv2
		(ReporterID, TorrentID, Type, UserComment, Status, ReportedTime, Track, Image, ExtraID, Link)
	VALUES
		(" . db_string($LoggedUser['ID']) . ", $TorrentID, '" . db_string($Type) . "', '$Extra', 'New', '" . sqltime() . "', '" . db_string($Tracks) . "', '" . db_string($Images) . "', '" . db_string($ExtraIDs) . "', '" . db_string($Links) . "')");

$ReportID = $DB->inserted_id();

$trigger = new ActionTrigger;
$trigger->triggerReport($Type, $TorrentID, $ReportID);


if ($Type != "rescore" && $Type != "lossyapproval" && $Type != "upload_contest" && $Type != 'edited') {
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
        t.Source,
		t.Codec,
		t.Container,
		t.Resolution,
        t.Processing,
		t.RemasterTitle,
		t.RemasterYear
	FROM torrents AS t
		LEFT JOIN torrents_group AS tg ON tg.ID = t.GroupID
		LEFT JOIN users_info AS ui ON ui.UserID = t.UserID
	WHERE t.ID = '$TorrentID' and ui.ReportedAlerts = '1'");
    if ($DB->has_results()) {
        $Data = G::$DB->next_record(MYSQLI_ASSOC);
        list(
            $UserID,
            $GroupID,
            $Size,
            $InfoHash,
            $Name,
            $SubName,
            $Year,
            $Time,
            $Source,
            $Codec,
            $Container,
            $Resolution,
            $RemasterTitle,
            $RemasterYear
        ) = array_values($Data);
        $Torrent = Torrents::get_torrent($TorrentID);
        $RawName = Torrents::torrent_name($Torrent, false);

        $ToUserLang = Lang::getUserLang($UserID);
        include(CONFIG['SERVER_ROOT'] . '/classes/reportsv2_type.php');
        if (array_key_exists($_POST['type'], $Types[$CategoryID])) {
            $ToReportTitle = $Types[$CategoryID][$Type]['title'];
        } elseif (array_key_exists($_POST['type'], $Types['master'])) {
            $ToReportTitle = $Types['master'][$Type]['title'];
        }
        Misc::send_pm_with_tpl($UserID, 'torrent_reported', ['SiteURL' => CONFIG['SITE_URL'], 'TorrentID' => $TorrentID, 'RawName' => $RawName, 'ToReportTitle' => $ToReportTitle]);
    }
}
$Cache->delete_value("reports_torrent_$TorrentID");
G::$Cache->delete_value("torrent_group_$GroupID");
G::$Cache->delete_value("torrents_details_$GroupID");

$Cache->increment('num_torrent_reportsv2');
header("Location: torrents.php?torrentid=$TorrentID");
