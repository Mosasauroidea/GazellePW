<?

use Aws\NetworkFirewall\NetworkFirewallClient;

if (!check_perms('site_torrents_notify')) {
    error(403);
}
$TorrentPass =  $LoggedUser['torrent_pass'];
$OfficialRSS = [
    'feed_news' => t('server.user.rss_news'),
    'feed_blog' => t('server.user.rss_blog'),
    /*'feed_changelog'*/
    'torrents_all' => t('server.user.rss_torrents_all'),
    'torrents_free' => t('server.user.rss_torrents_free'),
    "torrents_bookmarks_t_$TorrentPass" => t('server.bookmarks.your_bookmarked_torrent_groups'),
];
View::show_header(t('server.user.manage_notifications'), 'jquery.validate,form_validate', 'PageUserNotifyEdit');
?>
<div class="LayoutBody">
    <div class="BodyHeader">
        <h2 class="BodyHeader-nav"><?= t('server.user.notify_me_of_all_new_torrents_with') ?></h2>
        <div class="BodyNavLinks">
            <a href="user.php?action=notify_edit" class="brackets"><?= t('server.user.create_new_torrent_notify') ?></a>
            <a href="user.php?action=notify" class="brackets"><?= t('server.user.new_torrent_notify_list') ?></a>
            <a href="torrents.php?action=notify" class="brackets"><?= t('server.user.view_notifications') ?></a>
        </div>
    </div>
    <div class="BodyContent">
        <div class="Group">
            <div class="Group-header">
                <div class="Group-headerTitle">
                    <?= t('server.user.official_rss') ?>
                </div>
            </div>
            <div class="Group-body">
                <table class="Table">
                    <tr class="Table-row">
                        <th class="Table-cellHeader Table-cellLeft">
                            <?= t('server.common.name') ?>
                        </th>
                        <th class="Table-cellHeader Table-cellLeft">
                            <?= t('server.user.rss_feed_url') ?>
                        </th>
                    </tr>
                    <? foreach ($OfficialRSS as $RSS => $Title) { ?>
                        <tr class="Table-row">
                            <td class="Table-cell Table-cellLeft">
                                <div>
                                    <?= $Title ?>
                                </div>
                            </td>
                            <td class="Table-cell Table-cellLeft">
                                <div class="RssTitle">
                                    <a target="_blank" data-tooltip="<?= t('server.user.rss_address') ?>" href="feeds.php?feed=<?= $RSS ?>&amp;user=<?= $LoggedUser['ID'] ?>&amp;auth=<?= $LoggedUser['RSS_Auth'] ?>&amp;passkey=<?= $LoggedUser['torrent_pass'] ?>&amp;authkey=<?= $LoggedUser['AuthKey'] ?>&amp;name=<?= urlencode($N['Label']) ?>">
                                        <?= icon('rss') ?></a>
                                </div>
                            </td>
                        </tr>
                    <? } ?>
                </table>
            </div>
        </div>
        <?
        $DB->query("
	SELECT
		ID,
		Label
	FROM users_notify_filters
	WHERE UserID=$LoggedUser[ID]");

        $Notifications = $DB->to_array('ID', MYSQLI_ASSOC);
        if (count($Notifications) > 0) {
        ?>
            <div class="Group">
                <div class="Group-header">
                    <div class="Group-headerTitle">
                        <?= t('server.user.new_torrent_notify_name') ?>
                    </div>
                </div>
                <div class="Group-body">
                    <table class="Table">
                        <tr class="Table-row">
                            <th class="Table-cellHeader Table-cellLeft">
                                <?= t('server.common.name') ?>
                            </th>
                            <th class="Table-cellHeader Table-cellLeft">
                                <?= t('server.user.rss_feed_url') ?>
                            </th>
                            <th class="Table-cellHeader Table-cellLeft">
                                <?= t('server.tools.operation') ?>
                            </th>
                        </tr>
                        <?
                        foreach ($Notifications as $Idx => $N) {
                            $Label = $N['Label'];
                            $ID = $N['ID'];
                        ?>
                            <tr class="Table-row">
                                <td class="Table-cell Table-cellLeft">
                                    <div>
                                        <?= $Label ?>
                                    </div>
                                </td>
                                <td class="Table-cell Table-cellLeft">
                                    <div class="RssTitle">
                                        <a target="_blank" data-tooltip="<?= t('server.user.rss_address') ?>" href="feeds.php?feed=torrents_notify_<?= $ID ?>_<?= $LoggedUser['torrent_pass'] ?>&amp;user=<?= $LoggedUser['ID'] ?>&amp;auth=<?= $LoggedUser['RSS_Auth'] ?>&amp;passkey=<?= $LoggedUser['torrent_pass'] ?>&amp;authkey=<?= $LoggedUser['AuthKey'] ?>&amp;name=<?= urlencode($N['Label']) ?>">
                                            <?= icon('rss') ?></a>
                                    </div>
                                </td>
                                <td class="Table-cell Table-cellLeft">
                                    <a href="user.php?action=notify_edit&id=<?= $ID ?>&amp;auth=<?= $LoggedUser['AuthKey'] ?>" class="Button"><?= t('server.common.edit') ?></a>
                                    <a href="user.php?action=notify_delete&id=<?= $ID ?>&amp;auth=<?= $LoggedUser['AuthKey'] ?>" onclick="return confirm('<?= t('server.user.are_you_sure_delete_notification_filter') ?>')" class="Button"><?= t('server.common.delete') ?></a>
                                </td>
                            </tr>
                        <?
                        }
                        ?>
                    </table>
                </div>
            </div>
        <? } ?>
    </div>
</div>
<? View::show_footer(); ?>