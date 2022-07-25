<?

if (!check_perms('users_mod')) {
    error(403);
}
if (is_number($_GET['id'])) {
    $ID = $_GET['id'];
    $Event = SiteHistory::get_event($ID);
}

if ($ID) {
    $Title = t('server.sitehistory.edit');
} else {
    $Title = t('server.sitehistory.create');
}
View::show_header($Title, '', 'PageSiteHistoryEdit');

?>

<div class="header">
    <? if ($ID) { ?>
        <h2><?= t('server.sitehistory.edit_event') ?></h2>
    <?  } else { ?>
        <h2><?= t('server.sitehistory.create_new_event') ?></h2>
    <?  } ?>
</div>

<?
SiteHistoryView::render_edit_form($Event);
View::show_footer([], "sitehistory/index.js");
