<?
include(CONFIG['SERVER_ROOT'] . '/classes/torrenttable.class.php');

$TorrentID = $_GET['torrentid'];
if (!$TorrentID || !is_number($TorrentID)) {
    error(404);
}


$DB->query("
	SELECT
		t.UserID,
		t.Time,
		COUNT(x.uid)
	FROM torrents AS t
		LEFT JOIN xbt_snatched AS x ON x.fid = t.ID
	WHERE t.ID = $TorrentID
	GROUP BY t.UserID");

if (!$DB->has_results()) {
    error(t('server.torrents.torrent_already_deleted'));
}

if ($Cache->get_value('torrent_' . $TorrentID . '_lock')) {
    error(t('server.torrents.torrent_cannot_be_deleted_because_the_upload_process_is_not_completed_yet'));
}


list($UserID, $Time, $Snatches) = $DB->next_record();


if ($LoggedUser['ID'] != $UserID && !check_perms('torrents_delete')) {
    error(403);
}

if (isset($_SESSION['logged_user']['multi_delete']) && $_SESSION['logged_user']['multi_delete'] >= 3 && !check_perms('torrents_delete_fast')) {
    error(t('server.torrents.you_have_recently_deleted_3_torrents'));
}

if (time_ago($Time) > 3600 * 24 * 7 && !check_perms('torrents_delete')) { // Should this be torrents_delete or torrents_delete_fast?
    error(t('server.torrents.you_can_no_longer_delete_this_torrent_as_it_has_been_uploaded_for_over_a_week'));
}

if ($Snatches > 4 && !check_perms('torrents_delete')) { // Should this be torrents_delete or torrents_delete_fast?
    error(t('server.torrents.you_can_no_longer_delete_this_torrent_as_it_has_been_snatched_by_5_or_more_users'));
}


View::show_header(t('server.torrents.delete_torrent'), 'reportsv2', 'PageTorrentDelete');

?>
<div class="LayoutBody">
    <div class="Form-rowList" id="torrent_delete_reason" variant="header">
        <div class="Form-rowHeader"><?= t('server.torrents.delete_torrent') ?></div>
        <form class="delete_form" name="torrent" action="torrents.php" method="post">
            <input type="hidden" name="action" value="takedelete" />
            <input type="hidden" name="auth" value="<?= $LoggedUser['AuthKey'] ?>" />
            <input type="hidden" name="torrentid" value="<?= $TorrentID ?>" />
            <div class="Form-row">
                <p><strong class="u-colorWarning"><?= t('server.torrents.delete_torrent_note') ?></strong></p>
            </div>
            <div class="Form-row">
                <div class="Form-label"><?= t('server.torrents.reason') ?>: </div>
                <div class="Form-inputs">
                    <select class="Input" name="reason">
                        <option class="Select-option" value="Dead"><?= t('server.torrents.dead') ?></option>
                        <option class="Select-option" value="Dupe"><?= t('server.torrents.dupe') ?></option>
                        <option class="Select-option" value="Trumped"><?= t('server.torrents.trumped') ?></option>
                        <option class="Select-option" value="Rules Broken"><?= t('server.torrents.rules_broken') ?></option>
                        <option class="Select-option" value="" selected="selected"><?= t('server.torrents.other') ?></option>
                    </select>
                </div>
            </div>
            <div class="Form-row">
                <div class="Form-label"><?= t('server.torrents.extra_info') ?>: </div>
                <div class="Form-inputs">
                    <input class="Input" type="text" name="extra" size="30" placeholder="<?= t('server.torrents.extra_info_placeholder') ?>" />
                </div>
            </div>
            <div class="Form-row">
                <input class="Button" value="<?= t('server.common.delete') ?>" type="submit" />
            </div>
        </form>
    </div>
</div>
<?
if (check_perms('admin_reports')) {
?>
    <div id="all_reports" style="margin-left: auto; margin-right: auto;">
        <?
        include(CONFIG['SERVER_ROOT'] . '/classes/reports.class.php');

        //require(CONFIG['SERVER_ROOT'].'/sections/reportsv2/array.php');
        include(Lang::getLangfilePath("report_types"));
        // TODO fix data
        $ReportID = 0;
        $ReporterID = 0;
        $DB->query("
			SELECT
				tg.Name,
                tg.SubName,
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
				t.Source,
				t.Codec,
				t.Container,
				t.Resolution,
                t.Processing,
				t.Size,
				t.UserID AS UploaderID,
				uploader.Username
			FROM torrents AS t
				LEFT JOIN torrents_group AS tg ON tg.ID = t.GroupID
				LEFT JOIN torrents_artists AS ta ON ta.GroupID = tg.ID AND ta.Importance = '1'
				LEFT JOIN artists_alias AS aa ON aa.AliasID = ta.AliasID
				LEFT JOIN users_main AS uploader ON uploader.ID = t.UserID
			WHERE t.ID = $TorrentID");

        if (!$DB->has_results()) {
            die();
        }
        $Data = $DB->next_record(MYSQLI_ASSOC, false);
        list(
            $GroupName, $SubName, $GroupID, $ArtistID, $ArtistName, $Year, $CategoryID, $Time, $RemasterTitle,
            $RemasterYear, $Source, $Codec, $Container, $Resolution, $Processing, $Size, $UploaderID, $UploaderName
        ) = array_values($Data);

        $Type = 'dupe'; //hardcoded default

        if (array_key_exists($Type, $Types[$CategoryID])) {
            $ReportType = $Types[$CategoryID][$Type];
        } elseif (array_key_exists($Type, $Types['master'])) {
            $ReportType = $Types['master'][$Type];
        } else {
            //There was a type but it wasn't an option!
            $Type = 'other';
            $ReportType = $Types['master']['other'];
        }
        $TorrentDetail = Torrents::get_torrent($TorrentID);
        $RawName = Torrents::torrent_name($TorrentDetail, false);
        $LinkName = "<a href=\"torrents.php?torrentid=$TorrentID\">$RawName</a>";
        $BBName = "[url=torrents.php?torrentid=$TorrentID] $RawName [/url]";

        $DetailOption = new DetailOption;
        $DetailOption->WithReport = false;
        $DetailOption->ReadOnly = true;
        $tableRender = new UngroupTorrentSimpleListView([$TorrentDetail]);
        $tableRender->with_self(false)->with_detail('report', $DetailOption)->render();
        ?>
        <div id="report<?= $ReportID ?>" class="report">
            <form class="create_form" name="report" id="reportform_<?= $ReportID ?>" action="reports.php" method="post">
                <?
                /*
                * Some of these are for takeresolve, some for the JavaScript.
                */
                ?>
                <div>
                    <input type="hidden" name="auth" value="<?= $LoggedUser['AuthKey'] ?>" />
                    <input type="hidden" id="reportid<?= $ReportID ?>" name="reportid" value="<?= $ReportID ?>" />
                    <input type="hidden" id="torrentid<?= $ReportID ?>" name="torrentid" value="<?= $TorrentID ?>" />
                    <input type="hidden" id="uploader<?= $ReportID ?>" name="uploader" value="<?= $UploaderName ?>" />
                    <input type="hidden" id="uploaderid<?= $ReportID ?>" name="uploaderid" value="<?= $UploaderID ?>" />
                    <input type="hidden" id="reporterid<?= $ReportID ?>" name="reporterid" value="<?= $ReporterID ?>" />
                    <input type="hidden" id="raw_name<?= $ReportID ?>" name="raw_name" value="<?= $RawName ?>" />
                    <input type="hidden" id="type<?= $ReportID ?>" name="type" value="<?= $Type ?>" />
                    <input type="hidden" id="categoryid<?= $ReportID ?>" name="categoryid" value="<?= $CategoryID ?>" />
                    <input type="hidden" id="pm_type<?= $ReportID ?>" name="pm_type" value="Uploader" />
                    <input type="hidden" id="from_delete<?= $ReportID ?>" name="from_delete" value="<?= $GroupID ?>" />
                </div>
                <div class="TableContainer">
                    <table cellpadding="5" class="Form-rowList" variant="header">
                        <tr class="Form-row">
                            <td colspan="4">
                                <? if ($GroupID) { ?>
                                    <? $DB->query("
				SELECT r.ID
				FROM reportsv2 AS r
					LEFT JOIN torrents AS t ON t.ID = r.TorrentID
				WHERE r.Status != 'Resolved'
					AND t.GroupID = $GroupID");
                                    $GroupOthers = ($DB->has_results());

                                    if ($GroupOthers > 0) { ?>
                                        <div style="text-align: right;">
                                            <a href="reportsv2.php?view=group&amp;id=<?= $GroupID ?>">
                                                <?= t('server.reportsv2.there_are_n_other_reports_for_torrents_in_this_group', [
                                                    'Values' => [
                                                        t('server.reportsv2.there_are_n_other_reports_for_torrents_in_this_group_report', ['Count' => $GroupOthers, 'Values' => [$GroupOthers]]),
                                                    ]
                                                ]) ?>
                                            </a>
                                        </div>
                                    <?          }

                                    $DB->query("
				SELECT t.UserID
				FROM reportsv2 AS r
					JOIN torrents AS t ON t.ID = r.TorrentID
				WHERE r.Status != 'Resolved'
					AND t.UserID = $UploaderID");
                                    $UploaderOthers = ($DB->has_results());

                                    if ($UploaderOthers > 0) { ?>
                                        <div style="text-align: right;">
                                            <a href="reportsv2.php?view=uploader&amp;id=<?= $UploaderID ?>">
                                                <?= t('server.reportsv2.there_are_n_other_reports_for_torrents_uploaded_by_this_user', ['Values' => [
                                                    t('server.reportsv2.there_are_n_other_reports_for_torrents_uploaded_by_this_user_count', ['Count' => $UploaderOthers, 'Values' => [$UploaderOthers]])
                                                ]]) ?>
                                            </a>
                                        </div>
                                        <?          }

                                    $DB->query("
				SELECT DISTINCT req.ID,
					req.FillerID,
					um.Username,
					req.TimeFilled
				FROM requests AS req
					JOIN users_main AS um ON um.ID = req.FillerID
				AND req.TorrentID = $TorrentID");
                                    $Requests = ($DB->has_results());
                                    if ($Requests > 0) {
                                        while (list($RequestID, $FillerID, $FillerName, $FilledTime) = $DB->next_record()) {
                                        ?>
                                            <div style="text-align: right;">
                                                <strong class="u-colorWarning"><a href="user.php?id=<?= $FillerID ?>"><?= $FillerName ?></a> <?= t('server.torrents.used_this_torrent_to_fill') ?> <a href="requests.php?action=viewrequest&amp;id=<?= $RequestID ?>"><?= t('server.torrents.this_request') ?></a> <?= time_diff($FilledTime) ?></strong>
                                            </div>
                                <?              }
                                    }
                                }
                                ?>
                            </td>
                        </tr>
                        <?              /* END REPORTED STUFF :|: BEGIN MOD STUFF */ ?>
                        <tr class="Form-row">
                            <td class="Form-label">
                                <a href="javascript:Load('<?= $ReportID ?>')" data-tooltip="<?= t('server.reportsv2.resolve_title') ?>"><?= t('server.torrents.resolve') ?>:</a>
                            </td>
                            <td class="Form-items" colspan="3">
                                <div class="Form-inputs">
                                    <select class="Input" name="resolve_type" id="resolve_type<?= $ReportID ?>" onchange="ChangeResolve(<?= $ReportID ?>);">
                                        <?
                                        $TypeList = $Types['master'] + $Types[$CategoryID];
                                        $Priorities = array();
                                        foreach ($TypeList as $Key => $Value) {
                                            $Priorities[$Key] = $Value['priority'];
                                        }
                                        array_multisort($Priorities, SORT_ASC, $TypeList);

                                        foreach ($TypeList as $IType => $Data) {
                                        ?>
                                            <option class="Select-option" value="<?= $IType ?>" <?= (($Type == $IType) ? ' selected="selected"' : '') ?>><?= $Data['title'] ?></option>
                                        <?
                                        }
                                        ?>
                                    </select>
                                    |
                                    <span id="options<?= $ReportID ?>">

                                        <span data-tooltip="<?= t('server.torrents.warning_title') ?>">
                                            <label for="warning<?= $ReportID ?>"><strong><?= t('server.torrents.warning') ?></strong></label>
                                            <select class="Input" name="warning" id="warning<?= $ReportID ?>">
                                                <? for ($i = 0; $i < 9; $i++) { ?>
                                                    <option class="Select-option" value="<?= $i ?>" <?= (($ReportType['resolve_options']['warn'] == $i) ? ' selected="selected"' : '') ?>><?= $i ?></option>
                                                <?  } ?>
                                            </select>
                                        </span>
                                        |
                                        <span data-tooltip="<?= t('server.torrents.delete_title') ?>">
                                            <input type="checkbox" name="delete" id="delete<?= $ReportID ?>" <?= ($ReportType['resolve_options']['delete'] ? ' checked="checked"' : '') ?> />
                                            <label for="delete<?= $ReportID ?>"><strong><?= t('server.common.delete') ?></strong></label>
                                        </span>
                                        <?
                                        $DB->query("select firsttorrent from users_main where id=$UploaderID and firsttorrent=$TorrentID");
                                        $FirstTorrent = $DB->has_results();
                                        if ($FirstTorrent) {
                                        ?>
                                            <span data-tooltip="<?= t('server.torrents.first_torrent_title') ?>"><strong class="u-colorWarning"><?= t('server.torrents.first_torrent') ?></strong></span>
                                        <?
                                        }
                                        ?>
                                        |
                                        <span data-tooltip="<?= t('server.torrents.remove_upload_privileges_title') ?>">
                                            <input type="checkbox" name="upload" id="upload<?= $ReportID ?>" <?= ($ReportType['resolve_options']['upload'] ? ' checked="checked"' : '') ?> />
                                            <label for="upload<?= $ReportID ?>"><strong><?= t('server.torrents.remove_upload_privileges') ?></strong></label>
                                        </span>
                                    </span>
                                </div>
                            </td>
                        </tr>
                        <tr class="Form-row">
                            <td class="Form-label"><?= t('server.torrents.pm_uploader') ?>:</td>
                            <td class="Form-items" colspan="3">
                                <span data-tooltip="<?= t('server.torrents.appended_to_the_regular_message_unless_using_send_now') ?>">
                                    <? new TEXTAREA_PREVIEW("uploader_pm", "uploader_pm") ?>
                                </span>
                                <input style="width:100px" class="Button" type="button" value="Send now" onclick="SendPM(<?= $ReportID ?>);" />
                            </td>
                        </tr>
                        <tr class="Form-row">
                            <td class="Form-label"><strong><?= t('server.torrents.extra') ?></strong><?= t('server.torrents.space_log_message') ?>:</td>
                            <td class="Form-inputs">
                                <input class="Input" style="width:unset" type="text" name="log_message" id="log_message<?= $ReportID ?>" size="40" />
                                <div class="label"><strong><?= t('server.torrents.extra') ?></strong><?= t('server.torrents.space_staff_notes') ?>:</div>
                                <div>
                                    <input class="Input" type="text" name="admin_message" id="admin_message<?= $ReportID ?>" size="40" />
                                </div>
                            </td>
                        </tr>
                        <tr class="Form-row">
                            <td colspan="4" style="text-align: center;">
                                <input class="Button" type="button" value="<?= t('server.common.submit') ?>" onclick="TakeResolve(<?= $ReportID ?>);" />
                            </td>
                        </tr>
                    </table>
                </div>
            </form>
            <br />
        </div>
    </div>
<?
}
View::show_footer(); ?>