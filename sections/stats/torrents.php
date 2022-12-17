<?
View::show_header(t('server.stats.stats'), '', 'PageStatTorrent');
?>
<div class="LayoutBody">
    <div class="BodyHeader">
        <div class="BodyHeader-nav">
            <?= t('server.stats.stats') ?>
        </div>
        <div class="BodyHeader-subNav">
            <?= t('server.top10.torrents') ?>
        </div>
        <div class="BodyNavLinks">
            <a href="stats.php?action=users" class="brackets"><?= t('server.top10.user') ?></a>
            <a href="stats.php?action=peers" class="brackets">Peers</a>
        </div>
    </div>
    <div class="ChartRoot">
        <div id="chart_torrent_by_day"></div>
        <div id="chart_torrent_by_month"></div>
        <div class="ChartPieContainer" id="chart_torrent_specific"></div>
    </div>
</div>
<?
Stats::torrentByMonth();
Stats::torrentByDay();
Stats::torrentBySpecific();
View::show_footer([], 'stats/index.jsx');
