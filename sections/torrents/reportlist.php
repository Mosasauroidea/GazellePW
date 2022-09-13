<?php
if (!isset($_GET['torrentid']) || !is_number($_GET['torrentid'])) {
    error(404);
}
$TorrentID = $_GET['torrentid'];

$Reports = Torrents::get_reports($TorrentID);
$NumReports = count($Reports);
if ($NumReports < 0) {
    die();
}
$Reported = true;
include(CONFIG['SERVER_ROOT'] . '/classes/reportsv2_type.php');
?>
<div class="TorrentDetail-row is-reportList is-block">
    <strong><?= t('server.torrents.report_info') ?> (<?= $NumReports ?>) :</strong>
    <?
    foreach ($Reports as $Report) {
        $ReportID = $Report['ID'];
        if (check_perms('admin_reports')) {
            $ReporterID = $Report['ReporterID'];
            $Reporter = Users::user_info($ReporterID);
            $ReporterName = $Reporter['Username'];
            $ReportLinks = "<a href=\"user.php?id=$ReporterID\">$ReporterName</a> " . t('server.torrents.reported_it');
            $UploaderLinks = Users::format_username($UserID, false, false, false) . " " . t('server.torrents.reply_at');
        } else {
            $ReportLinks = t('server.torrents.someone_reported_it');
            $UploaderLinks = t('server.torrents.uploader_replied_it');
        }

        if (isset($Types[1][$Report['Type']])) {
            $ReportType = $Types[1][$Report['Type']];
        } elseif (isset($Types['master'][$Report['Type']])) {
            $ReportType = $Types['master'][$Report['Type']];
        } else {
            $ReportType = $Types['master']['other'];
        }
        $CanReply = $UserID == G::$LoggedUser['ID'] && !$Report['UploaderReply'] && !$ReadOnly;
        $area = new TEXTAREA_PREVIEW('uploader_reply', '', '', 50, 10, true, true, true, array(
            'placeholder="' . t('server.torrents.reply_it_patiently') . '"'
        ), false);
    ?>
        <div class="Box">
            <div class="Box-header">
                <a href="reportsv2.php?view=report&id=<?= $Report['ID'] ?>">
                    <?= $ReportType['title'] ?>
                </a>
                <div class="Box-headerActions">
                    <? if ($CanReply) {
                    ?>
                        <a class="report_reply_btn" onclick="$('.can_reply_<?= $ReportID ?>').toggle()" href="javascript:void(0)"><?= t('server.torrents.reply') ?></a>
                    <?
                    }
                    ?>
                </div>

            </div>
            <div class="Box-body BoxList">
                <div class="BoxBody">
                    <strong>
                        <?= $ReportLinks ?>
                        <?= t('server.torrents.at', ['Values' => [time_diff($Report['ReportedTime'], 2, true, true)]])
                        ?>
                    </strong>


                    <div style="padding-top:5px;">
                        <?= Text::full_format($Report['UserComment']) ?>
                    </div>
                </div>
                <? if ($Report['UploaderReply']) { ?>
                    <div class="BoxBody">
                        <strong><?= $UploaderLinks . ' ' . time_diff($Report['ReplyTime'], 2, true, true) ?></strong>
                        <div style="padding-top:5px;">
                            <?= Text::full_format($Report['UploaderReply']) ?>
                        </div>
                    </div>
                <?
                }
                ?>
                <form class="can_reply_<?= $ReportID ?>" style="display:none;" action="reportsv2.php?action=takeuploaderreply" method="POST">
                    <input type="hidden" name="reportid" value="<?= $ReportID ?>">
                    <input type="hidden" name="torrentid" value="<?= $TorrentID ?>">
                    <?= $area->getBuffer() ?>
                    <div class="center">
                        <input class="Button" type="submit">
                    </div>
                </form>
            </div>
        </div>
    <?
    }
    ?>
</div>