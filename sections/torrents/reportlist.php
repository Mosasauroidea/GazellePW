<?php
if (!isset($_GET['torrentid']) || !is_number($_GET['torrentid'])) {
    error(404);
}
$TorrentID = $_GET['torrentid'];


$Reports = Reports::get_reports($TorrentID);
$NumReports = count($Reports);
if ($NumReports < 0) {
    die();
}
$ReportIDs = array_column($Reports, 'ID');
$ReportsMessage = Reports::get_reports_messages($ReportIDs);
$Reported = true;
$ReportAdmin = check_perms('admin_reports');
$UploaderID = Torrents::get_torrent($TorrentID)['UserID'];
include(CONFIG['SERVER_ROOT'] . '/classes/reportsv2_type.php');
?>
<div class="TorrentDetail-row is-reportList is-block">
    <strong><?= t('server.torrents.report_info') ?> (<?= $NumReports ?>) :</strong>
    <?
    foreach ($Reports as $Report) {
        $ReportID = $Report['ID'];
        $ReporterID = $Report['ReporterID'];
        $IsAdmin = check_perms('admin_reports');
        if ($IsAdmin) {
            $Reporter = Users::user_info($ReporterID);
            $ReporterName = $Reporter['Username'];
            $ReportLinks = "<a href=\"user.php?id=$ReporterID\">$ReporterName</a> " . t('server.torrents.reported_it');
        } else {
            $ReportLinks = t('server.torrents.someone_reported_it');
        }
        $IsUploader = $LoggedUser['ID'] == $UploaderID;
        $IsReporter = $LoggedUser['ID'] == $ReporterID;
        $ReplyLinks = t('server.torrents.reports_replied_it');


        if (isset($Types[1][$Report['Type']])) {
            $ReportType = $Types[1][$Report['Type']];
        } elseif (isset($Types['master'][$Report['Type']])) {
            $ReportType = $Types['master'][$Report['Type']];
        } else {
            $ReportType = $Types['master']['other'];
        }
        $CanReply = ($IsUploader || $IsReporter || $IsAdmin) && !$ReadOnly;
        $area = new TEXTAREA_PREVIEW('uploader_reply', '', '', 50, 10, true, true, true, array(
            'placeholder="' . t('server.torrents.reply_it_patiently') . '"'
        ), false);
    ?>
        <div class="Box-body BoxList">
            <div class="BoxBody">
                <strong>
                    <?= $ReportLinks; ?>
                    <? if ($ReportAdmin) {
                    ?>
                        <a href="reportsv2.php?view=report&id=<?= $Report['ID'] ?>">
                            <?= $ReportType['title'] ?>
                        </a>
                    <?
                    } else {
                    ?>
                        <?= $ReportType['title'] ?>
                    <?
                    }
                    ?>
                    <?= t('server.torrents.at', ['Values' => [time_diff($Report['ReportedTime'], 2, true, true)]])
                    ?>
                </strong>
                <div style="padding-top:5px;">
                    <?= Text::full_format($Report['UserComment']) ?>
                </div>
            </div>

            <? foreach ($ReportsMessage[$ReportID] as $Message) {
                if ($Message['SenderID'] == $UploaderID) {
                    $Name = t('server.top10.torrents_uploaded');
                } else if ($Message['SenderID'] == $ReporterID) {
                    $Name = t('server.reportsv2.reporter');
                } else {
                    $Name = "TM";
                }

            ?>
                <div class="BoxBody">
                    <strong><?= $Name . ($IsAdmin ? ' ' . Users::format_username($Message['SenderID']) : '') . ' ' . $ReplyLinks . ' ' . time_diff($Message['SentDate'], 2, true, true) ?></strong>
                    <div style="padding-top:5px;">
                        <?= Text::full_format($Message['Body']) ?>
                    </div>
                </div>
            <?
            }
            ?>
            <? if ($CanReply) {
            ?>
                <form class="Form can_reply_<?= $ReportID ?>" action="reportsv2.php?action=takeuploaderreply" method="POST">
                    <input type="hidden" name="reportid" value="<?= $ReportID ?>">
                    <input type="hidden" name="torrentid" value="<?= $TorrentID ?>">
                    <?= $area->getBuffer() ?>
                    <div class="Form-row center">
                        <input class="Button" type="submit">
                    </div>
                </form>
            <?
            }
            ?>
        </div>
    <?
    }
    ?>
</div>