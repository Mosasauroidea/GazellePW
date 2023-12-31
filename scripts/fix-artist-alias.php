<?

require(__DIR__ . '/../classes/includes.php');

$StartArtistID = $argv[1];
$EndArtistID = $argv[2];
if ($StartArtistID && $EndArtistID) {
    echo "Handle artist: $StartArtistID-$EndArtistID\n";
    $DB->prepared_query("SELECT ArtistID, Name, SubName FROM artists_group WHERE ArtistID >= ? and ArtistID < ?", $StartArtistID, $EndArtistID);
} else {
    echo "Handle all artist\n";
    $DB->prepared_query("SELECT ArtistID, Name, SubName FROM artists_group");
}


$Artists = $DB->to_array('ArtistID', MYSQLI_ASSOC, false);

foreach ($Artists as $ArtistID => $Artist) {
    echo "Handle artist: $ArtistID\n";
    $DB->prepared_query("SELECT AliasID, Name FROM artists_alias WHERE ArtistID = $ArtistID");
    $Aliases = $DB->to_array('AliasID', MYSQLI_ASSOC, false);
    $FindName = false;
    $FindSubName = false;
    $Name = $Artist['Name'];
    $SubName = $Artist['SubName'];
    foreach ($Aliases as $AliasID => $Alias) {
        if (($Alias['Name']) == $Name) {
            $FindName = true;
        }
        if (($Alias['Name']) == $SubName) {
            $FindSubName = true;
        }
    }
    if (!$FindName && !empty($Name)) {
        $DB->prepared_query("INSERT INTO artists_alias (ArtistID, Name)
						VALUES (?, ?)", $ArtistID, $Name);

        echo "Insert alias: $Name for artist: $ArtistID\n";
    }
    if (!$FindSubName && !empty($SubName)) {
        $DB->prepared_query("INSERT INTO artists_alias (ArtistID, Name)
						VALUES (?, ?)", $ArtistID, $SubName);
        echo "Insert alias: $SubName for artist: $ArtistID\n";
    }
}
