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
        <h2 class="BodyHeader-nav"><?= page_title_conn([t('server.torrents.history_for_after'), $Title]) ?></h2>
    </div>
    <div class="TableContainer">
        <table class="TableGroupHistory Table">
            <tr class="Table-rowHeader">
                <td class="Table-cell TableTorrent-cellUploadTime"><?= t('server.torrents.date') ?></td>
                <td class="Table-cell"><?= t('server.global.torrent') ?></td>
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
            foreach ($LogEntries as $LogEntry) {
                list($TorrentID, $UserID, $Info, $Time) = $LogEntry;
            ?>
                <tr class="Table-row">
                    <td class="Table-cell TableTorrent-cellUploadTime"><?= $Time ?></td>
                    <?
                    if ($TorrentID != 0) {
                    ?>
                        <td class="Table-cell"><a href="torrents.php?torrentid=<?= $TorrentID ?>"><?= $TorrentID ?></a></td>
                    <? } else {
                    ?>
                        <td class="Table-cell"></td>
                    <? } ?>
                    <td class="Table-cell TableTorrent-cellUserName"><?= Users::format_username($UserID, false, false, false) ?></td>
                    <td class="Table-cell"><?= $Info ?></td>
                </tr>
            <?
            }
            ?>
        </table>
    </div>
</div>
<?
View::show_footer();
?>