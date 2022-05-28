<?

ini_set('memory_limit', -1);
//~~~~~~~~~~~ Main bookmarks page ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~//


function compare($X, $Y) {
    return ($Y['count'] - $X['count']);
}

if (!empty($_GET['userid'])) {
    if (!check_perms('users_override_paranoia')) {
        error(403);
    }
    $UserID = $_GET['userid'];
    if (!is_number($UserID)) {
        error(404);
    }
    $DB->query("
		SELECT Username
		FROM users_main
		WHERE ID = '$UserID'");
    list($Username) = $DB->next_record();
} else {
    $UserID = $LoggedUser['ID'];
}

$Sneaky = ($UserID != $LoggedUser['ID']);

$JsonBookmarks = array();

list($GroupIDs, $CollageDataList, $GroupList) = Users::get_bookmarks($UserID);
foreach ($GroupIDs as $GroupID) {
    if (!isset($GroupList[$GroupID])) {
        continue;
    }
    $Group = $GroupList[$GroupID];
    $JsonTorrents = array();
    foreach ($Group['Torrents'] as $Torrent) {
        $JsonTorrents[] = array(
            'id' => (int)$Torrent['ID'],
            'groupId' => (int)$Torrent['GroupID'],
            'remasterYear' => (int)$Torrent['RemasterYear'],
            'remasterTitle' => $Torrent['RemasterTitle'],
            'scene' => $Torrent['Scene'] == 1,
            'fileCount' => (int)$Torrent['FileCount'],
            'freeTorrent' => Torrents::torrent_freeleech($Torrent),
            'size' => (float)$Torrent['Size'],
            'leechers' => (int)$Torrent['Leechers'],
            'seeders' => (int)$Torrent['Seeders'],
            'snatched' => (int)$Torrent['Snatched'],
            'time' => $Torrent['Time'],
            'hasFile' => (int)$Torrent['HasFile']
        );
    }
    $JsonBookmarks[] = array(
        'id' => (int)$Group['ID'],
        'name' => $Group['Name'],
        'year' => (int)$Group['Year'],
        'tagList' => $Group['TagList'],
        'releaseType' => $Group['ReleaseType'],
        'image' => $Group['WikiImage'],
        'torrents' => $JsonTorrents
    );
}

print
    json_encode(
        array(
            'status' => 'success',
            'response' => array(
                'bookmarks' => $JsonBookmarks
            )
        )
    );
