<?
$DB = G::$DB;
$StartGroupID = $argv[3];
$EndGroupID = $argv[4];
if ($StartGroupID && $EndGroupID) {
    $DB->prepared_query("SELECT ID, IMDBID FROM torrents_group WHERE ID >= ? and ID < ?", $StartGroupID, $EndGroupID);
} else {
    $DB->prepared_query("SELECT ID, IMDBID FROM torrents_group");
}

$Groups = $DB->to_array('ID', MYSQLI_ASSOC);

foreach ($Groups as $ID => $Data) {
    if (empty($Data['IMDBID'])) {
        continue;
    }
    $IMDBID = $Data['IMDBID'];
    $Artists = MOVIE::get_imdb_actor_data($IMDBID);
    $IMDBIDs = [];
    $Importance = [];
    $All = [$Artists->Directors, $Artists->Writters, $Artists->Producers, $Artists->Composers, $Artists->Cinematographers, $Artists->Casts];
    foreach ($All as $index => $actor) {
        foreach ($actor as $key => $value) {
            $IMDBIDs[] = "nm" .  $value->imdb;
            $Importance['nm' . $value->imdb] = $index + 1;
        }
    }
    $IMDBIDStr = [];
    foreach ($IMDBIDs as $key => $value) {
        $IMDBIDStr[] = "'" . $value . "'";
    }
    $DB->query("SELECT ArtistID, IMDBID FROM artists_group WHERE IMDBID in (" .  implode(',', $IMDBIDStr) . ")");
    $IMDBID2ArtistID = $DB->to_array('IMDBID', MYSQLI_ASSOC);
    $ArtistIDs = [];
    foreach ($IMDBID2ArtistID as $key => $value) {
        $ArtistIDs[] = $value['ArtistID'];
    }
    $DB->query("SELECT ArtistID, AliasID FROM artists_alias WHERE ArtistID in (" . implode(',', $ArtistIDs) . ") and Redirect = 0");
    $ArtistID2AliasID = $DB->to_array('ArtistID', MYSQLI_ASSOC);

    foreach ($IMDBIDs as $Num => $IMDBID) {
        $ArtistID = $IMDBID2ArtistID[$IMDBID]['ArtistID'];
        $Importance = $Importance[$IMDBID];

        $AliasID = $ArtistID2AliasID[$ArtistID]['AliasID'];
        if (empty($AliasID)) {
            $AliasID = 0;
        }
        $DB->query(
            "INSERT IGNORE INTO torrents_artists (GroupID, ArtistID, AliasID, UserID, Importance, Credit, `Order`)
                VALUES ($ID, " . $ArtistID . ', ' . $AliasID . ', ' . "0" . ", '$Importance', true, $Num)"
        );
    }
    echo "Process Group: $ID\n";
}
