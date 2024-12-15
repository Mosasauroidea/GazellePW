<?php
/*
 * This page is used for viewing reports in every viewpoint except auto.
 * It doesn't AJAX grab a new report when you resolve each one, use auto
 * for that (reports.php). If you wanted to add a new view, you'd simply
 * add to the case statement(s) below and add an entry to views.php to
 * explain it.
 * Any changes made to this page within the foreach loop should probably be
 * replicated on the auto page (reports.php).
 */
include(CONFIG['SERVER_ROOT'] . '/sections/torrents/functions.php');
include(CONFIG['SERVER_ROOT'] . '/classes/torrenttable.class.php');
include(CONFIG['SERVER_ROOT'] . '/sections/reportsv2/report_item.php');

if (!check_perms('admin_reports')) {
    error(403);
}

include(CONFIG['SERVER_ROOT'] . '/classes/reports.class.php');

define('REPORTS_PER_PAGE', '10');
list($Page, $Limit) = Format::page_limit(REPORTS_PER_PAGE);


if (isset($_GET['view'])) {
    $View = $_GET['view'];
} else {
    $View = 'unauto';
}

if (isset($_GET['id'])) {
    if (!is_number($_GET['id']) && $View !== 'type') {
        error(404);
    } else {
        $ID = db_string($_GET['id']);
    }
} else {
    $ID = '';
}

$Order = 'ORDER BY r.ReportedTime ASC';

if (!$ID) {
    switch ($View) {
        case 'resolved':
            $Title = t('server.reportsv2.all_the_old_smelly_reports');
            $Where = "WHERE r.Status = 'Resolved'";
            $Order = 'ORDER BY r.LastChangeTime DESC';
            break;
        case 'unauto':
            $Title = t('server.reportsv2.new_reports_not_auto_assigned');
            $Where = "WHERE r.Status = 'New'";
            break;
        default:
            error(404);
            break;
    }
} else {
    switch ($View) {
        case 'staff':
            $DB->query("
				SELECT Username
				FROM users_main
				WHERE ID = $ID");
            list($Username) = $DB->next_record();
            if ($Username) {
                $Title = "$Username" . t('server.reportsv2.s_in_progress_reports');
            } else {
                $Title = "$ID" . t('server.reportsv2.s_in_progress_reports');
            }
            $Where = "
				WHERE r.Status = 'InProgress'
					AND r.ResolverID = $ID";
            break;
        case 'resolver':
            $DB->query("
				SELECT Username
				FROM users_main
				WHERE ID = $ID");
            list($Username) = $DB->next_record();
            if ($Username) {
                $Title = "$Username" . t('server.reportsv2.s_resolved_reports');
            } else {
                $Title = "$ID" . t('server.reportsv2.s_resolved_reports');
            }
            $Where = "
				WHERE r.Status = 'Resolved'
					AND r.ResolverID = $ID";
            $Order = 'ORDER BY r.LastChangeTime DESC';
            break;
        case 'group':
            $Title = t('server.reportsv2.unresolved_reports_for_the_group', ['Values' => [$ID]]);
            $Where = "
				WHERE r.Status != 'Resolved'
					AND tg.ID = $ID";
            break;
        case 'torrent':
            $Title = t('server.reportsv2.all_reports_for_the_torrent', ['Values' => [$ID]]);
            $Where = "WHERE r.TorrentID = $ID";
            break;
        case 'report':
            $Title = t('server.reportsv2.viewing_resolution_of_report', ['Values' => [$ID]]);
            $Where = "WHERE r.ID = $ID";
            break;
        case 'reporter':
            $DB->query("
				SELECT Username
				FROM users_main
				WHERE ID = $ID");
            list($Username) = $DB->next_record();
            if ($Username) {
                $Title = t('server.reportsv2.all_torrents_reported_by', ['Values' => [$Username]]);
            } else {
                $Title = t('server.reportsv2.all_torrents_reported_by_user', ['Values' => [$ID]]);
            }
            $Where = "WHERE r.ReporterID = $ID";
            $Order = 'ORDER BY r.ReportedTime DESC';
            break;
        case 'uploader':
            $DB->query("
				SELECT Username
				FROM users_main
				WHERE ID = $ID");
            list($Username) = $DB->next_record();
            if ($Username) {
                $Title = t('server.reportsv2.all_reports_for_torrents_uploaded_by', ['Values' => [$Username]]);
            } else {
                $Title = t('server.reportsv2.all_reports_for_torrents_uploaded_by_user', ['Values' => [$ID]]);
            }
            $Where = "
				WHERE r.Status != 'Resolved'
					AND t.UserID = $ID";
            break;
        case 'type':
            $Title = t('server.reportsv2.all_new_reports_for_the_chosen_type');
            $Where = "
				WHERE r.Status = 'New'
					AND r.Type = '$ID'";
            break;
            break;
        default:
            error(404);
            break;
    }
}


$DB->query("
	SELECT
		SQL_CALC_FOUND_ROWS
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
	$Where
	GROUP BY r.ID
	$Order
	LIMIT $Limit");

$Reports = G::$DB->to_array(false, MYSQLI_NUM);

$DB->query('SELECT FOUND_ROWS()');
list($Results) = $DB->next_record();
$PageLinks = Format::get_pages($Page, $Results, REPORTS_PER_PAGE, 11);
$ReportMessages = Reports::get_reports_messages(array_column($Reports, '0'));
View::show_header(t('server.reportsv2.reports_v2'), 'reportsv2,bbcode,browse', 'PageReportV2Static');
?>
<div class="LayoutBody">
    <div class="BodyHeader">
        <h2 class="BodyHeader-nav"><?= $Title ?></h2>
        <? include('header.php'); ?>
    </div>
    <div class="BodyContent">
        <div>
            <? if ($View !== 'resolved') { ?>
                <span data-tooltip=" <?= t('server.reportsv2.multi_resolve_btn_title') ?>"><input class="Button" type="button" onclick="MultiResolve();" value="<?= t('server.reportsv2.multi_resolve') ?>" /></span>
                <span data-tooltip="<?= t('server.reportsv2.claim_all_btn_title') ?>"><input class="Button" type="button" onclick="Grab();" value="<?= t('server.reportsv2.claim_all') ?>" /></span>
            <?  }
            if ($View === 'staff' && $LoggedUser['ID'] == $ID) { ?>
                <span data-tooltip="<?= t('server.reportsv2.unclaim_all_btn_title') ?>"><input class="Button" type="button" onclick="GiveBack();" value="<?= t('server.reportsv2.unclaim_all') ?>" /></span>
            <?  } ?>
        </div>
        <? if ($PageLinks) { ?>
            <div class="BodyNavLinks">
                <?= $PageLinks ?>
            </div>
        <?  } ?>
        <div id="all_reports">
            <? if (count($Reports) === 0) { ?>
                <div class="BoxBody">
                    <strong><?= t('server.reportsv2.no_new_reports') ?></strong>
                </div>
            <?  } else { ?>
                <?
                foreach ($Reports as $Idx => $Report) {
                    render_item($Idx, $Report, $ReportMessages[$Report['0']]);
                ?>
                <? } ?>
            <? } ?>
        </div>
        <? if ($PageLinks) { ?>
            <div class="BodyNavLinks pager"><?= $PageLinks ?></div>
        <? } ?>
    </div>
</div>

<? View::show_footer(); ?>