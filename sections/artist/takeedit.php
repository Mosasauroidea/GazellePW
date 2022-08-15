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
    $Name = db_string($_POST['name']);
    $SubName = db_string($_POST['sub_name']);
    ImageTools::blacklisted($Image);
    // Trickery
    if (!preg_match("/^" . IMAGE_REGEX . "$/i", $Image)) {
        $Image = '';
    }
}

// Insert revision
if (!$RevisionID) { // edit
    $DB->query(
        "SELECT w.Body, w.Image, w.IMDBID, w.SubName, w.Name from wiki_artists w left join artists_group a on a.RevisionID = w.RevisionID where a.ArtistID=$ArtistID"
    );
    list($OldBody, $OldyImage, $OldIMDBID, $OldSubName, $OldName) = $DB->next_record(MYSQLI_NUM, false);
    $BodyChange = $Body != db_string($OldBody);
    $ImageChange = $Image != $OldyImage;
    $IMDBIDChange = $IMDBID != $OldIMDBID;
    $SubNameChange = $SubName != $OldSubName;
    $NameChange = $Name != $OldName;
    $TotalSummary = "";
    // TODO by qwerty i18N
    if ($BodyChange) {
        $TotalSummary .= "修改艺人信息。";
    }
    if ($ImageChange) {
        $TotalSummary .= "修改封面。";
    }
    if ($IMDBIDChange) {
        $TotalSummary .= "修改IMDBID。";
    }
    if ($SubNameChange) {
        $TotalSummary .= "修改子名称";
    }
    if ($NameChange) {
        $TotalSummary .= "修改名称";
    }
    $TotalSummary .= $Summary ? " 原因：$Summary" : "";
    $DB->query("
		INSERT INTO wiki_artists
			(PageID, Body, Image, UserID, Summary, Time, IMDBID, SubName, Name)
		VALUES
			('$ArtistID', '$Body', '$Image', '$UserID', '$TotalSummary', '" . sqltime() . "', '$IMDBID', '$SubName', '$Name')");
    if ($SubNameChange) {
    }
} else { // revert
    G::$DB->query(
        "SELECT 
        w.Body, w.Image, w.IMDBID, w.SubName, w.Name from wiki_artists where RevisionID = '$RevisionID'"
    );
    list($Body, $Image, $IMDBID, $SubName, $Name) = $DB->next_record(MYSQLI_NUM, false);
    $DB->query(
        "INSERT INTO wiki_artists (PageID, Body, Image, UserID, Summary, Time, IMDBID, SubName, Name)
		SELECT '$ArtistID', Body, Image, '$UserID', 'Reverted to revision $RevisionID', '" . sqltime() . "', 'IMDBID' , 'SubName', 'Name'
		FROM wiki_artists
		WHERE RevisionID = '$RevisionID'"
    );
}

$RevisionID = $DB->inserted_id();

// Update artists table (technically, we don't need the RevisionID column, but we can use it for a join which is nice and fast)
$DB->query(
    "UPDATE artists_group
	SET
        Image = '$Image',
        Body = '$Body',
        IMDBID = '$IMDBID',
        SubName = '$SubName',
		RevisionID = '$RevisionID',
        Name = '$Name'
	WHERE ArtistID = '$ArtistID'"
);

// There we go, all done!
$Cache->delete_value("artist_$ArtistID"); // Delete artist cache

// delete group artist cache;
$DB->query(
    "SELECT GroupID
		FROM torrents_artists
		WHERE ArtistID = '$ArtistID'"
);
$Groups = $DB->collect('GroupID');
if (!empty($Groups)) {
    foreach ($Groups as $GroupID) {
        $Cache->delete_value("groups_artists_$GroupID"); // Delete group artist cache
        Torrents::update_hash($GroupID);
    }
}
header("Location: artist.php?id=$ArtistID");
