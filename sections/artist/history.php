<?
if (!is_number($_GET['artistid'])) {
    error(0);
}
$ArtistID = (int)$_GET['artistid'];
$Artist = Artists::get_artist_by_id($ArtistID);
$Name = Artists::display_artist($Artist);

View::show_header(t('server.artist.revision_history'), '', 'PageArtistHistory');
?>
<div class="LayoutBody">
    <div class="BodyHeader">
        <div class="BodyHeader-nav"><?= t('server.artist.revision_history') ?></div>
        <div class="BodyHeader-subNav"><?= $Name ?></div>
    </div>
    <?
    RevisionHistoryView::render_revision_history(RevisionHistory::get_revision_history('artists', $ArtistID), "artist.php?id=$ArtistID");
    ?>
</div>
<?
View::show_footer();
