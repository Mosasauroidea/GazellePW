<?

use Aws\NetworkFirewall\NetworkFirewallClient;

if (!check_perms('site_torrents_notify')) {
    error(403);
}
View::show_header(t('server.user.manage_notifications'), 'jquery.validate,form_validate', 'PageUserNotifyEdit');
?>
<div class="LayoutBody">
    <div class="BodyHeader">
        <h2 class="BodyHeader-nav"><?= t('server.user.notify_me_of_all_new_torrents_with') ?></h2>
        <div class="BodyNavLinks">
            <a href="user.php?action=notify" class="brackets"><?= t('server.user.new_torrent_notify_list') ?></a>
            <a href="torrents.php?action=notify" class="brackets"><?= t('server.user.view_notifications') ?></a>
            <a href="user.php?action=notify_edit" class="brackets"><?= t('server.user.create_new_torrent_notify') ?></a>
        </div>
    </div>
    <div class="BodyContent">
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
            <table class="Table">
                <tr class="Table-row">
                    <th class="Table-cellHeader Table-cellLeft">
                        <?= t('server.user.new_torrent_notify_name') ?>
                    </th>
                    <th class="Table-cellHeader Table-cellRight">
                        <?= t('server.tools.operation') ?>
                    </th>
                </tr>
                <?
                foreach ($Notifications as $Idx => $N) {
                    $Label = $N['Label'];
                    $ID = $N['ID'];
                ?>
                    <tr class="Table-row">
                        <td class="Tabel-cell Table-cellLeft">
                            <div class="RssTitle">
                                <a target="_blank" data-tooltip="<?= t('server.user.rss_address') ?>" href="feeds.php?feed=torrents_notify_<?= $ID ?>_<?= $LoggedUser['torrent_pass'] ?>&amp;user=<?= $LoggedUser['ID'] ?>&amp;auth=<?= $LoggedUser['RSS_Auth'] ?>&amp;passkey=<?= $LoggedUser['torrent_pass'] ?>&amp;authkey=<?= $LoggedUser['AuthKey'] ?>&amp;name=<?= urlencode($N['Label']) ?>">
                                    <?= icon('rss') ?></a>
                                <?= $Label ?>
                            </div>
                        </td>
                        <td class="Tabel-cell Table-cellRight">
                            <a href="user.php?action=notify_edit&id=<?= $ID ?>&amp;auth=<?= $LoggedUser['AuthKey'] ?>" class="Button"><?= t('server.common.edit') ?></a>
                            <a href="user.php?action=notify_delete&id=<?= $ID ?>&amp;auth=<?= $LoggedUser['AuthKey'] ?>" onclick="return confirm('<?= t('server.user.are_you_sure_delete_notification_filter') ?>')" class="Button"><?= t('server.common.delete') ?></a>
                        </td>
                    </tr>
                <?
                }
                ?>
            </table>
        <? } ?>
    </div>
</div>
<? View::show_footer(); ?>