<?

/*********************************************************************\
The page that handles the backend of the 'edit artist' function.
\*********************************************************************/

authorize();

if (!$_REQUEST['artistid'] || !is_number($_REQUEST['artistid'])) {
    error(404);
}

if (!check_perms('site_edit_wiki')) {
    error(403);
}

// Variables for database input
$UserID = $LoggedUser['ID'];
$ArtistID = $_REQUEST['artistid'];

if ($_GET['action'] === 'revert') { // if we're reverting to a previous revision
    authorize();
    $RevisionID = $_GET['revisionid'];
    if (!is_number($RevisionID)) {
        error(0);
    }
} else { // with edit, the variables are passed with POST
    $Body = db_string($_POST['body']);
    $Summary = db_string($_POST['summary']);
    $Image = db_string($_POST['image']);
    $IMDBID = db_string($_POST['imdb_id']);
    $CName = db_string($_POST['cname']);
    ImageTools::blacklisted($Image);
    // Trickery
    if (!preg_match("/^" . IMAGE_REGEX . "$/i", $Image)) {
        $Image = '';
    }
}

// Insert revision
if (!$RevisionID) { // edit
    $DB->query("
		select Body, Image, IMDBID, ChineseName, w.Birthday, w.PlaceOfBirth from wiki_artists w left join artists_group a on a.RevisionID = w.RevisionID where a.ArtistID=$ArtistID");
    list($OldBody, $OldyImage, $OldIMDBID, $OldChineseName, $Birthday, $PlaceOfBirth) = $DB->next_record(MYSQLI_BOTH, false);
    $BodyChange = $Body != db_string($OldBody);
    $ImageChange = $Image != $OldyImage;
    $IMDBIDChange = $IMDBID != $OldIMDBID;
    $CNameChange = $CName != $OldChineseName;
    $TotalSummary = "";
    if ($BodyChange) {
        $TotalSummary .= "修改艺人信息。";
    }
    if ($ImageChange) {
        $TotalSummary .= "修改封面。";
    }
    if ($IMDBIDChange) {
        $TotalSummary .= "修改IMDBID。";
    }
    if ($CNameChange) {
        $TotalSummary .= "修改中文名。";
    }
    $TotalSummary .= $Summary ? " 原因：$Summary" : "";
    $DB->query("
		INSERT INTO wiki_artists
			(PageID, Body, Image, UserID, Summary, Time, IMDBID, ChineseName, Birthday, PlaceOfBirth)
		VALUES
			('$ArtistID', '$Body', '$Image', '$UserID', '$TotalSummary', '" . sqltime() . "', '$IMDBID', '$CName', '$Birthday', '$PlaceOfBirth')");
} else { // revert
    $DB->query("
		INSERT INTO wiki_artists (PageID, Body, Image, UserID, Summary, Time, IMDBID, ChineseName, Birthday, PlaceOfBirth)
		SELECT '$ArtistID', Body, Image, '$UserID', 'Reverted to revision $RevisionID', '" . sqltime() . "', 'IMDBID' , 'ChineseName', 'Birthday, 'PlaceOfBirth'
		FROM wiki_artists
		WHERE RevisionID = '$RevisionID'");
}

$RevisionID = $DB->inserted_id();

// Update artists table (technically, we don't need the RevisionID column, but we can use it for a join which is nice and fast)
$DB->query("
	UPDATE artists_group
	SET
		RevisionID = '$RevisionID'
	WHERE ArtistID = '$ArtistID'");

// There we go, all done!
$Cache->delete_value("artist_$ArtistID"); // Delete artist cache

// delete gropu artist;
$DB->query("
		SELECT GroupID
		FROM torrents_artists
		WHERE ArtistID = '$ArtistID'");
$Groups = $DB->collect('GroupID');
if (!empty($Groups)) {
    foreach ($Groups as $GroupID) {
        $Cache->delete_value("groups_artists_$GroupID"); // Delete group artist cache
        Torrents::update_hash($GroupID);
    }
}
header("Location: artist.php?id=$ArtistID");
