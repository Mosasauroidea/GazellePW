<?
if (!check_perms('site_view_flow')) {
    error(403);
}
View::show_header(t('server.tools.torrents'), '', 'PageToolTorrentStat');

if (!$TorrentStats = $Cache->get_value('new_torrent_stats')) {
    $DB->query("
		SELECT COUNT(ID), SUM(Size), SUM(FileCount)
		FROM torrents");
    list($TorrentCount, $TotalSize, $TotalFiles) = $DB->next_record();
    $DB->query("
		SELECT COUNT(ID)
		FROM users_main
		WHERE Enabled = '1'");
    list($NumUsers) = $DB->next_record();
    $DB->query("SELECT COUNT(ID), SUM(Size), SUM(FileCount) FROM torrents WHERE Time > SUBDATE('" . sqltime() . "', INTERVAL 1 DAY)");
    list($DayNum, $DaySize, $DayFiles) = $DB->next_record();
    $DB->query("SELECT COUNT(ID), SUM(Size), SUM(FileCount) FROM torrents WHERE Time > SUBDATE('" . sqltime() . "', INTERVAL 7 DAY)");
    list($WeekNum, $WeekSize, $WeekFiles) = $DB->next_record();
    $DB->query("SELECT COUNT(ID), SUM(Size), SUM(FileCount) FROM torrents WHERE Time > SUBDATE('" . sqltime() . "', INTERVAL 30 DAY)");
    list($MonthNum, $MonthSize, $MonthFiles) = $DB->next_record();
    $Cache->cache_value('new_torrent_stats', array(
        $TorrentCount, $TotalSize, $TotalFiles,
        $NumUsers, $DayNum, $DaySize, $DayFiles,
        $WeekNum, $WeekSize, $WeekFiles, $MonthNum,
        $MonthSize, $MonthFiles
    ), 3600);
} else {
    list(
        $TorrentCount, $TotalSize, $TotalFiles, $NumUsers, $DayNum, $DaySize, $DayFiles,
        $WeekNum, $WeekSize, $WeekFiles, $MonthNum, $MonthSize, $MonthFiles
    ) = $TorrentStats;
}

?>
<div class="LayoutBody">
    <div class="BodyHeader">
        <h2 class="BodyHeader-nav"><?= t('server.tools.torrent_stats') ?></h2>
    </div>
    <div class="Box">
        <div class="Box-header"><?= t('server.tools.overall_stats') ?></div>
        <div class="Box-body">
            <ul class="stats nobullet">
                <li><strong><?= t('server.tools.total_torrents') ?>: </strong><?= number_format($TorrentCount) ?></li>
                <li><strong><?= t('server.tools.total_size') ?>: </strong><?= Format::get_size($TotalSize) ?></li>
                <li><strong><?= t('server.tools.total_files') ?>: </strong><?= number_format($TotalFiles) ?></li>
                <br />
                <li><strong><?= t('server.tools.mean_torrents_per_user') ?>: </strong><?= number_format($TorrentCount / $NumUsers) ?></li>
                <li><strong><?= t('server.tools.mean_torrent_size') ?>: </strong><?= Format::get_size($TotalSize / $TorrentCount) ?></li>
                <li><strong><?= t('server.tools.mean_files_per_torrent') ?>: </strong><?= number_format($TotalFiles / $TorrentCount) ?></li>
                <li><strong><?= t('server.tools.mean_filesize') ?>: </strong><?= Format::get_size($TotalSize / $TotalFiles) ?></li>
            </ul>
        </div>
    </div>
    <div class="Box">
        <div class="Box-header"><?= t('server.tools.upload_frequency') ?></div>
        <div class="Box-body">
            <ul class="stats nobullet">
                <li><strong><?= t('server.tools.torrents_today') ?>: </strong><?= number_format($DayNum) ?></li>
                <li><strong><?= t('server.tools.size_today') ?>: </strong><?= Format::get_size($DaySize) ?></li>
                <li><strong><?= t('server.tools.files_today') ?>: </strong><?= number_format($DayFiles) ?></li>
                <br />
                <li><strong><?= t('server.tools.torrents_this_week') ?>: </strong><?= number_format($WeekNum) ?></li>
                <li><strong><?= t('server.tools.size_this_week') ?>: </strong><?= Format::get_size($WeekSize) ?></li>
                <li><strong><?= t('server.tools.files_this_week') ?>: </strong><?= number_format($WeekFiles) ?></li>
                <br />
                <li><strong><?= t('server.tools.torrents_per_day_this_week') ?>: </strong><?= number_format($WeekNum / 7) ?></li>
                <li><strong><?= t('server.tools.size_per_day_this_week') ?>: </strong><?= Format::get_size($WeekSize / 7) ?></li>
                <li><strong><?= t('server.tools.files_per_day_this_week') ?>: </strong><?= number_format($WeekFiles / 7) ?></li>
                <br />
                <li><strong><?= t('server.tools.torrents_this_month') ?>: </strong><?= number_format($MonthNum) ?></li>
                <li><strong><?= t('server.tools.size_this_month') ?>: </strong><?= Format::get_size($MonthSize) ?></li>
                <li><strong><?= t('server.tools.files_this_month') ?>: </strong><?= number_format($MonthFiles) ?></li>
                <br />
                <li><strong><?= t('server.tools.torrents_per_day_this_month') ?>: </strong><?= number_format($MonthNum / 30) ?></li>
                <li><strong><?= t('server.tools.size_per_day_this_month') ?>: </strong><?= Format::get_size($MonthSize / 30) ?></li>
                <li><strong><?= t('server.tools.files_per_day_this_month') ?>: </strong><?= number_format($MonthFiles / 30) ?></li>
            </ul>
        </div>
    </div>
</div>
<?
View::show_footer();
?>