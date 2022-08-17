<?php
authorize();

if (!check_perms('torrents_edit')) {
    error(403);
}
$ArtistID = $_POST['artistid'];
$Redirect = $_POST['redirect'];
if (!$Redirect) {
    error(0);
}

if (!is_number($ArtistID) || !($Redirect === 0 || is_number($Redirect)) || !$ArtistID) {
    error(0);
}

if ($AliasName == '') {
    error(t('server.artist.blank_artist_name'));
}

if (!$CloneAliasID) {
    $DB->query("
			SELECT ArtistID, Redirect
			FROM artists_alias
			WHERE AliasID = $Redirect");
    if (!$DB->has_results()) {
        error(t('server.artist.cannot_redirect'));
    }
    list($FoundArtistID, $FoundRedirect) = $DB->next_record();
    if ($ArtistID != $FoundArtistID) {
        error(t('server.artist.redirection_must_target'));
    }
    if ($FoundRedirect != 0) {
        $Redirect = $FoundRedirect;
    }
    $DB->query("
		INSERT INTO artists_alias
			(ArtistID, Name, Redirect, UserID)
		VALUES
			($ArtistID, '$DBAliasName', $Redirect, " . $LoggedUser['ID'] . ')');
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
