<?
$FeaturedMovie = $Cache->get_value('album_of_the_month');
if ($FeaturedMovie === false) {
    $DB->query('
		SELECT
			fa.GroupID,
			tg.Name,
			tg.WikiImage,
			fa.ThreadID,
			fa.Title,
            tg.SubName,
            tg.Year,
            tg.ID
		FROM featured_albums AS fa
			JOIN torrents_group AS tg ON tg.ID = fa.GroupID
		WHERE Ended = 0 AND type = 0');
    $FeaturedMovie = $DB->next_record();
    $Cache->cache_value('album_of_the_month', $FeaturedMovie, 0);
}
if (is_number($FeaturedMovie['GroupID'])) {
    $Artists = Artists::get_artist($FeaturedMovie['GroupID']);
?>
    <div class="SidebarItemPoster SidebarItem Box">
        <div class="SidebarItem-header Box-header">
            <span class="SidebarItem-headerTitle">
                <?= Lang::get('index', 'featured_item') ?>
            </span>
            <div class="SidebarItem-headerActions">
                <a href="forums.php?action=viewthread&amp;threadid=<?= $FeaturedMovie['ThreadID'] ?>"><?= Lang::get('index', 'discuss') ?></a>
            </div>
        </div>
        <div class="SidebarItem-body Box-body">
            <a href="torrents.php?id=<?= $FeaturedMovie['GroupID'] ?>" data-tooltip="<?= Torrents::group_name($FeaturedMovie, false) ?>">
                <img class="SidebarItemPoster-image" src="<?= ImageTools::process($FeaturedMovie['WikiImage'], true) ?>" alt="<?= Torrents::group_name($FeaturedMovie, false) ?>" />
            </a>
        </div>
    </div>
<? } ?>