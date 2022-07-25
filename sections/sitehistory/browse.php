<?
define('DEFAULT_LIMIT', 10);

$Limit = DEFAULT_LIMIT;
if (is_number($_GET['month'])) {
    $Month = $_GET['month'];
    $Limit = null;
}
if (is_number($_GET['year'])) {
    $Year = $_GET['year'];
    $Limit = null;
}
if (!empty($_GET['title'])) {
    $Title = $_GET['title'];
    $Limit = null;
}
if (!empty($_GET['category'])) {
    $Category = $_GET['category'];
    $Limit = null;
}
if (!empty($_GET['subcategory'])) {
    $SubCategory = $_GET['subcategory'];
    $Limit = null;
}
if (!empty($_GET['tags'])) {
    $Tags = $_GET['tags'];
    $Limit = null;
}
$Events = SiteHistory::get_events($Month, $Year, $Title, $Category, $SubCategory, $Tags, $Limit);
$Months = SiteHistory::get_months();
View::show_header(t('server.sitehistory.site_history'), '', 'PageSiteHistoryHome');
?>
<div class="LayoutBody">
    <div class="BodyHeader">
        <h2 class="BodyHeader-nav"><a href="sitehistory.php"><?= t('server.sitehistory.site_history') ?></a> <?= $Month && $Year ? date("- F, Y", mktime(0, 0, 0, $Month, 1, $Year)) : '' ?></h2>
        <?
        SiteHistoryView::render_linkbox();
        ?>
    </div>
    <div class="LayoutMainSidebar">
        <div class="Sidebar LayoutMainSidebar-sidebar">
            <?
            SiteHistoryView::render_search();
            SiteHistoryView::render_months($Months);
            ?>
        </div>
        <div class="LayoutMainSidebar-main">
            <?
            SiteHistoryView::render_events($Events);
            ?>
        </div>
    </div>
</div>
<?
View::show_footer();
