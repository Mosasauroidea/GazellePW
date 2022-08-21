<?php
authorize();

if (!check_perms('torrents_edit')) {
    error(403);
}
$ArtistID = $_POST['artistid'];
$AliasName = $_POST['name'];

if (!is_number($ArtistID) || !$ArtistID) {
    error(0);
}

if ($AliasName == '') {
    error(t('server.artist.blank_artist_name'));
}

if (!$CloneAliasID) {
    $DB->query("
		INSERT INTO artists_alias
			(ArtistID, Name, UserID)
		VALUES
			($ArtistID, '$AliasName', " . $LoggedUser['ID'] . ')');
    $AliasID = $DB->inserted_id();

    $DB->query("
		SELECT Name
		FROM artists_group
		WHERE ArtistID = $ArtistID");
    list($ArtistName) = $DB->next_record(MYSQLI_NUM, false);

    Misc::write_log("The alias $AliasID was added to the artist $ArtistID by user " . $LoggedUser['ID'] . ' (' . $LoggedUser['Username'] . ')');
}

$Location = (empty($_SERVER['HTTP_REFERER'])) ? "artist.php?action=edit&artistid={$ArtistID}" : $_SERVER['HTTP_REFERER'];
header("Location: {$Location}");
