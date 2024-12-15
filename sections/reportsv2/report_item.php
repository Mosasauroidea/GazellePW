<?
function render_item($Idx, $Report, $ReportMessages) {
    $DB = G::$DB;
    $Cache = G::$Cache;
    $LoggedUser = G::$LoggedUser;
    global $Types;
    list(
        $ReportID,
        $ReporterID,
        $ReporterName,
        $TorrentID,
        $Type,
        $UserComment,
        $UploaderReply,
        $ResolverID,
        $ResolverName,
        $Status,
        $ReportedTime,
        $LastChangeTime,
        $ModComment,
        $Tracks,
        $Images,
        $ExtraIDs,
        $Links,
        $LogMessage,
        $GroupName,
        $GroupID,
        $ArtistID,
        $ArtistName,
        $Year,
        $CategoryID,
        $Time,
        $RemasterTitle,
        $RemasterYear,
        $Codec,
        $Source,
        $Resolution,
        $Container,
        $Size,
        $UploaderID,
        $UploaderName
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
            <a href="reportsv2.php?view=report&amp;id=<?= $ReportID ?>"><?= t('server.reportsv2.report') ?> <?= $ReportID ?></a> <?= t('server.reportsv2.report_for_torrent') ?> <?= $TorrentID ?> <?= t('server.reportsv2.deleted_has_been_automatically_resolved') ?><input class="Button" type="button" value="Hide" onclick="ClearReport(<?= $ReportID ?>);" />
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
            $RawName = Torrents::torrent_name($Torrent, false);
        }

    ?>
        <div id="report<?= $ReportID ?>">
            <form class="Form manage_form" name="report" id="reportform_<?= $ReportID ?>" action="reports.php" method="post">
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
                <table variant="header" class="Form-rowList" cellpadding="5">
                    <tr class="Form-rowHeader">
                        <td class="Form-title">
                            <span data-tooltip="<?= t('server.reportsv2.multi_resolve_title') ?>">
                                <input type="checkbox" name="multi" id="multi<?= $ReportID ?>" />
                            </span>
                            <strong><a href='#<?= $Idx + 1 ?>' id='<?= $Idx + 1 ?>'>#<?= $Idx + 1 ?></a></strong>
                        </td>
                    </tr>
                    <tr class="Form-rowSubHeader">
                        <td colspan="2"><?= t('server.torrents.report_info') ?></td>
                    </tr>
                    <tr class="Form-row">
                        <td class="Form-label">
                            <?= t('server.common.torrents') ?>:
                        </td>
                        <td class="Form-inputs">
                            <?
                            if (!$GroupID) {
                            ?>
                                <div>
                                    <a href="log.php?search=Torrent+<?= $TorrentID ?>"><?= $TorrentID ?></a>&nbsp;(<?= t('server.torrents.deleted') ?>)
                                </div>
                            <? } else { ?>
                                <?= Torrents::torrent_simple_view($Torrent['Group'], $Torrent) ?>
                                <?

                                if ($Status != 'Resolved') {
                                    $DB->query("
						SELECT r.ID
						FROM reportsv2 AS r
							LEFT JOIN torrents AS t ON t.ID = r.TorrentID
						WHERE r.Status != 'Resolved'
							AND t.GroupID = $GroupID");
                                    $GroupOthers = ($DB->record_count() - 1);
                                ?>
                                    <? if ($GroupOthers > 0) { ?>
                                        <div style="font-style: italic;">
                                            <a href="reportsv2.php?view=group&amp;id=<?= $GroupID ?>">
                                                <?= t('server.reportsv2.there_are_n_other_reports_for_torrents_in_this_group', [
                                                    'Values' => [
                                                        t('server.reportsv2.there_are_n_other_reports_for_torrents_in_this_group_report', ['Count' => $GroupOthers, 'Values' => [$GroupOthers]]),
                                                    ]
                                                ]) ?>
                                            </a>
                                        </div>
                                    <? } ?>
                                <? } ?>
                                <?


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
                                            <strong class="u-colorWarning"><a href="user.php?id=<?= $FillerID ?>"><?= $FillerName ?></a> <?= t('server.reportsv2.used_this_torrent_to_fill') ?> <a href="requests.php?action=view&amp;id=<?= $RequestID ?>"><?= t('server.reportsv2.this_request') ?></a> <?= time_diff($FilledTime) ?></strong>
                                        </div>
                                    <? } ?>
                                <? } ?>
                            <? } ?>
                        </td>
                    </tr>

                    <?
                    if ($Tracks) { ?>
                        <tr class="Form-row">
                            <td class="Form-label Table-cellRight"><?= t('server.reportsv2.relevant_tracks') ?>:</td>
                            <td class="Form-items">
                                <?= str_replace(' ', ', ', $Tracks) ?>
                            </td>
                        </tr>
                    <? } ?>
                    <? if ($Links) { ?>
                        <tr class="Form-row">
                            <td class="Form-label Table-cellRight"><?= t('server.reportsv2.relevant_links') ?>:</td>
                            <td class="Form-items">
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
                    <? } ?>
                    <? if ($Images) { ?>
                        <tr class="Form-row">
                            <td class="Form-label Table-cellRight"><?= t('server.reportsv2.relevant_images') ?>:</td>
                            <td class="Form-items">
                                <?
                                $Images = explode(' ', $Images);
                                foreach ($Images as $Image) {
                                ?>
                                    <img style="max-width: 200px;" onclick="lightbox.init(this, 200);" src="<?= ImageTools::process($Image) ?>" alt="Relevant image" />
                                <?              } ?>
                            </td>
                        </tr>
                    <? } ?>
                    <?
                    if ($ExtraIDs) {
                    ?>
                        <tr class="Form-row">
                            <td class="Form-label Table-cellRight"><?= t('server.reportsv2.relevant_other_torrents') ?>:</td>
                            <td class="Form-items">
                                <?
                                $Extras = explode(' ', $ExtraIDs);
                                $Value = '';
                                foreach ($Extras as $ExtraID) {
                                    $Value = site_url() . "torrents.php?torrentid=$ExtraID ";
                                ?>
                                    <a href="<?= $Value ?>"><?= $Value ?></a>
                                <?
                                } ?>
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
                        if ($DB->has_results()) {
                            $EditTopics = $DB->to_array();
                        ?>
                            <tr class="Form-row">
                                <td class="Form-label Table-cellRight"><?= t('server.reportsv2.edit_request_about_this_torrent') ?>:</td>
                                <td class="Form-items">
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
                                        <? } ?>
                                    </table>
                                </td>
                            </tr>
                        <? } ?>
                    <? } ?>
                    <? if ($GroupID) { ?>
                        <tr class="Form-row">
                            <td class="Form-label Table-cellRight Table-cellTop"><?= t('server.reportsv2.uploaded_by') ?>:</td>
                            <td class="Form-items">
                                <div class="Form-inputs">
                                    <a href="user.php?id=<?= $UploaderID ?>"><?= $UploaderName ?></a>
                                    <div class="Table-cellRight Table-cellTop"><?= t('server.reportsv2.upload_time') ?>:</div>
                                    <div><?= $Time ?></div>
                                    <?
                                    $DB->query("
						SELECT t.UserID
						FROM reportsv2 AS r
							JOIN torrents AS t ON t.ID = r.TorrentID
						WHERE r.Status != 'Resolved'
							AND t.UserID = $UploaderID");
                                    $UploaderOthers = ($DB->record_count() - 1);

                                    if ($UploaderOthers > 0) { ?>
                                        <div style="font-style: italic;">
                                            <a href="reportsv2.php?view=uploader&amp;id=<?= $UploaderID ?>">
                                                <?= t('server.reportsv2.there_are_n_other_reports_for_torrents_uploaded_by_this_user', ['Values' => [
                                                    t('server.reportsv2.there_are_n_other_reports_for_torrents_uploaded_by_this_user_count', ['Count' => $UploaderOthers, 'Values' => [$UploaderOthers]])
                                                ]]) ?>
                                            </a>
                                        </div>
                                    <?                  }
                                    ?>
                                </div>
                            </td>
                        </tr>
                    <? } ?>
                    <tr class="Form-row">
                        <td class="Form-label Table-cellRight Table-cellTop"><?= t('server.reportsv2.user_comment') ?>:</td>
                        <td class="Form-inputs">
                            <div class="HtmlText">
                                <?= Text::full_format($UserComment) ?>
                            </div>
                            <a href="user.php?id=<?= $ReporterID ?>"><?= $ReporterName ?></a>
                            <div class="Table-cellRight Table-cellTop"><?= t('server.reportsv2.date_reported') ?>:</div>
                            <div><?= $ReportedTime ?></div>

                        </td>
                    </tr>
                    <? if ($Status == 'InProgress') { // END REPORTED STUFF :|: BEGIN MOD STUFF
                    ?>
                        <tr class="Form-row">
                            <td class="Form-label Table-cellRight"><?= t('server.reportsv2.in_progress_by') ?>:</td>
                            <td class="Form-items">
                                <a href="user.php?id=<?= $ResolverID ?>"><?= $ResolverName ?></a>
                            </td>
                        </tr>
                    <? } ?>
                    <tr class="Form-rowSubHeader">
                        <td colspan="2"><?= t('server.reports.conversation') ?></td>
                    </tr>

                    <? if (count($ReportMessages) > 0) { ?>
                        <tr class="Form-row">
                            <td class="Form-label Table-cellRight Table-cellTop">
                                <?= t('server.reports.conversation_history') ?>:
                            </td>
                            <td class="Form-items Table-cellTop">
                                <?
                                foreach ($ReportMessages as $Message) {
                                    if ($Message['SenderID'] == $UploaderID) {
                                        $Name = t('server.top10.torrents_uploaded');
                                    } else if ($Message['SenderID'] == $ReporterID) {
                                        $Name = t('server.reportsv2.reporter');
                                    } else {
                                        $Name = "TM";
                                    }

                                ?>
                                    <div class="BoxBody">
                                        <strong><?= $Name . ' ' . Users::format_username($Message['SenderID']) . ' ' .  t('server.torrents.reports_replied_it') . ' ' . time_diff($Message['SentDate'], 2, true, true) ?></strong>
                                        <div style="padding-top:5px;">
                                            <?= Text::full_format($Message['Body']) ?>
                                        </div>
                                    </div>
                                <?
                                }
                                ?>
                            </td>
                        </tr>
                    <? } ?>

                    <tr class="Form-row">
                        <td class="Form-label Table-cellRight Table-cellTop">
                            <?= t('server.inbox.quote') ?>:
                        </td>
                        <td class="Form-items">
                            <?php new TEXTAREA_PREVIEW('uploader_pm', 'uploader_pm_' . $ReportID, '', 50, 2, true, true, false); ?>
                        </td>
                    </tr>
                    <tr class="Form-row">
                        <td collapse="4">
                            <input class=" Button" type="button" value="<?= t('server.inbox.send_message') ?>" onclick="SendPM(<?= $Idx ?>,<?= $ReportID ?>);" />
                        </td>
                    </tr>
                    <tr class="Form-rowSubHeader">
                        <td colspan="2"><?= t('server.reportsv2.resolve') ?></td>
                    </tr>

                    <? if ($Status != 'Resolved') { ?>
                        <tr class="Form-row">
                            <td class="Form-label Table-cellRight Table-cellTop"><?= t('server.reportsv2.resolve') ?>:</td>
                            <td class="Form-inputs">
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
                                    <span data-tooltip="<?= t('server.reportsv2.warning_title') ?>">
                                        <label for="warning<?= $ReportID ?>"><strong><?= t('server.reportsv2.warning') ?></strong></label>
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
                                        <span data-tooltip="<?= t('server.reportsv2.first_torrent_title') ?>"><strong class="u-colorWarning"><?= t('server.reportsv2.first_torrent') ?></strong></span> |
                                    <?
                                    }
                                    ?>
                                    <? if (check_perms('users_mod')) { ?>
                                        <span data-tooltip="<?= t('server.reportsv2.delete_title') ?>">
                                            <input type="checkbox" name="delete" id="delete<?= $ReportID ?>" />&nbsp;<label for="delete<?= $ReportID ?>"><strong><?= t('server.common.delete') ?></strong></label>
                                        </span> |
                                    <?              } ?>
                                    <span data-tooltip="<?= t('server.reportsv2.remove_upload_privileges_title') ?>">
                                        <input type="checkbox" name="upload" id="upload<?= $ReportID ?>" />&nbsp;<label for="upload<?= $ReportID ?>"><strong><?= t('server.reportsv2.remove_upload_privileges') ?></strong></label>
                                    </span>
                                </div>
                            </td>
                        </tr>
                        <tr class="Form-row">
                            <td class="Form-label Table-cellRight Table-cellTop"><?= t('server.reports.comment') ?>:</td>
                            <td class="Form-inputs">
                                <textarea style="min-height: auto;" class="Input" type="text" name="comment" id="comment<?= $ReportID ?>" size="70"><?= $ModComment ?></textarea>
                            </td>
                        </tr>

                        <tr class="Form-row">
                            <td class="Form-label Table-cellTop Table-cellRight"><?= t('server.reportsv2.custom_trumpable') ?>:</td>
                            <td class="Form-inputs">
                                <textarea class="Input" name="custom_trumpable" id="custom_trumpable<?= $ReportID ?>" cols="50" rows="2" style="min-height: auto;"></textarea>
                            </td>
                        </tr>
                        <tr class="Form-row">
                            <td class="Form-label Table-cellRight"><strong><?= t('server.reportsv2.extra') ?></strong> <?= t('server.reportsv2.space_log_message') ?>:</td>
                            <td class="Form-inputs">
                                <input class="Input" type="text" name="log_message" id="log_message<?= $ReportID ?>" size="40" <?
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
                            </td>
                        </tr>
                        <tr class="Form-row">
                            <td class="Form-label">
                                <strong><?= t('server.reportsv2.extra') ?></strong> <?= t('server.reportsv2.space_staff_notes') ?>:
                            </td>
                            <td class="Form-inputs">
                                <input class=" Input" type="text" name="admin_message" id="admin_message<?= $ReportID ?>" size="40" />
                            </td>
                        </tr>
                        <tr class="Form-row">
                            <td colspan="4" style="text-align: center;">
                                <input class="Button" type="button" value="<?= t('server.reportsv2.invalidate_report') ?>" onclick="Dismiss(<?= $ReportID ?>);" />
                                <input class="Button" type="button" value="<?= t('server.reportsv2.resolve_report') ?>" onclick="ManualResolve(<?= $ReportID ?>);" />
                                <? if ($Status == 'InProgress' && $LoggedUser['ID'] == $ResolverID) { ?>
                                    <input class="Button" type="button" value="<?= t('server.reportsv2.unclaim') ?>" onclick="GiveBack(<?= $ReportID ?>);" />
                                <?                  } else { ?>
                                    <input class="Button" id="grab<?= $ReportID ?>" type="button" value="<?= t('server.reportsv2.claim') ?>" onclick="Grab(<?= $ReportID ?>);" />
                                <?                  }   ?>
                                <input class="Button" type="button" id="submit_<?= $ReportID ?>" value="<?= t('server.common.submit') ?>" onclick="TakeResolve(<?= $ReportID ?>);" />
                                <input class="Button" type="button" value="<?= t('client.common.save') ?>" onclick="UpdateComment(<?= $ReportID ?>);" />
                            </td>
                        </tr>
                    <? } else { ?>
                        <tr class="Form-row">
                            <td class="Form-label Table-cellRight"><?= t('server.reportsv2.resolver') ?>:</td>
                            <td class="Form-items" colspan="3">
                                <a href="user.php?id=<?= $ResolverID ?>"><?= $ResolverName ?></a>
                            </td>
                        </tr>
                        <tr class="Form-row">
                            <td class="Form-label Table-cellRight"><?= t('server.reportsv2.resolve_time') ?>:</td>
                            <td class="Form-items" colspan="3">
                                <?= time_diff($LastChangeTime);
                                echo "\n"; ?>
                            </td>
                        </tr>
                        <tr class="Form-row">
                            <td class="Form-label Table-cellRight Table-cellTop"><?= t('server.reportsv2.report_comment') ?>:</td>
                            <td class="Form-items" colspan="3">
                                <?= $ModComment;
                                echo "\n"; ?>
                            </td>
                        </tr>
                        <tr class="Form-row">
                            <td class="Form-label Table-cellRight"><?= t('server.reportsv2.log_message') ?>:</td>
                            <td class="Form-items" colspan="3">
                                <?= $LogMessage;
                                echo "\n"; ?>
                            </td>
                        </tr>
                        <? if ($GroupID) { ?>
                            <tr class="Form-row">
                                <td colspan="4" style="text-align: center;">
                                    <input class="Button" id="grab<?= $ReportID ?>" type="button" value="<?= t('server.reportsv2.claim') ?>" onclick="Grab(<?= $ReportID ?>);" />
                                </td>
                            </tr>
                        <? } ?>
                    <? } ?>
                </table>
            </form>
        </div>
        <script type="text/javascript">
            Load(<?= $ReportID ?>);
        </script>
<? }
}
