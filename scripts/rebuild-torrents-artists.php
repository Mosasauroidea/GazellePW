<?
require(__DIR__ . '/../classes/includes.php');

$StartGroupID = $argv[1];
$EndGroupID = $argv[2];
if ($StartGroupID && $EndGroupID) {
    echo "Handle group: $StartGroupID-$EndGroupID\n";
    $DB->prepared_query("SELECT ID, IMDBID FROM torrents_group WHERE ID >= ? and ID < ?", $StartGroupID, $EndGroupID);
} else {
    echo "Handle all group\n";
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
    $Importances = [];
    $Names = [];
    $All = [$Artists->Directors, $Artists->Writters, $Artists->Producers, $Artists->Composers, $Artists->Cinematographers, $Artists->Casts];
    foreach ($All as $index => $actor) {
        foreach ($actor as $key => $value) {
            $IMDBIDs[] = "nm" .  $value->imdb;
            $Importances[] = $index + 1;
            $Names['nm' . $value->imdb] = $value->name;
        }
    }
    if (Count($IMDBIDs) == 0) {
        continue;
    }
    $IMDBIDStr = [];
    foreach ($IMDBIDs as $key => $value) {
        $IMDBIDStr[] = "'" . $value . "'";
    }
    $DB->query("SELECT ArtistID, IMDBID FROM artists_group WHERE IMDBID in (" .  implode(',', $IMDBIDStr) . ")");
    $IMDBID2ArtistID = $DB->to_array('IMDBID', MYSQLI_ASSOC);


    $MissIMDBID = [];
    foreach ($IMDBIDs as $key => $value) {
        if (empty($IMDBID2ArtistID[$value])) {
            $MissIMDBID[] = $value;
        }
    }

    $NewArtists = Movie::get_artists($MissIMDBID, $IMDBID);
    foreach ($MissIMDBID as $key => $value) {
        $ArtistDetail = MOVIE::get_default_artist($value);
        $Detail = $NewArtists[$value];
        if ($Detail) {
            $ArtistDetail = $Detail;
        }
        $NewArtist['Name'] = html_entity_decode($Names[$value], ENT_QUOTES);
        $NewArtist['Image'] = $ArtistDetail['Image'];
        $NewArtist['Description'] = $ArtistDetail['Description'];
        $NewArtist['Birthday'] = $ArtistDetail['Birthday'];
        $NewArtist['PlaceOfBirth'] = $ArtistDetail['PlaceOfBirth'];
        $NewArtist['IMDBID'] = $value;
        $NewArtist = Artists::add_artist($NewArtist);
        $IMDBID2ArtistID[$value] = ['ArtistID' => $NewArtist['ArtistID']];
        echo "Add new artist: [$value] " . $Names[$value] . "\n";
    }


    $ArtistIDs = [];
    foreach ($IMDBID2ArtistID as $key => $value) {
        $ArtistIDs[] = $value['ArtistID'];
    }

    $DB->query("DELETE FROM torrents_artists WHERE GroupID = $ID");

    foreach ($IMDBIDs as $Num => $IMDBID) {
        $ArtistID = $IMDBID2ArtistID[$IMDBID]['ArtistID'];
        $Importance = $Importances[$Num];
        $DB->query(
            "INSERT IGNORE INTO torrents_artists (GroupID, ArtistID, UserID, Importance, Credit, `Order`)
                VALUES ($ID, " . $ArtistID . ', ' . "0" . ", '$Importance', true, $Num)"
        );
    }
    $Cache->delete_value("groups_artists_$ID"); // Delete group artist cache
    echo "Process Group: $ID\n";
}
