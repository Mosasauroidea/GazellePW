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
		SQL_CALC_FOUND_ROWS
		xu.uid,
		t.Size,
		xu.active,
		xu.connectable,
		xu.uploaded,
		xu.remaining,
		xu.useragent
	FROM xbt_files_users AS xu
		LEFT JOIN users_main AS um ON um.ID = xu.uid
		JOIN torrents AS t ON t.ID = xu.fid
	WHERE xu.fid = '$TorrentID'
		AND um.Visible = '1'
	ORDER BY xu.uid = '$LoggedUser[ID]' DESC, xu.uploaded DESC
	LIMIT $Limit");
$DB->query('SELECT FOUND_ROWS()');
list($NumResults) = $DB->next_record();
$DB->set_query_id($Result);

?>
<div class="TorrentDetail-row is-peerList is-block">
    <strong class="TorrentDetailPeerList-title" id="peerlist_box_title"><?= t('server.torrents.peer_list') ?>:</strong>
    <? if ($NumResults > 100) { ?>
        <div class="BodyNavLinks"><?= js_pages('show_peers', $_GET['torrentid'], $NumResults, $Page) ?></div>
    <? } ?>
    <div class="TableContainer">
        <table class="TableTorrentPeerList Table">
            <tr class="Table-rowHeader">
                <td class="Table-cell"><?= t('server.torrents.user') ?></td>
                <td class="Table-cell"><?= t('server.torrents.active') ?></td>
                <td class="Table-cell"><?= t('server.torrents.connectable') ?></td>
                <td class="Table-cell Table-cellRight"><?= t('server.torrents.up_this_session') ?></td>
                <td class="Table-cell Table-cellRight">%</td>
                <td class="Table-cell"><?= t('server.torrents.client') ?></td>
            </tr>
            <?
            while (list($PeerUserID, $Size, $Active, $Connectable, $Uploaded, $Remaining, $UserAgent) = $DB->next_record()) {
            ?>
                <tr class="Table-row">
                    <?
                    if (check_perms('users_mod') || $PeerUserID == G::$LoggedUser['ID']) {
                    ?>
                        <td class="Table-cell"><?= Users::format_username($PeerUserID, false, false, false) ?></td>
                    <?  } else {
                    ?>
                        <td class="Table-cell"><?= t('server.torrents.peer') ?></td>
                    <?  }
                    ?>
                    <td class="Table-cell"><?= ($Active) ? '<span class="u-colorSuccess">Yes</span>' : '<span class="u-colorWarning">No</span>' ?></td>
                    <td class="Table-cell"><?= ($Connectable) ? '<span class="u-colorSuccess">Yes</span>' : '<span class="u-colorWarning">No</span>' ?></td>
                    <td class="Table-cell Table-cellRight"><?= Format::get_size($Uploaded) ?></td>
                    <td class="Table-cell Table-cellRight"><?= number_format(($Size - $Remaining) / $Size * 100, 2) ?></td>
                    <td class="Table-cell"><?= display_str($UserAgent) ?></td>
                </tr>
            <?
            }
            ?>
        </table>
    </div>
    <? if ($NumResults > 100) { ?>
        <div class="BodyNavLinks"><?= js_pages('show_peers', $_GET['torrentid'], $NumResults, $Page) ?></div>
    <? } ?>
</div>