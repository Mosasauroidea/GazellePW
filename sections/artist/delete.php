<?

/************************************************************************
||------------|| Delete artist ||--------------------------------------||

This is a very powerful page - it deletes an artist, and all associated
requests and torrents. It is called when $_GET['action'] == 'delete'.

 ************************************************************************/

authorize();

$ArtistID = $_GET['artistid'];
if (!is_number($ArtistID) || empty($ArtistID)) {
    error(0);
}

if (!check_perms('site_delete_artist') || !check_perms('torrents_delete')) {
    error(403);
}

View::show_header(t('server.artist.artist_deleted'), '', 'PageArtistDelete');

$DB->query("
	SELECT Name
	FROM artists_group
	WHERE ArtistID = $ArtistID");
list($Name) = $DB->next_record();

$DB->query("
	SELECT tg.Name, tg.ID
	FROM torrents_group AS tg
		LEFT JOIN torrents_artists AS ta ON ta.GroupID = tg.ID
	WHERE ta.ArtistID = $ArtistID");
$Count = $DB->record_count();
if ($DB->has_results()) {
?>
    <div class="LayoutBody">
        <div class="remove-artist-failed">
            <?= t('server.artist.there_are_still_torrents_that_have') ?><a href="artist.php?id=<?= $ArtistID ?>" data-tooltip="<?= t('server.artist.view_artist') ?>" dir="ltr"><?= $Name ?></a><?= t('server.artist.as_an_artist') ?><br />
            <?= t('server.artist.please_remove_the_artist_from_these_torrents') ?><br />
            <div class="Box">
                <div class="Box-body">
                    <ul>
                        <?
                        while (list($GroupName, $GroupID) = $DB->next_record(MYSQLI_NUM, true)) {
                        ?>
                            <li>
                                <a href="torrents.php?id=<?= $GroupID ?>" data-tooltip="<?= t('server.artist.view_torrent_group') ?>" dir="ltr"><?= $GroupName ?></a>
                            </li>
                        <?
                        }
                        ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
<?
}

$DB->query("
	SELECT r.Title, r.ID
	FROM requests AS r
		LEFT JOIN requests_artists AS ra ON ra.RequestID = r.ID
	WHERE ra.ArtistID = $ArtistID");
$Count += $DB->record_count();
if ($DB->has_results()) {
?>
    <div class="LayoutBody">
        <div class="remove-artist-failed">
            <?= t('server.artist.there_are_still_requests_that_have') ?><a href="artist.php?id=<?= $ArtistID ?>" data-tooltip="<?= t('server.artist.view_artist') ?>" dir="ltr"><?= $Name ?></a><?= t('server.artist.as_an_artist') ?><br />
            <?= t('server.artist.please_remove_the_artist_from_these_requests') ?><br />
            <div class="Box">
                <div class="Box-body">
                    <ul>
                        <?
                        while (list($RequestName, $RequestID) = $DB->next_record(MYSQLI_NUM, true)) {
                        ?>
                            <li>
                                <a href="requests.php?action=view&amp;id=<?= $RequestID ?>" data-tooltip="<?= t('server.artist.view_request') ?>" dir="ltr"><?= $RequestName ?></a>
                            </li>
                        <?
                        }
                        ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
<?
}

if ($Count == 0) {
    Artists::delete_artist($ArtistID);
?>
    <div class="Box">
        <div class="thin Box-body">
            <?= t('server.artist.artist_deleted_before') ?><?= $Name ?><?= t('server.artist.artist_deleted_after') ?>
        </div>
    </div>
<?
}
View::show_footer(); ?>