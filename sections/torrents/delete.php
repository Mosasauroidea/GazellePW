<?
include(CONFIG['SERVER_ROOT'] . '/classes/torrenttable.class.php');

$TorrentID = $_GET['torrentid'];
if (!$TorrentID || !is_number($TorrentID)) {
    error(404);
}


$DB->query("
	SELECT
		t.UserID,
		t.Time,
		COUNT(x.uid)
	FROM torrents AS t
		LEFT JOIN xbt_snatched AS x ON x.fid = t.ID
	WHERE t.ID = $TorrentID
	GROUP BY t.UserID");

if (!$DB->has_results()) {
    error(t('server.torrents.torrent_already_deleted'));
}

if ($Cache->get_value('torrent_' . $TorrentID . '_lock')) {
    error(t('server.torrents.torrent_cannot_be_deleted_because_the_upload_process_is_not_completed_yet'));
}


list($UserID, $Time, $Snatches) = $DB->next_record();


if ($LoggedUser['ID'] != $UserID && !check_perms('torrents_delete')) {
    error(403);
}

if (isset($_SESSION['logged_user']['multi_delete']) && $_SESSION['logged_user']['multi_delete'] >= 3 && !check_perms('torrents_delete_fast')) {
    error(t('server.torrents.you_have_recently_deleted_3_torrents'));
}

if (time_ago($Time) > 3600 * 24 * 7 && !check_perms('torrents_delete')) { // Should this be torrents_delete or torrents_delete_fast?
    error(t('server.torrents.you_can_no_longer_delete_this_torrent_as_it_has_been_uploaded_for_over_a_week'));
}

if ($Snatches > 4 && !check_perms('torrents_delete')) { // Should this be torrents_delete or torrents_delete_fast?
    error(t('server.torrents.you_can_no_longer_delete_this_torrent_as_it_has_been_snatched_by_5_or_more_users'));
}


View::show_header(t('server.torrents.delete_torrent'), 'reportsv2', 'PageTorrentDelete');
$TorrentDetail = Torrents::get_torrent($TorrentID);
$HeadTitle = Torrents::torrent_simple_view($TorrentDetail['Group'], $TorrentDetail, true, [
    'SettingTorrentTitle' => G::$LoggedUser['SettingTorrentTitle'],
]);

?>
<div class="LayoutBody">
    <div class="BodyHeader">
        <div class="BodyHeader-nav"><?= t('server.common.delete') ?></div>
        <div class="BodyHeader-subNav"><?= $HeadTitle ?></div>
    </div>
    <form class="delete_form" name="torrent" action="torrents.php" method="post">
        <input type="hidden" name="action" value="takedelete" />
        <input type="hidden" name="auth" value="<?= $LoggedUser['AuthKey'] ?>" />
        <input type="hidden" name="torrentid" value="<?= $TorrentID ?>" />
        <div class="Form-rowList" id="torrent_delete_reason" variant="header">
            <div class="Form-rowHeader"><?= t('server.torrents.delete_torrent') ?></div>
            <div class="Form-row">
                <p><strong class="u-colorWarning"><?= t('server.torrents.delete_torrent_note') ?></strong></p>
            </div>
            <div class="Form-row">
                <div class="Form-label"><?= t('server.torrents.reason') ?>: </div>
                <div class="Form-inputs">
                    <select class="Input" name="reason">
                        <option class="Select-option" value="Dead"><?= t('server.torrents.dead') ?></option>
                        <option class="Select-option" value="Dupe"><?= t('server.torrents.dupe') ?></option>
                        <option class="Select-option" value="Trumped"><?= t('server.torrents.trumped') ?></option>
                        <option class="Select-option" value="Rules Broken"><?= t('server.torrents.rules_broken') ?></option>
                        <option class="Select-option" value="" selected="selected"><?= t('server.torrents.other') ?></option>
                    </select>
                </div>
            </div>
            <div class="Form-row">
                <div class="Form-label"><?= t('server.torrents.extra_info') ?>: </div>
                <div class="Form-inputs">
                    <input class="Input" type="text" name="extra" size="30" placeholder="<?= t('server.torrents.extra_info_placeholder') ?>" />
                </div>
            </div>
            <div class="Form-row">
                <input class="Button" value="<?= t('server.common.delete') ?>" type="submit" />
            </div>
        </div>
    </form>
</div>
<?
View::show_footer(); ?>