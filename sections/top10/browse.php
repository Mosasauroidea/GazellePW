<?
include(CONFIG['SERVER_ROOT'] . '/classes/torrenttable.class.php');
require(CONFIG['SERVER_ROOT'] . '/classes/top10_movies.class.php');

$Top10Movies = new Top10Movies();
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
View::show_header(t('server.top10.top') . " $Limit " . t('server.top10.top_movies'), '', 'PageTop10Home');
?>
<div class="LayoutBody">
    <div class="BodyHeader">
        <div class="BodyHeader-nav TorrentViewWrapper">
            <?= t('server.top10.top') ?> <?= $Limit ?> <?= t('server.top10.top_movies') ?>
            <?
            renderTorrentViewButton(TorrentViewScene::Top10Movie);
            ?>
        </div>
        <? Top10View::render_linkbox("movies", "BodyNavLinks"); ?>
    </div>
    <?
    if ($Details == 'all' || $Details == 'day') {
        $Data = $Top10Movies->getData("active_day", ['Limit' => $Limit]);
        generate_torrent_table(t('server.top10.in_the_past_day', ['Values' => [t('server.top10.movies')]]), 'day', $Data, $Limit);
    }
    if ($Details == 'all' || $Details == 'week') {
        $Data = $Top10Movies->getData("active_week", ['Limit' => $Limit]);
        generate_torrent_table(t('server.top10.in_the_past_week', ['Values' => [t('server.top10.movies')]]), 'week', $Data, $Limit);
    }
    if ($Details == 'all' || $Details == 'month') {
        $Data = $Top10Movies->getData("active_month", ['Limit' => $Limit]);
        generate_torrent_table(t('server.top10.in_the_past_month', ['Values' => [t('server.top10.movies')]]), 'month', $Data, $Limit);
    }
    if ($Details == 'all' || $Details == 'year') {
        $Data = $Top10Movies->getData("active_year", ['Limit' => $Limit]);
        generate_torrent_table(t('server.top10.in_the_past_year', ['Values' => [t('server.top10.movies')]]), 'year', $Data, $Limit);
    }
    if ($Details == 'all' || $Details == 'overall') {
        $Data = $Top10Movies->getData("active_all", ['Limit' => $Limit]);
        generate_torrent_table(t('server.top10.most_torrents', ['Values' => [t('server.top10.movies')]]), 'overall', $Data, $Limit);
    }

    if (($Details == 'all' || $Details == 'snatched') && !$Filtered) {
        $Data = $Top10Movies->getData("snatched", ['Limit' => $Limit]);
        generate_torrent_table(t('server.top10.most_snatched', ['Values' => [t('server.top10.movies')]]), 'snatched', $Data, $Limit);
    }

    if (($Details == 'all' || $Details == 'data') && !$Filtered) {
        $Data = $Top10Movies->getData("data", ['Limit' => $Limit]);
        generate_torrent_table(t('server.top10.most_data', ['Values' => [t('server.top10.movies')]]), 'data', $Data, $Limit);
    }

    if (($Details == 'all' || $Details == 'seeded') && !$Filtered) {
        $Data = $Top10Movies->getData("seeded", ['Limit' => $Limit]);
        generate_torrent_table(t('server.top10.most_seed', ['Values' => [t('server.top10.movies')]]), 'seeded', $Data, $Limit);
    }
    ?>
</div>
<?
View::show_footer();

function generate_torrent_table($Caption, $Tag, $Groups, $Limit) {
?>
    <div class="Group">
        <div class="Group-header">
            <div class="Group-headerTitle"><?= t('server.top10.top') ?> <?= "$Limit $Caption" ?>
            </div>
            <? if (empty($_GET['advanced'])) { ?>
                <small class="Group-headerActions top10_quantity_links">
                    <?
                    switch ($Limit) {
                        case 100: ?>
                            <a class="brackets" href="top10.php?details=<?= $Tag ?>"><?= t('server.top10.top') ?> 10</a>
                            - <span class="brackets"><?= t('server.top10.top') ?> 100</span>
                            - <a class="brackets" href="top10.php?type=movies&amp;limit=250&amp;details=<?= $Tag ?>"><?= t('server.top10.top') ?> 250</a>
                        <? break;
                        case 250: ?>
                            <a class="brackets" href="top10.php?details=<?= $Tag ?>"><?= t('server.top10.top') ?> 10</a>
                            - <a class="brackets" href="top10.php?type=movies&amp;limit=100&amp;details=<?= $Tag ?>"><?= t('server.top10.top') ?> 100</a>
                            - <span class="brackets"><?= t('server.top10.top') ?> 250</span>
                        <? break;
                        default: ?>
                            <span class="brackets"><?= t('server.top10.top') ?> 10</span>
                            - <a class="brackets" href="top10.php?type=movies&amp;limit=100&amp;details=<?= $Tag ?>"><?= t('server.top10.top') ?> 100</a>
                            - <a class="brackets" href="top10.php?type=movies&amp;limit=250&amp;details=<?= $Tag ?>"><?= t('server.top10.top') ?> 250</a>
                    <? } ?>
                </small>
            <? } ?>
        </div>
        <div class="Group-body">
            <?
            if (empty($Groups)) {
                echo '<table>
<tr class="Table-row">
    <td class="center Table-cell Table-cellCenter" colspan="9">' . t('server.top10.found_no_torrents_matching_the_criteria') . '</td>
</tr>
</table></div></div>';
                return;
            }
            $tableRender = newGroupTorrentView(TorrentViewScene::Top10Movie, $Groups);
            $tableRender->render(['Variant' => 'FiveGrid']);
            ?>
        </div>
    </div>
<? } ?>