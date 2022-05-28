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
include(SERVER_ROOT . '/sections/torrents/functions.php');
include(SERVER_ROOT . '/classes/torrenttable.class.php');

if (!check_perms('admin_reports')) {
    error(403);
}

include(SERVER_ROOT . '/classes/reports.class.php');

define('REPORTS_PER_PAGE', '10');
list($Page, $Limit) = Format::page_limit(REPORTS_PER_PAGE);


if (isset($_GET['view'])) {
    $View = $_GET['view'];
} else {
    error(404);
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
            $Title = Lang::get('reportsv2', 'all_the_old_smelly_reports');
            $Where = "WHERE r.Status = 'Resolved'";
            $Order = 'ORDER BY r.LastChangeTime DESC';
            break;
        case 'unauto':
            $Title = Lang::get('reportsv2', 'new_reports_not_auto_assigned');
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
                $Title = "$Username" . Lang::get('reportsv2', 's_in_progress_reports');
            } else {
                $Title = "$ID" . Lang::get('reportsv2', 's_in_progress_reports');
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
                $Title = "$Username" . Lang::get('reportsv2', 's_resolved_reports');
            } else {
                $Title = "$ID" . Lang::get('reportsv2', 's_resolved_reports');
            }
            $Where = "
				WHERE r.Status = 'Resolved'
					AND r.ResolverID = $ID";
            $Order = 'ORDER BY r.LastChangeTime DESC';
            break;
        case 'group':
            $Title = Lang::get('reportsv2', 'unresolved_reports_for_the_group_before') . "$ID" . Lang::get('reportsv2', 'unresolved_reports_for_the_group_after');
            $Where = "
				WHERE r.Status != 'Resolved'
					AND tg.ID = $ID";
            break;
        case 'torrent':
            $Title = Lang::get('reportsv2', 'all_reports_for_the_torrent_before') . "$ID" . Lang::get('reportsv2', 'all_reports_for_the_torrent_after');
            $Where = "WHERE r.TorrentID = $ID";
            break;
        case 'report':
            $Title = Lang::get('reportsv2', 'viewing_resolution_of_report_before') . "$ID" . Lang::get('reportsv2', 'viewing_resolution_of_report_after');
            $Where = "WHERE r.ID = $ID";
            break;
        case 'reporter':
            $DB->query("
				SELECT Username
				FROM users_main
				WHERE ID = $ID");
            list($Username) = $DB->next_record();
            if ($Username) {
                $Title = Lang::get('reportsv2', 'all_torrents_reported_by_before') . "$Username" . Lang::get('reportsv2', 'all_torrents_reported_by_after');
            } else {
                $Title = Lang::get('reportsv2', 'all_torrents_reported_by_user_before') . "$ID" . Lang::get('reportsv2', 'all_torrents_reported_by_user_after');
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
                $Title = Lang::get('reportsv2', 'all_reports_for_torrents_uploaded_by_before') . "$Username" . Lang::get('reportsv2', 'all_reports_for_torrents_uploaded_by_after');
            } else {
                $Title = Lang::get('reportsv2', 'all_reports_for_torrents_uploaded_by_user_before') . "$ID" . Lang::get('reportsv2', 'all_reports_for_torrents_uploaded_by_user_after');
            }
            $Where = "
				WHERE r.Status != 'Resolved'
					AND t.UserID = $ID";
            break;
        case 'type':
            $Title = Lang::get('reportsv2', 'all_new_reports_for_the_chosen_type');
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
		LEFT JOIN artists_alias AS aa ON aa.AliasID = ta.AliasID
		LEFT JOIN users_main AS resolver ON resolver.ID = r.ResolverID
		LEFT JOIN users_main AS reporter ON reporter.ID = r.ReporterID
		LEFT JOIN users_main AS uploader ON uploader.ID = t.UserID
	$Where
	GROUP BY r.ID
	$Order
	LIMIT $Limit");

$Reports = $DB->to_array();

$DB->query('SELECT FOUND_ROWS()');
list($Results) = $DB->next_record();
$PageLinks = Format::get_pages($Page, $Results, REPORTS_PER_PAGE, 11);
View::show_header(Lang::get('reportsv2', 'reports_v2'), 'reportsv2,bbcode,browse', 'PageReportV2Static');
?>
<div class="LayoutBody">
    <div class="BodyHeader">
        <h2 class="BodyHeader-nav"><?= $Title ?></h2>
        <? include('header.php'); ?>
    </div>
    <div class="buttonBoxBody center">
        <? if ($View !== 'resolved') { ?>
            <span data-tooltip="<?= Lang::get('reportsv2', 'multi_resolve_btn_title') ?>"><input class="Button" type="button" onclick="MultiResolve();" value="<?= Lang::get('reportsv2', 'multi_resolve') ?>" /></span>
            <span data-tooltip="<?= Lang::get('reportsv2', 'claim_all_btn_title') ?>"><input class="Button" type="button" onclick="Grab();" value="<?= Lang::get("reportsv2", 'claim_all') ?>" /></span>
        <?  }
        if ($View === 'staff' && $LoggedUser['ID'] == $ID) { ?>
            | <span data-tooltip="<?= Lang::get('reportsv2', 'unclaim_all_btn_title') ?>"><input class="Button" type="button" onclick="GiveBack();" value="<?= Lang::get("reportsv2", 'unclaim_all') ?>" /></span>
        <?  } ?>
    </div>
    <? if ($PageLinks) { ?>
        <div class="BodyNavLinks">
            <?= $PageLinks ?>
        </div>
    <?  } ?>
    <div id="all_reports" style="margin-left: auto; margin-right: auto;">
        <?
        if (count($Reports) === 0) {
        ?>
            <div class="BoxBody center">
                <strong><?= Lang::get('reportsv2', 'no_new_reports') ?></strong>
            </div>
            <?
        } else {
            foreach ($Reports as $Idx => $Report) {

                list(
                    $ReportID, $ReporterID, $ReporterName, $TorrentID, $Type, $UserComment, $UploaderReply, $ResolverID, $ResolverName, $Status, $ReportedTime, $LastChangeTime,
                    $ModComment, $Tracks, $Images, $ExtraIDs, $Links, $LogMessage, $GroupName, $GroupID, $ArtistID, $ArtistName, $Year, $CategoryID, $Time, $RemasterTitle,
                    $RemasterYear, $Codec, $Source, $Resolution, $Container, $Size, $UploaderID, $UploaderName
                ) = Misc::display_array($Report, array('ModComment'));

                if (!$GroupID && $Status != 'Resolved') {
                    //Torrent already deleted
                    $DB->query("
				UPDATE reportsv2
				SET
					Status = 'Resolved',
					LastChangeTime = '" . sqltime() . "',
					ModComment = 'Report already dealt with (torrent deleted)'
				WHERE ID = $ReportID");
                    $Cache->decrement('num_torrent_reportsv2');
            ?>
                    <div id="report<?= $ReportID ?>" class="report BoxBody center">
                        <a href="reportsv2.php?view=report&amp;id=<?= $ReportID ?>"><?= Lang::get('reportsv2', 'report') ?> <?= $ReportID ?></a> <?= Lang::get('reportsv2', 'report_for_torrent') ?> <?= $TorrentID ?> <?= Lang::get('reportsv2', 'deleted_has_been_automatically_resolved') ?><input class="Button" type="button" value="Hide" onclick="ClearReport(<?= $ReportID ?>);" />
                    </div>
                <?
                } else {
                    if (!$CategoryID) {
                        //Torrent was deleted
                    } else {
                        if (array_key_exists($Type, $Types[$CategoryID])) {
                            $ReportType = $Types[$CategoryID][$Type];
                        } elseif (array_key_exists($Type, $Types['master'])) {
                            $ReportType = $Types['master'][$Type];
                        } else {
                            //There was a type but it wasn't an option!
                            $Type = 'other';
                            $ReportType = $Types['master']['other'];
                        }
                    }
                    if ($GroupID) {
                        $Torrent = Torrents::get_torrent($TorrentID, true);
                        $Group = $Torrent['Group'];
                        $RawName = Torrents::torrent_name($Torrent, false);
                        $LinkName = "<a href='torrents.php?torrentid=$TorrentID#$TorrentID'>$RawName</a>";
                        $BBName = "[url=torrents.php?torrentid=$TorrentID#TorrentID] $RawName [/url]";
                    }

                ?>
                    <div id="report<?= $ReportID ?>">
                        <h3><strong>#<?= $Idx + 1 ?></strong>
                            <span class="floatright" data-tooltip="<?= Lang::get('reportsv2', 'multi_resolve_title') ?>">
                                <input type="checkbox" name="multi" id="multi<?= $ReportID ?>" />&nbsp;<label for="multi"><?= Lang::get('reportsv2', 'multi_resolve') ?></label>
                            </span>
                            <?
                            if (!$GroupID) {
                            ?>
                                <a href="log.php?search=Torrent+<?= $TorrentID ?>"><?= $TorrentID ?></a>(<?= Lang::get('torrents', 'deleted') ?>)
                            <? } ?>
                        </h3>

                        <?
                        if ($GroupID) {
                        ?>
                            <div class="TableContainer">
                                <?
                                $DetailOption = new DetailOption;
                                $DetailOption->WithReport = false;
                                $DetailOption->ReadOnly = true;
                                $tableRender = new UngroupTorrentSimpleListView([$Torrent]);
                                $tableRender->with_self(false)->with_detail('report', $DetailOption)->render();
                                ?>
                            </div>
                        <?
                        }
                        ?>
                        <form class="manage_form" name="report" id="reportform_<?= $ReportID ?>" action="reports.php" method="post">
                            <div>
                                <input type="hidden" name="auth" value="<?= $LoggedUser['AuthKey'] ?>" />
                                <input type="hidden" id="reportid<?= $ReportID ?>" name="reportid" value="<?= $ReportID ?>" />
                                <input type="hidden" id="torrentid<?= $ReportID ?>" name="torrentid" value="<?= $TorrentID ?>" />
                                <input type="hidden" id="uploader<?= $ReportID ?>" name="uploader" value="<?= $UploaderName ?>" />
                                <input type="hidden" id="uploaderid<?= $ReportID ?>" name="uploaderid" value="<?= $UploaderID ?>" />
                                <input type="hidden" id="reporterid<?= $ReportID ?>" name="reporterid" value="<?= $ReporterID ?>" />
                                <input type="hidden" id="report_reason<?= $ReportID ?>" name="report_reason" value="<?= $UserComment ?>" />
                                <input type="hidden" id="raw_name<?= $ReportID ?>" name="raw_name" value="<?= $RawName ?>" />
                                <input type="hidden" id="type<?= $ReportID ?>" name="type" value="<?= $Type ?>" />
                                <input type="hidden" id="categoryid<?= $ReportID ?>" name="categoryid" value="<?= $CategoryID ?>" />
                            </div>
                            <div class="TableContainer">
                                <table class="Form-rowList" variant="header" cellpadding="5">
                                    <tr class="Form-row">
                                        <td class="Tabel-cell" colspan="4">
                                            <?
                                            if ($Status != 'Resolved') {


                                                $DB->query("
						SELECT r.ID
						FROM reportsv2 AS r
							LEFT JOIN torrents AS t ON t.ID = r.TorrentID
						WHERE r.Status != 'Resolved'
							AND t.GroupID = $GroupID");
                                                $GroupOthers = ($DB->record_count() - 1);

                                                if ($GroupOthers > 0) { ?>
                                                    <div style="font-style: italic;">
                                                        <a href="reportsv2.php?view=group&amp;id=<?= $GroupID ?>"><?= Lang::get('reportsv2', 'there_are_n_other_reports_for_torrents_in_this_group_1') ?><?= (($GroupOthers > 1) ? Lang::get('reportsv2', 'there_are_n_other_reports_for_torrents_in_this_group_2') . " $GroupOthers " . Lang::get('reportsv2', 'there_are_n_other_reports_for_torrents_in_this_group_3') : Lang::get('reportsv2', 'there_are_n_other_reports_for_torrents_in_this_group_4')) ?><?= Lang::get('reportsv2', 'there_are_n_other_reports_for_torrents_in_this_group_5') ?></a>
                                                    </div>
                                                <?                  }

                                                $DB->query("
						SELECT t.UserID
						FROM reportsv2 AS r
							JOIN torrents AS t ON t.ID = r.TorrentID
						WHERE r.Status != 'Resolved'
							AND t.UserID = $UploaderID");
                                                $UploaderOthers = ($DB->record_count() - 1);

                                                if ($UploaderOthers > 0) { ?>
                                                    <div style="font-style: italic;">
                                                        <a href="reportsv2.php?view=uploader&amp;id=<?= $UploaderID ?>"><?= Lang::get('reportsv2', 'there_are_n_other_reports_for_torrents_uploaded_by_this_user_1') ?><?= (($UploaderOthers > 1) ? Lang::get('reportsv2', 'there_are_n_other_reports_for_torrents_uploaded_by_this_user_2') . " $UploaderOthers " . Lang::get('reportsv2', 'there_are_n_other_reports_for_torrents_uploaded_by_this_user_3') : Lang::get('reportsv2', 'there_are_n_other_reports_for_torrents_uploaded_by_this_user_4')) ?><?= Lang::get('reportsv2', 'there_are_n_other_reports_for_torrents_uploaded_by_this_user_5') ?></a>
                                                    </div>
                                                    <?                  }

                                                $DB->query("
						SELECT DISTINCT req.ID,
							req.FillerID,
							um.Username,
							req.TimeFilled
						FROM requests AS req
							LEFT JOIN torrents AS t ON t.ID = req.TorrentID
							LEFT JOIN reportsv2 AS rep ON rep.TorrentID = t.ID
							JOIN users_main AS um ON um.ID = req.FillerID
						WHERE rep.Status != 'Resolved'
							AND req.TimeFilled > '2010-03-04 02:31:49'
							AND req.TorrentID = $TorrentID");
                                                $Requests = ($DB->has_results());
                                                if ($Requests > 0) {
                                                    while (list($RequestID, $FillerID, $FillerName, $FilledTime) = $DB->next_record()) {
                                                    ?>
                                                        <div style="font-style: italic;">
                                                            <strong class="u-colorWarning"><a href="user.php?id=<?= $FillerID ?>"><?= $FillerName ?></a> <?= Lang::get('reportsv2', 'used_this_torrent_to_fill') ?> <a href="requests.php?action=view&amp;id=<?= $RequestID ?>"><?= Lang::get('reportsv2', 'this_request') ?></a> <?= time_diff($FilledTime) ?></strong>
                                                        </div>
                                            <?                      }
                                                }
                                            }
                                            ?>
                                        </td>

                                    </tr>

                            </div>
                            </td>
                            </tr>
                            <?
                            if ($Tracks) { ?>
                                <tr class="Form-row">
                                    <td class="Form-label Table-cellRight"><?= Lang::get('reportsv2', 'relevant_tracks') ?>:</td>
                                    <td class="Form-items" colspan="3">
                                        <?= str_replace(' ', ', ', $Tracks) ?>
                                    </td>
                                </tr>
                            <?
                            }

                            if ($Links) { ?>
                                <tr class="Form-row">
                                    <td class="Form-label Table-cellRight"><?= Lang::get('reportsv2', 'relevant_links') ?>:</td>
                                    <td class="Form-items" colspan="3">
                                        <?
                                        $Links = explode(' ', $Links);
                                        foreach ($Links as $Link) {

                                            if ($local_url = Text::local_url($Link)) {
                                                $Link = $local_url;
                                            }
                                        ?>
                                            <a href="<?= $Link ?>"><?= $Link ?></a>
                                        <?              } ?>
                                    </td>
                                </tr>
                            <?
                            }
                            if ($Images) {
                            ?>
                                <tr class="Form-row">
                                    <td class="Form-label Table-cellRight"><?= Lang::get('reportsv2', 'relevant_images') ?>:</td>
                                    <td class="Form-items" colspan="3">
                                        <?
                                        $Images = explode(' ', $Images);
                                        foreach ($Images as $Image) {
                                        ?>
                                            <img style="max-width: 200px;" onclick="lightbox.init(this, 200);" src="<?= ImageTools::process($Image) ?>" alt="Relevant image" />
                                        <?              } ?>
                                    </td>
                                </tr>
                                <?
                            }
                            if ($GroupID) {
                                $DB->query(
                                    "select ft.id, body
				from forums_posts fp 
				left join forums_topics ft on fp.topicid=ft.id 
				where (body like '%torrents.php?id=$GroupID%' or body like '%artist.php?id=$ArtistID%')
				and fp.authorid = 9 
				and LastPostID = fp.id"
                                );
                                //[quote=Comments]两位艺人应该分开，已经单独添加，申请删除合并[/quote]
                                if ($DB->has_results()) {
                                    $EditTopics = $DB->to_array();
                                ?>
                                    <tr class="Form-row">
                                        <td class="Form-label Table-cellRight"><?= Lang::get('reportsv2', 'edit_request_about_this_torrent') ?>:</td>
                                        <td class="Form-items" colspan="3" class="wrap_overflow">
                                            <table>
                                                <?
                                                foreach ($EditTopics as $EditTopic) {
                                                    if (strpos($EditTopic['body'], "artist.php") !== false) {
                                                        $EditText = "<td>[Artist";
                                                    } else {
                                                        $EditText = "<td>[Album";
                                                    }
                                                    preg_match('/user\.php\?id=(\d+)\](.+?)\[/', $EditTopic['body'], $match);
                                                    $EditText .= " Request]</td><td>[<a href=\"user.php?id=$match[1]\">$match[2]</a>]</td><td><a href=\"forums.php?action=viewthread&threadid=" . $EditTopic['id'] . "\">";
                                                    preg_match('/\[quote=Comments\](.+)\[\/quote\]/s', $EditTopic['body'], $match);
                                                    $EditText .= $match[1] . "</a></td>";
                                                ?>
                                                    <tr><?= $EditText ?></tr>
                                                <?
                                                }
                                                ?>
                                            </table>
                                        </td>
                                    </tr>
                                <?
                                }
                            }
                            if ($GroupID) {
                                ?>

                                <tr class="Form-row">
                                    <td class="Form-label Table-cellRight Table-cellTop"><?= Lang::get('reportsv2', 'uploaded_by') ?>:</td>
                                    <td class="Form-inputs" colspan="1" class="wrap_overflow"><a href="user.php?id=<?= $UploaderID ?>"><?= $UploaderName ?></a>
                                        <div class="Table-cellRight Table-cellTop"><?= Lang::get('reportsv2', 'upload_time') ?>:</div>
                                        <div colspan="1" class="wrap_overflow"><?= $Time ?></div>
                                    </td>
                                </tr>
                            <?
                            }
                            ?>
                            <tr class="Form-row">
                                <td class="Form-label Table-cellRight Table-cellTop"><?= Lang::get('reportsv2', 'reporter') ?>:</td>
                                <td class="Form-inputs" colspan="1" class="wrap_overflow">
                                    <a href="user.php?id=<?= $ReporterID ?>"><?= $ReporterName ?></a>
                                    <div class="Table-cellRight Table-cellTop"><?= Lang::get('reportsv2', 'date_reported') ?>:</div>
                                    <div colspan="1" class="wrap_overflow"><?= $ReportedTime ?></div>
                                </td>
                            </tr>

                            <tr class="Form-row">
                                <td class="Form-label Table-cellRight Table-cellTop"><?= Lang::get('reportsv2', 'user_comment') ?>:</td>
                                <td class="Form-items" colspan="3" class="wrap_overflow"><?= Text::full_format($UserComment) ?></td>
                            </tr>
                            <?
                            if ($UploaderReply) {
                            ?>
                                <tr class="Form-row">
                                    <td class="Form-label Table-cellRight"><?= Lang::get('reportsv2', 'uploader_s_reply') ?>:</td>
                                    <td class="Form-items" colspan="3" class="wrap_overflow"><?= Text::full_format($UploaderReply) ?></td>
                                </tr>
                            <?
                            }
                            ?>
                            <?          // END REPORTED STUFF :|: BEGIN MOD STUFF
                            if ($Status == 'InProgress') { ?>
                                <tr class="Form-row">
                                    <td class="Form-label Table-cellRight"><?= Lang::get('reportsv2', 'in_progress_by') ?>:</td>
                                    <td class="Form-items" colspan="3">
                                        <a href="user.php?id=<?= $ResolverID ?>"><?= $ResolverName ?></a>
                                    </td>
                                </tr>
                            <?          }
                            if ($Status != 'Resolved') { ?>
                                <tr class="Form-row">
                                    <td class="Form-label Table-cellRight Table-cellTop"><?= Lang::get('reportsv2', 'report_comment') ?>:</td>
                                    <td class="Form-inputs" colspan="3">
                                        <textarea style="min-height: auto;" class="Input" type="text" name="comment" id="comment<?= $ReportID ?>" size="70"><?= $ModComment ?></textarea>
                                        <input class="Button" type="button" value="Update now" onclick="UpdateComment(<?= $ReportID ?>);" />
                                    </td>
                                </tr>
                                <tr class="Form-row">
                                    <td class="Form-label Table-cellRight Table-cellTop">
                                        <a href="javascript:Load('<?= $ReportID ?>')" data-tooltip="<?= Lang::get('reportsv2', 'resolve_title') ?>"><?= Lang::get('reportsv2', 'resolve') ?></a>:
                                    </td>
                                    <td class="Form-items" colspan="3">
                                        <div id="options<?= $ReportID ?>" class="Table-item Form-inputs">
                                            <select class="Input" name="resolve_type" id="resolve_type<?= $ReportID ?>" onchange="ChangeResolve(<?= $ReportID ?>);">
                                                <?
                                                $TypeList = $Types['master'] + $Types[$CategoryID];
                                                $Priorities = array();
                                                foreach ($TypeList as $Key => $Value) {
                                                    $Priorities[$Key] = $Value['priority'];
                                                }
                                                array_multisort($Priorities, SORT_ASC, $TypeList);

                                                foreach ($TypeList as $Type => $Data) { ?>
                                                    <option class="Select-option" value="<?= $Type ?>"><?= $Data['title'] ?></option>
                                                <?              } ?>
                                            </select>
                                            |
                                            <span data-tooltip="<?= Lang::get('reportsv2', 'warning_title') ?>">
                                                <label for="warning<?= $ReportID ?>"><strong><?= Lang::get('reportsv2', 'warning') ?></strong></label>
                                                <select class="Input" name="warning" id="warning<?= $ReportID ?>">
                                                    <? for ($i = 0; $i < 9; $i++) { ?>
                                                        <option class="Select-option" value="<?= $i ?>"><?= $i ?></option>
                                                    <?              } ?>
                                                </select>
                                            </span> |
                                            <?
                                            $DB->query("select firsttorrent from users_main where id=$UploaderID and firsttorrent=$TorrentID");
                                            $FirstTorrent = $DB->has_results();
                                            if ($FirstTorrent) {
                                            ?>
                                                <span data-tooltip="<?= Lang::get('reportsv2', 'first_torrent_title') ?>"><strong class="u-colorWarning"><?= Lang::get('reportsv2', 'first_torrent') ?></strong></span> |
                                            <?
                                            }
                                            ?>
                                            <? if (check_perms('users_mod')) { ?>
                                                <span data-tooltip="<?= Lang::get('reportsv2', 'delete_title') ?>">
                                                    <input type="checkbox" name="delete" id="delete<?= $ReportID ?>" />&nbsp;<label for="delete<?= $ReportID ?>"><strong><?= Lang::get('global', 'delete') ?></strong></label>
                                                </span> |
                                            <?              } ?>
                                            <span data-tooltip="<?= Lang::get('reportsv2', 'remove_upload_privileges_title') ?>">
                                                <input type="checkbox" name="upload" id="upload<?= $ReportID ?>" />&nbsp;<label for="upload<?= $ReportID ?>"><strong><?= Lang::get('reportsv2', 'remove_upload_privileges') ?></strong></label>
                                            </span>
                                            <div>
                                                <input class="Button" type="button" name="update_resolve" id="update_resolve<?= $ReportID ?>" value="Update now" onclick="UpdateResolve(<?= $ReportID ?>);" />
                                            </div>
                                        </div>

                                    </td>
                                </tr>
                                <tr class="Form-row">
                                    <td class="Form-label Table-cellTop Table-cellRight"><?= Lang::get('reportsv2', 'custom_trumpable') ?>:</td>
                                    <td class="Form-inputs" colspan="3">
                                        <textarea class="Input" name="custom_trumpable" id="custom_trumpable<?= $ReportID ?>" cols="50" rows="2" style="min-height: auto;"></textarea>
                                    </td>
                                </tr>
                                <tr class="Form-row">
                                    <td class="Form-label Table-cellRight Table-cellTop" data-tooltip="<?= Lang::get('reportsv2', 'pm_uploader_reporter_title') ?>">
                                        <?= Lang::get('reportsv2', 'pm_uploader_reporter') ?>
                                        <select class="Input" name="pm_type" id="pm_type<?= $ReportID ?>">
                                            <option class="Select-option" value="Uploader" selected="selected"><?= Lang::get('reportsv2', 'uploader') ?></option>
                                            <option class="Select-option" value="Reporter"><?= Lang::get('reportsv2', 'reporter') ?></option>
                                        </select>:
                                    </td>
                                    <td class="Form-items Table-cellTop" colspan="3">
                                        <?php new TEXTAREA_PREVIEW('uploader_pm', 'uploader_pm_' . $ReportID, '', 50, 2, true, true, false); ?>
                                        <input class="Button" style="width: 100px" type="button" value="Send now" onclick="SendPM(<?= $ReportID ?>);" />
                                    </td>
                                </tr>
                                <tr class="Form-row">
                                    <td class="Form-label Table-cellRight"><strong><?= Lang::get('reportsv2', 'extra') ?></strong><?= Lang::get('reportsv2', 'space_log_message') ?>:</td>
                                    <td class="Form-inputs">
                                        <input style="width:unset" class="Input" type="text" name="log_message" id="log_message<?= $ReportID ?>" size="40" <?
                                                                                                                                                            if ($ExtraIDs) {
                                                                                                                                                                $Extras = explode(' ', $ExtraIDs);
                                                                                                                                                                $Value = '';
                                                                                                                                                                foreach ($Extras as $ExtraID) {
                                                                                                                                                                    $Value .= site_url() . "torrents.php?torrentid=$ExtraID ";
                                                                                                                                                                }
                                                                                                                                                                echo ' value="' . trim($Value) . '"';
                                                                                                                                                            } elseif (isset($ReportType['extra_log'])) {
                                                                                                                                                                printf(' value="%s"', $ReportType['extra_log']);
                                                                                                                                                            } ?> />
                                        <div><strong><?= Lang::get('reportsv2', 'extra') ?></strong><?= Lang::get('reportsv2', 'space_staff_notes') ?>:</div>
                                        <div>
                                            <input class=" Input" type="text" name="admin_message" id="admin_message<?= $ReportID ?>" size="40" />
                                        </div>
                                    </td>
                                </tr>
                                <tr class="Form-row">
                                    <td colspan="4" style="text-align: center;">
                                        <input class="Button" type="button" value="<?= Lang::get('reportsv2', 'invalidate_report') ?>" onclick="Dismiss(<?= $ReportID ?>);" />
                                        <input class="Button" type="button" value="<?= Lang::get('reportsv2', 'resolve_report') ?>" onclick="ManualResolve(<?= $ReportID ?>);" />
                                        <? if ($Status == 'InProgress' && $LoggedUser['ID'] == $ResolverID) { ?>
                                            <input class="Button" type="button" value="<?= Lang::get('reportsv2', 'unclaim') ?>" onclick="GiveBack(<?= $ReportID ?>);" />
                                        <?                  } else { ?>
                                            <input class="Button" id="grab<?= $ReportID ?>" type="button" value="<?= Lang::get('reportsv2', 'claim') ?>" onclick="Grab(<?= $ReportID ?>);" />
                                        <?                  }   ?>
                                        <input class="Button" type="button" id="submit_<?= $ReportID ?>" value="<?= Lang::get('global', 'submit') ?>" onclick="TakeResolve(<?= $ReportID ?>);" />
                                    </td>
                                </tr>
                            <?              } else { ?>
                                <tr class="Form-row">
                                    <td class="Form-label Table-cellRight"><?= Lang::get('reportsv2', 'resolver') ?>:</td>
                                    <td class="Form-items" colspan="3">
                                        <a href="user.php?id=<?= $ResolverID ?>"><?= $ResolverName ?></a>
                                    </td>
                                </tr>
                                <tr class="Form-row">
                                    <td class="Form-label Table-cellRight"><?= Lang::get('reportsv2', 'resolve_time') ?>:</td>
                                    <td class="Form-items" colspan="3">
                                        <?= time_diff($LastChangeTime);
                                        echo "\n"; ?>
                                    </td>
                                </tr>
                                <tr class="Form-row">
                                    <td class="Form-label Table-cellRight Table-cellTop"><?= Lang::get('reportsv2', 'report_comment') ?>:</td>
                                    <td class="Form-items" colspan="3">
                                        <?= $ModComment;
                                        echo "\n"; ?>
                                    </td>
                                </tr>
                                <tr class="Form-row">
                                    <td class="Form-label Table-cellRight"><?= Lang::get('reportsv2', 'log_message') ?>:</td>
                                    <td class="Form-items" colspan="3">
                                        <?= $LogMessage;
                                        echo "\n"; ?>
                                    </td>
                                </tr>
                                <? if ($GroupID) { ?>
                                    <tr class="Form-row">
                                        <td colspan="4" style="text-align: center;">
                                            <input class="Input" id="grab<?= $ReportID ?>" type="button" value="<?= Lang::get('reportsv2', 'claim') ?>" onclick="Grab(<?= $ReportID ?>);" />
                                        </td>
                                    </tr>
                            <?                  }
                            } ?>
                            </table>
                    </div>
                    </form>
    </div>
    <script type="text/javascript">
        //<![CDATA[
        Load(<?= $ReportID ?>);
        //]]>
    </script>
<?
                }
            }
        }
?>
</div>
<? if ($PageLinks) { ?>
    <div class="BodyNavLinks pager"><?= $PageLinks ?></div>
<? } ?>
</div>
<? View::show_footer(); ?>