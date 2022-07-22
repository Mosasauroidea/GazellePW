<?
if (!isset($_GET['id']) || !is_number($_GET['id']) || !isset($_GET['torrentid']) || !is_number($_GET['torrentid'])) {
    error(0);
}
$GroupID = $_GET['id'];
$TorrentID = $_GET['torrentid'];
$Torrent = Torrents::get_torrent($TorrentID);
$Title = Torrents::torrent_name($Torrent);

View::show_header('Edit torrent', 'upload', 'PageTorrentMassPM');

if (!check_perms('site_moderate_requests')) {
    error(403);
}
?>
<div class="LayoutBody">
    <div class="header">
        <h3><?= $Title ?></h3>
        <h3><?= Lang::get('torrents.mass_pm') ?></h3>
    </div>
    <form class="send_form" name="mass_message" action="torrents.php" method="post">
        <input type="hidden" name="action" value="takemasspm" />
        <input type="hidden" name="auth" value="<?= $LoggedUser['AuthKey'] ?>" />
        <input type="hidden" name="torrentid" value="<?= $TorrentID ?>" />
        <input type="hidden" name="groupid" value="<?= $GroupID ?>" />
        <table class="layout">
            <tr>
                <td class="label">Subject</td>
                <td>
                    <input class="Input" type="text" name="subject" value="" size="60" />
                </td>
            </tr>
            <tr>
                <td class="label">Message</td>
                <td>
                    <textarea class="Input" name="message" id="message" cols="60" rows="8"></textarea>
                </td>
            </tr>
            <tr>
                <td colspan="2" class="center">
                    <input class="Button" type="submit" value="Send Mass PM" />
                </td>
            </tr>
        </table>
    </form>
</div>
<? View::show_footer(); ?>