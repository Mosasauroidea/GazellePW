<?
//Check if a givin string can be validated as a torrenthash
function is_valid_torrenthash($Str) {
    //6C19FF4C 6C1DD265 3B25832C 0F6228B2 52D743D5
    $Str = str_replace(' ', '', $Str);
    if (preg_match('/^[0-9a-fA-F]{40}$/', $Str))
        return $Str;
    return false;
}

function is_valid_imdbid($Str) {
    preg_match('/(tt\\d+)/', $Str, $IMDBMatch);
    if ($IMDBMatch[1]) {
        return $IMDBMatch[1];
    }
    return false;
}

//Functionality for the API to resolve input into other data.

function torrenthash_to_torrentid($Str) {
    global $Cache, $DB;
    $DB->query("
		SELECT ID
		FROM torrents
		WHERE HEX(info_hash) = '" . db_string($Str) . "'");
    $TorrentID = (int)array_pop($DB->next_record(MYSQLI_ASSOC));
    if ($TorrentID) {
        return $TorrentID;
    }
    return null;
}

function torrenthash_to_groupid($Str) {
    global $Cache, $DB;
    $DB->query("
		SELECT GroupID
		FROM torrents
		WHERE HEX(info_hash) = '" . db_string($Str) . "'");
    $GroupID = (int)array_pop($DB->next_record(MYSQLI_ASSOC));
    if ($GroupID) {
        return $GroupID;
    }
    return null;
}

function canCheckTorrent($TorrentID) {
    global $CheckAllTorrents, $CheckSelfTorrents, $LoggedUser;
    if ($CheckAllTorrents) {
        return true;
    } else if ($CheckSelfTorrents) {
        G::$DB->query("select 1 from torrents where userid=" . $LoggedUser['ID'] . " and id=$TorrentID");
        return G::$DB->has_results();
    } else {
        return false;
    }
}
