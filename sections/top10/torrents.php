<?
include(CONFIG['SERVER_ROOT'] . '/classes/torrenttable.class.php');
$Where = array();
if (!empty($_GET['advanced']) && check_perms('site_advanced_top10')) {
    $Details = 'all';
    $Limit = 10;
} else {
    // error out on invalid requests (before caching)
    if (isset($_GET['details'])) {
        if (in_array($_GET['details'], array('day', 'week', 'overall', 'snatched', 'data', 'seeded', 'month', 'year'))) {
            $Details = $_GET['details'];
        } else {
            error(404);
        }
    } else {
        $Details = 'all';
    }

    // defaults to 10 (duh)
    $Limit = (isset($_GET['limit']) ? intval($_GET['limit']) : 10);
    $Limit = (in_array($Limit, array(10, 100, 250)) ? $Limit : 10);
}
$Filtered = !empty($Where);
View::show_header(t('server.top10.top') . " $Limit " . t('server.top10.top_torrents'), '', 'PageTop10Torrents');
?>
<div class="LayoutBody">
    <div class="BodyHeader">
        <div class="BodyHeader-nav TorrentViewWrapper"><?= t('server.top10.top') ?> <?= $Limit ?> <?= t('server.top10.top_torrents') ?>
            <?
            renderTorrentViewButton(TorrentViewScene::Top10Torrent);
            ?>
        </div>
        <? Top10View::render_linkbox("torrents", "BodyNavLinks"); ?>
    </div>
    <?

    $GroupBySum = '';
    $GroupBy = '';
    if (isset($_GET['groups']) && $_GET['groups'] == 'show') {
        $GroupBy = ' GROUP BY g.ID ';
        $GroupBySum = md5($GroupBy);
    }

    if (!empty($Where)) {
        $Where = '(' . implode(' AND ', $Where) . ')';
        $WhereSum = md5($Where);
    } else {
        $WhereSum = '';
    }
    $BaseQuery = '
	SELECT
        t.ID as TorrentID,
		g.ID,
		g.Name,
		g.CategoryID,
		g.WikiImage,
		t.Scene,
		t.RemasterYear,
		g.Year,
		t.RemasterTitle,
		t.Snatched,
		t.Seeders,
		t.Leechers,
		((t.Size * t.Snatched) + (t.Size * 0.5 * t.Leechers)) AS Data,
		g.ReleaseType,
		t.Size
	FROM torrents AS t
		LEFT JOIN freetorrents_timed as fttd on fttd.TorrentID = t.id
		LEFT JOIN torrents_group AS g ON g.ID = t.GroupID';

    if ($Details == 'all' || $Details == 'day') {
        $TopTorrentsActiveLastDay = $Cache->get_value('top10tor_day_' . $Limit . $WhereSum . $GroupBySum);
        if ($TopTorrentsActiveLastDay === false) {
            if ($Cache->get_query_lock('top10')) {
                $DayAgo = time_minus(86400);
                $Query = $BaseQuery . ' WHERE t.Seeders>0 AND ';
                if (!empty($Where)) {
                    $Query .= $Where . ' AND ';
                }
                $Query .= "
				t.Time>'$DayAgo'
				$GroupBy
				ORDER BY (t.Seeders + t.Leechers) DESC
				LIMIT $Limit;";
                $DB->query($Query);
                $TopTorrentsActiveLastDay = $DB->to_array(false, MYSQLI_ASSOC);
                $Cache->cache_value('top10tor_day_' . $Limit . $WhereSum . $GroupBySum, $TopTorrentsActiveLastDay, 3600 * 2);
                $Cache->clear_query_lock('top10');
            } else {
                $TopTorrentsActiveLastDay = false;
            }
        }
        generate_torrent_table(t('server.top10.in_the_past_day', ['Values' => [t('server.top10.torrents')]]), 'day', $TopTorrentsActiveLastDay, $Limit);
    }
    if ($Details == 'all' || $Details == 'week') {
        $TopTorrentsActiveLastWeek = $Cache->get_value('top10tor_week_' . $Limit . $WhereSum . $GroupBySum);
        if ($TopTorrentsActiveLastWeek === false) {
            if ($Cache->get_query_lock('top10')) {
                $WeekAgo = time_minus(604800);
                $Query = $BaseQuery . ' WHERE ';
                if (!empty($Where)) {
                    $Query .= $Where . ' AND ';
                }
                $Query .= "
				t.Time>'$WeekAgo'
				$GroupBy
				ORDER BY (t.Seeders + t.Leechers) DESC
				LIMIT $Limit;";
                $DB->query($Query);
                $TopTorrentsActiveLastWeek = $DB->to_array(false, MYSQLI_ASSOC);
                $Cache->cache_value('top10tor_week_' . $Limit . $WhereSum . $GroupBySum, $TopTorrentsActiveLastWeek, 3600 * 6);
                $Cache->clear_query_lock('top10');
            } else {
                $TopTorrentsActiveLastWeek = false;
            }
        }
        generate_torrent_table(t('server.top10.in_the_past_week', ['Values' => [t('server.top10.torrents')]]), 'week', $TopTorrentsActiveLastWeek, $Limit);
    }

    if ($Details == 'all' || $Details == 'month') {
        $TopTorrentsActiveLastMonth = $Cache->get_value('top10tor_month_' . $Limit . $WhereSum . $GroupBySum);
        if ($TopTorrentsActiveLastMonth === false) {
            if ($Cache->get_query_lock('top10')) {
                $Query = $BaseQuery . ' WHERE ';
                if (!empty($Where)) {
                    $Query .= $Where . ' AND ';
                }
                $Query .= "
				t.Time>'" . sqltime() . "' - INTERVAL 1 MONTH
				$GroupBy
				ORDER BY (t.Seeders + t.Leechers) DESC
				LIMIT $Limit;";
                $DB->query($Query);
                $TopTorrentsActiveLastMonth = $DB->to_array(false, MYSQLI_ASSOC);
                $Cache->cache_value('top10tor_month_' . $Limit . $WhereSum . $GroupBySum, $TopTorrentsActiveLastMonth, 3600 * 6);
                $Cache->clear_query_lock('top10');
            } else {
                $TopTorrentsActiveLastMonth = false;
            }
        }
        generate_torrent_table(t('server.top10.in_the_past_month', ['Values' => [t('server.top10.torrents')]]), 'month', $TopTorrentsActiveLastMonth, $Limit);
    }

    if ($Details == 'all' || $Details == 'year') {
        $TopTorrentsActiveLastYear = $Cache->get_value('top10tor_year_' . $Limit . $WhereSum . $GroupBySum);
        if ($TopTorrentsActiveLastYear === false) {
            if ($Cache->get_query_lock('top10')) {
                // IMPORTANT NOTE - we use WHERE t.Seeders>200 in order to speed up this query. You should remove it!
                $Query = $BaseQuery . ' WHERE ';
                if ($Details == 'all' && !$Filtered) {
                    $Query .= 't.Seeders>=200 AND ';
                    if (!empty($Where)) {
                        $Query .= $Where . ' AND ';
                    }
                } elseif (!empty($Where)) {
                    $Query .= $Where . ' AND ';
                }
                $Query .= "
				t.Time>'" . sqltime() . "' - INTERVAL 1 YEAR
				$GroupBy
				ORDER BY (t.Seeders + t.Leechers) DESC
				LIMIT $Limit;";
                $DB->query($Query);
                $TopTorrentsActiveLastYear = $DB->to_array(false, MYSQLI_ASSOC);
                $Cache->cache_value('top10tor_year_' . $Limit . $WhereSum . $GroupBySum, $TopTorrentsActiveLastYear, 3600 * 6);
                $Cache->clear_query_lock('top10');
            } else {
                $TopTorrentsActiveLastYear = false;
            }
        }
        generate_torrent_table(t('server.top10.in_the_past_year', ['Values' => [t('server.top10.torrents')]]), 'year', $TopTorrentsActiveLastYear, $Limit);
    }

    if ($Details == 'all' || $Details == 'overall') {
        $TopTorrentsActiveAllTime = $Cache->get_value('top10tor_overall_' . $Limit . $WhereSum . $GroupBySum);
        if ($TopTorrentsActiveAllTime === false) {
            if ($Cache->get_query_lock('top10')) {
                // IMPORTANT NOTE - we use WHERE t.Seeders>500 in order to speed up this query. You should remove it!
                $Query = $BaseQuery;
                if ($Details == 'all' && !$Filtered) {
                    $Query .= " WHERE t.Seeders>=500 ";
                    if (!empty($Where)) {
                        $Query .= ' AND ' . $Where;
                    }
                } elseif (!empty($Where)) {
                    $Query .= ' WHERE ' . $Where;
                }
                $Query .= "
				$GroupBy
				ORDER BY (t.Seeders + t.Leechers) DESC
				LIMIT $Limit;";
                $DB->query($Query);
                $TopTorrentsActiveAllTime = $DB->to_array(false, MYSQLI_ASSOC);
                $Cache->cache_value('top10tor_overall_' . $Limit . $WhereSum . $GroupBySum, $TopTorrentsActiveAllTime, 3600 * 6);
                $Cache->clear_query_lock('top10');
            } else {
                $TopTorrentsActiveAllTime = false;
            }
        }
        generate_torrent_table(t('server.top10.most_torrents', ['Values' => [t('server.top10.torrents')]]), 'overall', $TopTorrentsActiveAllTime, $Limit);
    }

    if (($Details == 'all' || $Details == 'snatched') && !$Filtered) {
        $TopTorrentsSnatched = $Cache->get_value('top10tor_snatched_' . $Limit . $WhereSum . $GroupBySum);
        if ($TopTorrentsSnatched === false) {
            if ($Cache->get_query_lock('top10')) {
                $Query = $BaseQuery;
                if (!empty($Where)) {
                    $Query .= ' WHERE ' . $Where;
                }
                $Query .= "
				$GroupBy
				ORDER BY t.Snatched DESC
				LIMIT $Limit;";
                $DB->query($Query);
                $TopTorrentsSnatched = $DB->to_array(false, MYSQLI_ASSOC);
                $Cache->cache_value('top10tor_snatched_' . $Limit . $WhereSum . $GroupBySum, $TopTorrentsSnatched, 3600 * 6);
                $Cache->clear_query_lock('top10');
            } else {
                $TopTorrentsSnatched = false;
            }
        }
        generate_torrent_table(t('server.top10.most_snatched', ['Values' => [t('server.top10.torrents')]]), 'snatched', $TopTorrentsSnatched, $Limit);
    }

    if (($Details == 'all' || $Details == 'data') && !$Filtered) {
        $TopTorrentsTransferred = $Cache->get_value('top10tor_data_' . $Limit . $WhereSum . $GroupBySum);
        if ($TopTorrentsTransferred === false) {
            if ($Cache->get_query_lock('top10')) {
                // IMPORTANT NOTE - we use WHERE t.Snatched>100 in order to speed up this query. You should remove it!
                $Query = $BaseQuery;
                if ($Details == 'all') {
                    $Query .= " WHERE t.Snatched>=100 ";
                    if (!empty($Where)) {
                        $Query .= ' AND ' . $Where;
                    }
                }
                $Query .= "
				$GroupBy
				ORDER BY Data DESC
				LIMIT $Limit;";
                $DB->query($Query);
                $TopTorrentsTransferred = $DB->to_array(false, MYSQLI_ASSOC);
                $Cache->cache_value('top10tor_data_' . $Limit . $WhereSum . $GroupBySum, $TopTorrentsTransferred, 3600 * 6);
                $Cache->clear_query_lock('top10');
            } else {
                $TopTorrentsTransferred = false;
            }
        }
        generate_torrent_table(t('server.top10.most_data', ['Values' => [t('server.top10.torrents')]]), 'data', $TopTorrentsTransferred, $Limit);
    }

    if (($Details == 'all' || $Details == 'seeded') && !$Filtered) {
        $TopTorrentsSeeded = $Cache->get_value('top10tor_seeded_' . $Limit . $WhereSum . $GroupBySum);
        if ($TopTorrentsSeeded === false) {
            if ($Cache->get_query_lock('top10')) {
                $Query = $BaseQuery;
                if (!empty($Where)) {
                    $Query .= ' WHERE ' . $Where;
                }
                $Query .= "
				$GroupBy
				ORDER BY t.Seeders DESC
				LIMIT $Limit;";
                $DB->query($Query);
                $TopTorrentsSeeded = $DB->to_array(false, MYSQLI_ASSOC);
                $Cache->cache_value('top10tor_seeded_' . $Limit . $WhereSum . $GroupBySum, $TopTorrentsSeeded, 3600 * 6);
                $Cache->clear_query_lock('top10');
            } else {
                $TopTorrentsSeeded = false;
            }
        }
        generate_torrent_table(t('server.top10.most_seed', ['Values' => [t('server.top10.torrents')]]), 'seeded', $TopTorrentsSeeded, $Limit);
    }
    ?>
