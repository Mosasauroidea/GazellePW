<?
authorize();

if (!check_perms('torrents_edit')) {
  error(403);
}
if (empty($_POST['newartistid'])) {
  error('Please enter a valid artist ID number or a valid artist name.');
}
$ArtistID = (int)$_POST['artistid'];
$NewArtistID = (int)$_POST['newartistid'];


if (!is_number($ArtistID) || !$ArtistID) {
  error('Please select a valid artist to change.');
}

$DB->query("
	SELECT Name, SubName
	FROM artists_group
	WHERE ArtistID = $ArtistID
	LIMIT 1");
if (!(list($ArtistName, $ArtistSubName) = $DB->next_record(MYSQLI_NUM, false))) {
  error('An error has occurred.');
}

// Make sure that's a real artist ID number, and grab the name
$DB->query("
SELECT Name, SubName
FROM artists_group
WHERE ArtistID = $NewArtistID
LIMIT 1");
if (!(list($NewArtistName, $NewArtistSubName) = $DB->next_record())) {
  error('Please enter a valid artist ID number.');
}

if ($ArtistID == $NewArtistID) {
  error('You cannot merge an artist with itself.');
}
if (isset($_POST['confirm'])) {
  // Get the information for the cache update
  $DB->query("
		SELECT DISTINCT GroupID
		FROM torrents_artists
		WHERE ArtistID = $ArtistID");
  $Groups = $DB->collect('GroupID');
  $DB->query("
		SELECT DISTINCT RequestID
		FROM requests_artists
		WHERE ArtistID = $ArtistID");
  $Requests = $DB->collect('RequestID');
  $DB->query("
		SELECT DISTINCT UserID
		FROM bookmarks_artists
		WHERE ArtistID = $ArtistID");
  $BookmarkUsers = $DB->collect('UserID');
  $DB->query("
		SELECT DISTINCT ct.CollageID
		FROM collages_torrents AS ct
			JOIN torrents_artists AS ta ON ta.GroupID = ct.GroupID
		WHERE ta.ArtistID = $ArtistID");
  $Collages = $DB->collect('CollageID');

  // And the info to avoid double-listing an artist if it and the target are on the same group
  $DB->query("
		SELECT DISTINCT GroupID
		FROM torrents_artists
		WHERE ArtistID = $NewArtistID");
  $NewArtistGroups = $DB->collect('GroupID');
  $NewArtistGroups[] = '0';
  $NewArtistGroups = implode(',', $NewArtistGroups);

  $DB->query("
		SELECT DISTINCT RequestID
		FROM requests_artists
		WHERE ArtistID = $NewArtistID");
  $NewArtistRequests = $DB->collect('RequestID');
  $NewArtistRequests[] = '0';
  $NewArtistRequests = implode(',', $NewArtistRequests);

  $DB->query("
		SELECT DISTINCT UserID
		FROM bookmarks_artists
		WHERE ArtistID = $NewArtistID");
  $NewArtistBookmarks = $DB->collect('UserID');
  $NewArtistBookmarks[] = '0';
  $NewArtistBookmarks = implode(',', $NewArtistBookmarks);

  // Merge all of this artist's aliases onto the new artist
  $DB->query("
		UPDATE artists_alias
		SET ArtistID = $NewArtistID
		WHERE ArtistID = $ArtistID");

  // Update the torrent groups, requests, and bookmarks
  $DB->query("
		UPDATE IGNORE torrents_artists
		SET ArtistID = $NewArtistID
		WHERE ArtistID = $ArtistID
			AND GroupID NOT IN ($NewArtistGroups)");
  $DB->query("
		DELETE FROM torrents_artists
		WHERE ArtistID = $ArtistID");
  $DB->query("
		UPDATE IGNORE requests_artists
		SET ArtistID = $NewArtistID
		WHERE ArtistID = $ArtistID
			AND RequestID NOT IN ($NewArtistRequests)");
  $DB->query("
		DELETE FROM requests_artists
		WHERE ArtistID = $ArtistID");
  $DB->query("
		UPDATE IGNORE bookmarks_artists
		SET ArtistID = $NewArtistID
		WHERE ArtistID = $ArtistID
			AND UserID NOT IN ($NewArtistBookmarks)");
  $DB->query("
		DELETE FROM bookmarks_artists
		WHERE ArtistID = $ArtistID");

  // Cache clearing
  if (!empty($Groups)) {
    foreach ($Groups as $GroupID) {
      $Cache->delete_value("groups_artists_$GroupID");
      Torrents::update_hash($GroupID);
    }
  }
  if (!empty($Requests)) {
    foreach ($Requests as $RequestID) {
      $Cache->delete_value("request_artists_$RequestID");
      Requests::update_sphinx_requests($RequestID);
    }
  }
  if (!empty($BookmarkUsers)) {
    foreach ($BookmarkUsers as $UserID) {
      $Cache->delete_value("notify_artists_$UserID");
    }
  }
  if (!empty($Collages)) {
    foreach ($Collages as $CollageID) {
      $Cache->delete_value("collage_$CollageID");
    }
  }

  $Cache->delete_value("artist_$ArtistID");
  $Cache->delete_value("artist_$NewArtistID");
  $Cache->delete_value("artist_groups_$ArtistID");
  $Cache->delete_value("artist_groups_$NewArtistID");

  // Delete the old artist
  $DB->query("
		DELETE FROM artists_group
		WHERE ArtistID = $ArtistID");

  Misc::write_log("The artist $ArtistID ($ArtistName) was made into a alias of artist $NewArtistID ($NewArtistName) by user " . $LoggedUser['ID'] . " (" . $LoggedUser['Username'] . ')');

  header("Location: artist.php?action=edit&artistid=$NewArtistID");
} else {
  View::show_header(t('server.artist.merging_artists'), 'PageChangeArtistId');
?>
  <div class="LayoutBody">
    <div class="BodyHeader">
      <h2 class="BodyHeader-nav"><?= page_title_conn([t('server.artist.confirm_merge'), Artists::display_artist(['Name' => $ArtistName, 'SubName' => $ArtistSubName])]); ?></h2>
    </div>
    <div class="BodyContent">
      <form class="merge_form" name="artist" action="artist.php" method="post">
        <input type="hidden" name="action" value="change_artistid" />
        <input type="hidden" name="auth" value="<?= $LoggedUser['AuthKey'] ?>" />
        <input type="hidden" name="artistid" value="<?= $ArtistID ?>" />
        <input type="hidden" name="newartistid" value="<?= $NewArtistID ?>" />
        <input type="hidden" name="confirm" value="1" />
        <div>
          <p id="confirm_merge_note">
            <?
            $ArtistNameDisplay = display_str(Artists::display_artist(['Name' => $ArtistName, 'SubName' => $ArtistSubName], false));
            $NewArtistNameDisplay = display_str(Artists::display_artist(['Name' => $NewArtistName, 'SubName' => $NewArtistSubName], false));
            ?>
            <?= t('server.artist.confirm_merge_body', ['Values' => [
              "<a href='artist.php?id=${ArtistID}'>${ArtistNameDisplay} (${ArtistID})</a>",
              "<a href='artist.php?id=${NewArtistID}'>${NewArtistNameDisplay} (${NewArtistID})</a>",
            ]]) ?>
          </p>
          <input class="Button" type="submit" value="Confirm" />
        </div>
      </form>
    </div>
  </div>
<?
  View::show_footer();
}
?>