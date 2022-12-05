<?
if (!check_perms('users_mod')) {
    error(404);
}
// if (!check_perms('site_top10_history')) {
//  error(403);
// }
View::show_header(t('server.top10.top_10_torrents_history'), '', 'PageTop10History');
?>
<div class="LayoutBody">
    <div class="BodyHeader">
        <h2 class="BodyHeader-nav"><?= t('server.top10.top_10_torrents') ?></h2>
        <? Top10View::render_linkbox('', 'BodyNavLinks'); ?>
    </div>
    <form class="Form SearchPage Box SearchTop10History" name="top10" method="get" action="">
        <input type="hidden" name="type" value="history" />
        <h3><?= t('server.top10.search_for_a_date_after') ?></h3>
        <table class="Form-rowList">
            <tr class="Form-row">
                <td class="Form-label"><?= t('server.top10.date') ?>:</td>
                <td class="Form-inputs"><input class="Input" type="text" id="date" name="date" value="<?= !empty($_GET['date']) ? display_str($_GET['date']) : 'YYYY-MM-DD' ?>" onfocus="if ($('#date').raw().value == 'YYYY-MM-DD') { $('#date').raw().value = ''; }" /></td>
            </tr>
            <tr class="Form-row">
                <td class="Form-label"><?= t('server.top10.type') ?>:</td>
                <td class="Form-inputs">
                    <div class="Radio">
                        <input class="Input" type="radio" name="datetype" value="day" checked="checked">
                        <label class="Radio-label"><?= t('server.top10.day') ?></label>
                    </div>
                    <div class="Radio">
                        <input class="Input" type="radio" name="datetype" value="week">
                        <label class="Radio-label"><?= t('server.top10.week') ?></label>
                    </div>
                </td>
            </tr>
            <tr class="Form-row">
                <td class="Form-submit" colspan="2">
                    <input class="Button" type="submit" value="<?= t('server.common.submit') ?>" />
                </td>
            </tr>
        </table>
    </form>
    <?
    if (!empty($_GET['date'])) {
        $Date = $_GET['date'];
        $SQLTime = $Date . ' 00:00:00';
        if (!validDate($SQLTime)) {
            error(t('server.top10.sth_is_wrong_with_the_date_you_provided'));
        }

        if (empty($_GET['datetype']) || $_GET['datetype'] == 'day') {
            $Type = 'day';
            $Where = "
			WHERE th.Date BETWEEN '$SQLTime' AND '$SQLTime' + INTERVAL 24 HOUR
				AND Type = 'Daily'";
        } else {
            $Type = 'week';
            $Where = "
			WHERE th.Date BETWEEN '$SQLTime' - AND '$SQLTime' + INTERVAL 7 DAY
				AND Type = 'Weekly'";
        }

        $Details = $Cache->get_value("top10_history_$SQLTime");
        if ($Details === false) {
            $DB->query("
			SELECT
				tht.Rank,
				tht.TitleString,
				tht.TorrentID,
                g.ID,
				g.Name,
                g.SubName,
				g.CategoryID,
				t.Scene,
				t.RemasterYear,
				g.Year,
				t.RemasterTitle,
				t.Snatched,
				t.Seeders,
				t.Leechers,
                t.Codec,
                t.Processing,
                t.Source, 
                t.Container,
                t.Resolution,
                t.Size
			FROM top10_history AS th
				LEFT JOIN top10_history_torrents AS tht ON tht.HistoryID = th.ID
				LEFT JOIN torrents AS t ON t.ID = tht.TorrentID
				LEFT JOIN torrents_group AS g ON g.ID = t.GroupID
			$Where
			ORDER BY tht.Rank ASC");

            $Details = $DB->to_array();

            $Cache->cache_value("top10_history_$SQLTime", $Details, 3600 * 24);
        }
    ?>

        <div class="Group">
            <div class="Group-header">
                <div class="Group-headerTitle">
                    <?= t('server.top10.top_10_for', ['Values' => [
                        ($Type == 'day' ? $Date : t('server.top10.the_first_week_after', ['Values' => [$Date]]))
                    ]]) ?>
                </div>
            </div>
            <div class="Group-body">
                <?
                $TableTorrentClass = G::$LoggedUser['SettingTorrentTitle']['Alternative'] ? 'is-alternative' : '';
                ?>
                <table class="TableTorrent Table $TableTorrentClass">
                    <tr class="Table-rowHeader">
                        <td class="Table-cell" style="width: 15px;"></td>
                        <td class="Table-cell"></td>
                        <td class="Table-cell"><?= t('server.top10.name') ?></td>
                    </tr>
                    <?
                    foreach ($Details as $Torrent) {
                        $GroupID = $Torrent['ID'];
                        $TorrentID = $Torrent['TorrentID'];
                        if ($GroupID) {
                            $TorrentDetail = Torrents::get_torrent($TorrentID);
                            $TitleString = Torrents::torrent_simple_view($TorrentDetail['Group'], $TorrentDetail);
                        } else {
                            $TitleString = "$TitleString (Deleted)";
                        } // if ($GroupID)
                        $TorrentTags = new Tags($TagString);
                    ?>
                        <tr class="Table-row <?= $Highlight ?>">
                            <td class="Table-cell"><strong><?= $Rank ?></strong></td>
                            <td class="Table-cell">
                                <div data-tooltip="<?= $TorrentTags->title() ?>" class="<?= Format::css_category($GroupCategoryID) ?> <?= $TorrentTags->css_name() ?>"></div>
                            </td>
                            <td class="Table-cell">
                                <span><?= ($GroupID ? '<a href="torrents.php?action=download&amp;id=' . $TorrentID . '&amp;authkey=' . $LoggedUser['AuthKey'] . '&amp;torrent_pass=' . $LoggedUser['torrent_pass'] . ' data-tooltip="Download" class="brackets">DL</a>' : '(Deleted)') ?></span>
                                <?= $TitleString ?>
                                <div class="tags"><?= $TorrentTags->format() ?></div>
                            </td>
                        </tr>
                    <?
                    } //foreach ($Details as $Detail)
                    ?>
                </table>
            </div>
        </div>
</div>
<?
    }
    View::show_footer();
?>