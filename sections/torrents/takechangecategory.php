<?

/***************************************************************
 * Temp handler for changing the category for a single torrent.
 ****************************************************************/

authorize();
if (!check_perms('users_mod')) {
    error(403);
}

$OldGroupID = $_POST['oldgroupid'];
$TorrentID = $_POST['torrentid'];
$Title = db_string(trim($_POST['title']));
$OldCategoryID = $_POST['oldcategoryid'];
$NewCategoryID = $_POST['newcategoryid'];
if (!is_number($OldGroupID) || !is_number($TorrentID) || !$OldGroupID || !$TorrentID || empty($Title)) {
    error(0);
}

$DB->query("
	UPDATE torrents
	SET GroupID = '$GroupID'
	WHERE ID = '$TorrentID'");

// Delete old group if needed
$DB->query("
	SELECT ID
	FROM torrents
	WHERE GroupID = '$OldGroupID'");
if (!$DB->has_results()) {
    // TODO: votes etc.
    $DB->query("
		UPDATE comments
		SET PageID = '$GroupID'
		WHERE Page = 'torrents'
			AND PageID = '$OldGroupID'");
    Torrents::delete_group($OldGroupID);
    $Cache->delete_value("torrent_comments_{$GroupID}_catalogue_0");
} else {
    Torrents::update_hash($OldGroupID);
}

Torrents::update_hash($GroupID);

$Cache->delete_value("torrent_download_$TorrentID");

Misc::write_log("Torrent $TorrentID was edited by $LoggedUser[Username]");
Torrents::write_group_log($GroupID, 0, $LoggedUser['ID'], "merged from group $OldGroupID", 0);
$DB->query("
	UPDATE group_log
	SET GroupID = $GroupID
	WHERE GroupID = $OldGroupID");

header("Location: torrents.php?id=$GroupID");