</div>
<?
View::show_footer();

// generate a table based on data from most recent query to $DB
function generate_torrent_table($Caption, $Tag, $Details, $Limit) {
?>
    <div class="Group">
        <div class="Group-header">
            <div class="Group-headerTitle"><?= t('server.top10.top') ?> <?= "$Limit $Caption" ?></div>
            <? if (empty($_GET['advanced'])) { ?>
                <small class="Group-headerActions top10_quantity_links">
                    <?
                    switch ($Limit) {
                        case 100: ?>
                            <a href="top10.php?details=<?= $Tag ?>" class="brackets"><?= t('server.top10.top') ?> 10</a>
                            - <span class="brackets"><?= t('server.top10.top') ?> 100</span>
                            - <a href="top10.php?type=torrents&amp;limit=250&amp;details=<?= $Tag ?>" class="brackets"><?= t('server.top10.top') ?> 250</a>
                        <? break;
                        case 250: ?>
                            <a href="top10.php?details=<?= $Tag ?>" class="brackets"><?= t('server.top10.top') ?> 10</a>
                            - <a href="top10.php?type=torrents&amp;limit=100&amp;details=<?= $Tag ?>" class="brackets"><?= t('server.top10.top') ?> 100</a>
                            - <span class="brackets"><?= t('server.top10.top') ?> 250</span>
                        <? break;
                        default: ?>
                            <span class="brackets"><?= t('server.top10.top') ?> 10</span>
                            - <a href="top10.php?type=torrents&amp;limit=100&amp;details=<?= $Tag ?>" class="brackets"><?= t('server.top10.top') ?> 100</a>
                            - <a href="top10.php?type=torrents&amp;limit=250&amp;details=<?= $Tag ?>" class="brackets"><?= t('server.top10.top') ?> 250</a>
                    <? } ?>
                </small>
            <? } ?>
        </div>
        <div class="Group-body">
            <?
            $TorrentLists = [];
            if ($Details) {
                $GroupIDs = [];
                foreach ($Details as $Detail) {
                    $GroupIDs[] = $Detail['ID'];
                }
                $Groups = Torrents::get_groups($GroupIDs);
                foreach ($Details as $Detail) {
                    $TorrentLists[] = Torrents::convert_torrent($Groups[$Detail['ID']], $Detail['TorrentID']);
                }
            }
            $tableRender = newUngroupTorrentView(TorrentViewScene::Top10Torrent, $TorrentLists);
            $tableRender->with_number(true)->render([]);
            ?>
        </div>
    </div>
<? } ?>