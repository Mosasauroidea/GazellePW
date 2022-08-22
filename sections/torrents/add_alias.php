<?php
authorize();

$UserID = $LoggedUser['ID'];
$GroupID = db_string($_POST['groupid']);
$Importance = $_POST['importance'];
$ArtistName = $_POST['artist'];
$ArtistSubName = $_POST['artist_sub'];
$ArtistIMDBID = $_POST['artist_id'];
if (!in_array($Importance, Artists::Importances)) {
    error(0);
}
$ArtistForm[db_string($Importance)][] = ['IMDBID' => $ArtistIMDBID, 'Name' => db_string($ArtistName), 'SubName' => db_string($ArtistSubName)];

if (empty($ArstitName) && empty($ArtistIMDBID)) {
    error(0);
}

if (!is_number($GroupID) || !$GroupID) {
    error(0);
}

$DB->query("
	SELECT Name,IMDBID
	FROM torrents_group
	WHERE ID = $GroupID");
if (!$DB->has_results()) {
    error(404);
}
list($GroupName, $IMDBID) = $DB->next_record(MYSQLI_NUM, false);

$ArtistForm = Artists::new_artist($ArtistForm, $IMDBID);
$ArtistID = $ArtistForm[$Importance][0]['ArtistID'];
$DB->query(
    "INSERT IGNORE INTO torrents_artists
		(GroupID, ArtistID, Importance, UserID)
	VALUES
		('$GroupID', '$ArtistID', '$Importance', '$UserID')"
);

if ($DB->affected_rows()) {
    $Changed = true;
    Misc::write_log("Artist $ArtistID ($ArtistName) was added to the group $GroupID ($GroupName) as " . $ArtistTypes[$Importance] . ' by user ' . $LoggedUser['ID'] . ' (' . $LoggedUser['Username'] . ')');
    Torrents::write_group_log($GroupID, 0, $LoggedUser['ID'], "added artist $ArtistName as " . $ArtistTypes[$Importance], 0);
    $Cache->delete_value("torrents_details_$GroupID");
    $Cache->delete_value("groups_artists_$GroupID"); // Delete group artist cache
    Torrents::update_hash($GroupID);
}


$Location = (empty($_SERVER['HTTP_REFERER'])) ? "torrents.php?id={$GroupID}" : $_SERVER['HTTP_REFERER'];
header("Location: {$Location}");
