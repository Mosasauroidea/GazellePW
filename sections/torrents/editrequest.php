<?php

if (empty($_GET['groupid']) || !is_numeric($_GET['groupid'])) {
    error(404);
}
$GroupID = intval($_GET['groupid']);

include(CONFIG['SERVER_ROOT'] . '/sections/torrents/functions.php');
$TorrentCache = Torrents::get_group($GroupID, true);

$TorrentDetails = $TorrentCache;
$TorrentList = $TorrentCache['Torrents'];

$Name = Torrents::group_name($TorrentDetails);
View::show_header(t('server.torrents.request_an_edit'), '', '', 'PageTorrentEditRequest');

?>
<div class="LayoutBody">
    <div class="BodyHeader">
        <h2 class="BodyHeader-nav"><?= t('server.torrents.request_an_edit') ?></h2>
        <div class="BodyHeader-subNav"><?= $Name ?></div>
    </div>
    <div class="Box">
        <div class="Box-header">
            <?= t('server.common.rules') ?>
        </div>
        <div class="Box-body">
            <?= t('server.torrents.you_are_req_note') ?>
        </div>
    </div>
    <form class="Form-rowList" action="torrents.php" method="POST">
        <input type="hidden" name="action" value="takeeditrequest" />
        <input type="hidden" name="groupid" value="<?= $GroupID ?>" />
        <input type="hidden" name="auth" value="<?= G::$LoggedUser['AuthKey'] ?>" />
        <div>
            <? new TEXTAREA_PREVIEW('edit_details', 'edit_details'); ?>
        </div>
        <div class="Form-row">
            <input class="Button" type="submit" value="<?= t('server.common.submit') ?>" />
        </div>
    </form>
</div>

<?php
View::show_footer();
