<?
View::show_header(t('server.stats.stats'), '', 'PageStatPeer');
?>
<div class="LayoutBody">
    <div class="BodyHeader">
        <div class="BodyHeader-nav">
            <?= t('server.stats.stats') ?>
        </div>
        <div class="BodyHeader-subNav">
            <?= 'Peers' ?>
        </div>
        <div class="BodyNavLinks">
            <a href="stats.php?action=users" class="brackets"><?= t('server.top10.user') ?></a>
            <a href="stats.php?action=torrents" class="brackets"><?= t('server.top10.torrents') ?></a>
        </div>
    </div>
    <div class="ChartRoot">
        <div id="chart_peers_count"></div>
        <div id="chart_seeding_user"></div>
    </div>
</div>
<?
Stats::peersCount();
Stats::seedingUser();
View::show_footer([], 'stats/index.jsx');
