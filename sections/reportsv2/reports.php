<?
/*
 * This is the outline page for auto reports. It calls the AJAX functions
 * that actually populate the page and shows the proper header and footer.
 * The important function is AddMore().
 */
if (!check_perms('admin_reports')) {
    error(403);
}

View::show_header(t('server.reportsv2.reports_v2'), 'reportsv2', 'PageReportV2Home');
?>
<div class="LayoutBody">
    <div class="BodyHeader">
        <h2 class="BodyHeader-nav"><?= t('server.reportsv2.new_reports_auto_assigned') ?></h2>
        <? include('header.php'); ?>
    </div>
    <div class="BodyContent">
        <div>
            <span data-tooltip="<?= t('server.reportsv2.multi_resolve_btn_title') ?>">
                <input class="Button" type="button" onclick="MultiResolve();" value="<?= t('server.reportsv2.multi_resolve') ?>" /></span>
            <span data-tooltip="<?= t('server.reportsv2.unclaim_all_btn_title') ?>">
                <input class="Button" type="button" onclick="GiveBack();" value="<?= t('server.reportsv2.unclaim_all') ?>" /></span>
            <input class="Input is-small" type="text" name="repop_amount" id="repop_amount" size="2" value="10" />
            <span data-tooltip="<?= t('server.reportsv2.dynamic_title') ?>">
                <input type="checkbox" checked="checked" id="dynamic" /> <label for="dynamic"><?= t('server.reportsv2.dynamic') ?></label></span>

            <input class="Button" type="button" onclick="AddMore();" value="Add more" />

        </div>
    </div>

    <div id="all_reports" style="margin-left: auto; margin-right: auto;">
    </div>
</div>

<?
View::show_footer();
?>