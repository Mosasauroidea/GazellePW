<?
require(__DIR__ . '/../classes/includes.php');

$StartGroupID = $argv[1];
$EndGroupID = $argv[2];
$Select = 'ID';
if ($StartGroupID && $EndGroupID) {
    echo "Handle group: $StartGroupID-$EndGroupID\n";
    $DB->prepared_query("SELECT $Select FROM torrents_group WHERE ID >= ? and ID < ?", $StartGroupID, $EndGroupID);
} else {
    echo "Handle all group\n";
    $DB->prepared_query("SELECT $Select FROM torrents_group");
}

$Datas = [];

while ($Data = $DB->next_record(MYSQLI_NUM)) {
    $Datas[] = $Data;
}
foreach ($Datas as $Data) {
    list($ID) = $Data;
    echo "Begin handle group: $ID\n";
    $AllArtists = Artists::get_artist($ID);
    $Artist2IMDBID = [];
    foreach ($AllArtists[Artists::Actor] as $Key => $Artist) {
        if (!empty($Artist['IMDBID'])) {
            $Artist2IMDBID[$Artist['ArtistID']] = $Artist['IMDBID'];
        }
    }
    foreach ($AllArtists as $Importace => $Artists) {
        if ($Importace == Artists::Actor) {
            continue;
        }
        foreach ($Artists as $Key => $Artist) {
            if (!empty($Artist['IMDBID'])) {
                $Artist2IMDBID[$Artist['ArtistID']] = $Artist['IMDBID'];
            }
        }
    }
    $IMDBIDs = array_values($Artist2IMDBID);
    $NewArtists = Movie::get_artists(array_slice($IMDBIDs, 0, 10));
    foreach ($Artist2IMDBID as $ArtistID => $IMDBID) {
        $DB->query("SELECT ArtistID, Body, MainBody, Image, IMDBID, SubName, Name, RevisionID FROM artists_group WHERE ArtistID = $ArtistID");
        list($ArtistID, $Body, $MainBody, $Image, $IMDBID, $SubName, $Name, $RevisionID) = $DB->next_record(MYSQLI_NUM);
        $NewRevision = empty($RevisionID);
        if (empty($MainBody) && !empty($NewArtists[$IMDBID]['MainDescription'])) {
            $NewRevision = true;
            $MainBody = $NewArtists[$IMDBID]['MainDescription'];
            echo "Get main plot success for artist id: $ArtistID\n";
        }
        if ($NewRevision) {
            $DB->prepared_query("INSERT INTO wiki_artists
							(PageID, Body, MainBody, Image, UserID, Summary, Time, IMDBID, Name, SubName)
						VALUES
							(?,?,?,?,?,?,?,?,?,?)", $ArtistID, $Body, $MainBody, $Image, 0, 'Auto load', sqltime(), $IMDBID, $Name, $SubName);
            echo "Add new revision for artist:$ArtistID, revision id:$RevisionID\n";
        } else {
            $DB->query("UPDATE wiki_artists SET Body='$Body', MainBody='$MainBody', Image='$Image', IMDBID='$IMDBID', Name='$Name', SubName='$SubName'");
        }
        echo "Update artist: $ArtistID wiki and main body info success\n";
    }
}
