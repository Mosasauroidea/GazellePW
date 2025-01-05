<?
$GroupID = $_GET['groupid'];
if (!is_number($GroupID)) {
    error(404);
}

View::show_header(t('server.torrents.history_for_group_after'), '', 'PageTorrentGroupLog');

$Group = Torrents::get_group($GroupID);
$Title = Torrents::group_name($Group);
?>

<div class="LayoutBody">
    <div class="BodyHeader">
        <div class="BodyHeader-nav"><?= t('server.torrents.history_for_after') ?></div>
        <div class="BodyHeader-subNav"><?= $Title ?></div>
    </div>
    <div class="TableContainer">
        <table class="TableGroupHistory Table">
            <tr class="Table-rowHeader">
                <td class="Table-cell TableTorrent-cellUploadTime"><?= t('server.torrents.date') ?></td>
                <td class="Table-cell TableTorrent-cellID"><?= t('server.common.torrent') ?></td>
                <td class="Table-cell TableTorrent-cellUserName"><?= t('server.torrents.user') ?></td>
                <td class="Table-cell"><?= t('server.torrents.info') ?></td>
            </tr>
            <?
            $Log = $DB->query("
			SELECT TorrentID, UserID, Info, Time
			FROM group_log
			WHERE GroupID = $GroupID
			ORDER BY Time DESC");
            $LogEntries = $DB->to_array(false, MYSQLI_NUM);
            $i = 0;
            foreach ($LogEntries as $LogEntry) {
                list($TorrentID, $UserID, $Info, $Time) = $LogEntry;
                if (strpos($Info, 'deleted') !== false) {
                    $Color = 'red';
                } else if (strpos($Info, 'uploaded ') !== false) {
                    $Color = 'green';
                } else {
                    $Color = '';
                }
            ?>
                <tr class="Table-row">
                    <td class="Table-cell TableTorrent-cellUploadTime"><?= $Time ?></td>
                    <?
                    if ($TorrentID != 0) {
                    ?>
                        <td class="Table-cell  TableTorrent-cellID"><a href="torrents.php?torrentid=<?= $TorrentID ?>"><?= $TorrentID ?></a></td>
                    <? } else {
                    ?>
                        <td class="Table-cell"></td>
                    <? } ?>
                    <td class="Table-cell TableTorrent-cellUserName"><?= Users::format_username($UserID, false, false, false) ?></td>
                    <td class="Table-cell">
                        <span style="color: <?= $Color ?>">
                            <?
                            View::long_text('log_' . $i, $Info, 1);
                            ?>
                        </span>
                    </td>
                </tr>
            <?
                $i++;
            }
            ?>
        </table>
    </div>
</div>
<?
View::show_footer();
?>