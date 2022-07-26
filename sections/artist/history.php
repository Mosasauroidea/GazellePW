<?
if (!is_number($_GET['artistid'])) {
    error(0);
}
$ArtistID = (int)$_GET['artistid'];

$DB->query("
	SELECT Name
	FROM artists_group
	WHERE ArtistID = $ArtistID");
if (!$DB->has_results()) {
    error(404);
}
list($Name) = $DB->next_record();

View::show_header(t('server.artist.revision_history', ['Values' => [$Name]]), '', 'PageArtistHistory');
?>
<div class="LayoutBody">
    <div class="BodyHeader">
        <h2 class="BodyHeader-nav"><?= t('server.artist.revision_history', ['Values' => ["<a href='artist.php?id=<?= $ArtistID ?>'>$Name</a>"]]) ?></h2>
    </div>
    <?
    RevisionHistoryView::render_revision_history(RevisionHistory::get_revision_history('artists', $ArtistID), "artist.php?id=$ArtistID");
    ?>
</div>
<?
View::show_footer();
