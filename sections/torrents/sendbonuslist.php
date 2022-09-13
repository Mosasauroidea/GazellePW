<?php
if (!isset($_GET['torrentid']) || !is_number($_GET['torrentid'])) {
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
                FromUserID,
                sum(Bonus) Count
			FROM torrents_send_bonus
            WHERE TorrentID = '$TorrentID'
            group by FromUserID
            ORDER BY Count DESC
			LIMIT $Limit");
$Results = $DB->to_array('FromUserID', MYSQLI_ASSOC);

$DB->query('SELECT FOUND_ROWS()');
list($NumResults) = $DB->next_record();

?>
<div class="TorrentDetail-row is-sendbonusList is-block">
    <strong class="TorrentDetailSendBonusList-title" id="sendbonuslist_box_title"><?= t('server.torrents.list_of_giver_title') ?>:</strong>

    <? if ($NumResults > 100) { ?>
        <div class="BodyNavLinks"><?= js_pages('show_snatches', $_GET['torrentid'], $NumResults, $Page) ?></div>
    <? } ?>
    <div class="TableContainer">
        <table class="TableTorrentSeedBonus Table">
            <tr class="Table-rowHeader">
                <td class="Table-cell"><?= t('server.torrents.user') ?></td>
                <td class="Table-cell"><?= t('server.torrents.gift_points_pre_tax') ?></td>

                <td class="Table-cell"><?= t('server.torrents.user') ?></td>
                <td class="Table-cell"><?= t('server.torrents.gift_points_pre_tax') ?></td>
            </tr>
            <tr>
                <?
                $i = 0;

                foreach ($Results as $ID => $Data) {
                    list($GiverID, $Bonus) = array_values($Data);
                    if (!$GiverID && !$Bonus) continue;
                    if ($i % 2 == 0 && $i > 0) {
                ?>
            </tr>
            <tr class="Table-row">
            <?
                    }
            ?>
            <td class="Table-cell"><?= Users::format_username($GiverID, true, true, true, true) ?></td>
            <td class="Table-cell"><?= $Bonus ?></td>
        <?
                    $i++;
                }
        ?>
            </tr>
        </table>
    </div>
    <? if ($NumResults > 100) { ?>
        <div class="BodyNavLinks"><?= js_pages('show_giver', $_GET['torrentid'], $NumResults, $Page) ?></div>
    <? } ?>
</div>