<?php
if (!isset($_GET['torrentid']) || !is_number($_GET['torrentid']) || !check_perms('site_view_torrent_snatchlist')) {
    error(404);
}
$TorrentID = $_GET['torrentid'];

if (!empty($_GET['page']) && is_number($_GET['page'])) {
    $Page = $_GET['page'];
    $Limit = (string)(($Page - 1) * 100) . ', 100';
} else {
    $Page = 1;
    $Limit = 100;
}

$Result = $DB->query("
			SELECT
				SQL_CALC_FOUND_ROWS
				uid,
				tstamp
			FROM xbt_snatched
			WHERE fid = '$TorrentID'
			ORDER BY tstamp DESC
			LIMIT $Limit");
$Results = $DB->to_array('uid', MYSQLI_ASSOC);

$DB->query('SELECT FOUND_ROWS()');
list($NumResults) = $DB->next_record();

?>
<div class="TorrentDetail-row is-snatchedList is-block">
    <strong class="TorrentDetailSnatchedList-title" id="snatched_box_title"><?= t('server.torrents.list_of_snatchers') ?>:</strong>
    <? if ($NumResults > 100) { ?>
        <div class="BodyNavLinks"><?= js_pages('show_snatches', $_GET['torrentid'], $NumResults, $Page) ?></div>
    <? } ?>
    <div class="TableContainer">
        <table class="TableTorrentSnatchList Table">
            <tr class="Table-rowHeader">
                <td class="Table-cell"><?= t('server.torrents.user') ?></td>
                <td class="Table-cell"><?= t('server.torrents.time') ?></td>

                <td class="Table-cell"><?= t('server.torrents.user') ?></td>
                <td class="Table-cell"><?= t('server.torrents.time') ?></td>
            </tr>
            <tr>
                <?
                $i = 0;

                foreach ($Results as $ID => $Data) {
                    list($SnatcherID, $Timestamp) = array_values($Data);

                    if ($i % 2 == 0 && $i > 0) {
                ?>
            </tr>
            <tr class="Table-row">
            <?
                    }
            ?>
            <td class="Table-cell"><?= Users::format_username($SnatcherID, true, true, true, true) ?></td>
            <td class="Table-cell"><?= time_diff($Timestamp) ?></td>
        <?
                    $i++;
                }
        ?>
            </tr>
        </table>
    </div>
    <? if ($NumResults > 100) { ?>
        <div class="BodyNavLinks"><?= js_pages('show_snatches', $_GET['torrentid'], $NumResults, $Page) ?></div>
    <? } ?>
</div>