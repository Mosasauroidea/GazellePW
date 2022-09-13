<?
if (!isset($_GET['id']) || !is_number($_GET['id']) || !isset($_GET['torrentid']) || !is_number($_GET['torrentid'])) {
    error(0);
}
$GroupID = $_GET['id'];
$TorrentID = $_GET['torrentid'];
$Torrent = Torrents::get_torrent($TorrentID);
$Title = Torrents::torrent_simple_view($Torrent['Group'], $Torrent);

View::show_header(t('server.torrents.mass_pm'), 'upload', 'PageTorrentMassPM');

if (!check_perms('site_moderate_requests')) {
    error(403);
}
?>
<div class="LayoutBody">
    <div class="BodyHeader">
        <div class="BodyHeader-nav">
            <?= t('server.torrents.mass_pm') ?>
        </div>
        <div class="BodyHeader-subNav"><?= $Title ?></div>
    </div>
    <form class="send_form" name="mass_message" action="torrents.php" method="post">
        <input type="hidden" name="action" value="takemasspm" />
        <input type="hidden" name="auth" value="<?= $LoggedUser['AuthKey'] ?>" />
        <input type="hidden" name="torrentid" value="<?= $TorrentID ?>" />
        <input type="hidden" name="groupid" value="<?= $GroupID ?>" />
        <table class="Form-rowList" variant="header">
            <tr class="Form-rowHeader">
                <td class="Form-title">
                    <?= t('server.tools.send_a_mass_pm') ?>
                </td>
            </tr>
            <tr class="Form-row">
                <td class="Form-label"><?= t('server.inbox.subject') ?></td>
                <td class="Form-inputs">
                    <input class="Input" type="text" name="subject" value="" size="60" />
                </td>
            </tr>
            <tr class="Form-row">
                <td class="Form-label"><?= t('server.inbox.message') ?></td>
                <td class="Form-items">
                    <textarea class="Input" name="message" id="message" cols="60" rows="8"></textarea>
                </td>
            </tr>
            <tr class="Form-row">
                <td colspan="2" class="center">
                    <input class="Button" type="submit" value="<?= t('server.inbox.send_message') ?>" />
                </td>
            </tr>
        </table>
    </form>
</div>
<? View::show_footer(); ?>