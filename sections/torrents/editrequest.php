<?php

if (empty($_GET['groupid']) || !is_numeric($_GET['groupid'])) {
    error(404);
}
$GroupID = intval($_GET['groupid']);

include(SERVER_ROOT . '/sections/torrents/functions.php');
$TorrentCache = Torrents::get_group($GroupID, true);

$TorrentDetails = $TorrentCache;
$TorrentList = $TorrentCache['Torrents'];

$Name = Torrents::group_name($TorrentDetails);

$Title = page_title_conn([Lang::get('torrents', 'request_an_edit'), $Name]);

View::show_header(Lang::get('torrents', 'request_an_edit'), '', '', 'PageTorrentEditRequest');

?>
<div class="LayoutBody">
    <div class="BodyHeader">
        <h2 class="BodyHeader-nav"><?= $Title ?></h2>
    </div>
    <div class="Box">
        <div class="Box-body">
            <?= Lang::get('torrents', 'you_are_req_note') ?>
        </div>
    </div>
    <div class="Form-rowList" variant="header">
        <div class="Form-rowHeader"><strong class="u-colorWarning"><?= Lang::get('torrents', 'edit_details') ?></strong></div>
        <form action="torrents.php" method="POST">
            <input type="hidden" name="action" value="takeeditrequest" />
            <input type="hidden" name="groupid" value="<?= $GroupID ?>" />
            <input type="hidden" name="auth" value="<?= G::$LoggedUser['AuthKey'] ?>" />
            <div>
                <? new TEXTAREA_PREVIEW('edit_details', 'edit_details'); ?>
            </div>
            <div class="Form-row">
                <input class="Button" type="submit" value="<?= Lang::get('global', 'submit') ?>" />
            </div>
        </form>
    </div>
</div>

<?php
View::show_footer();
