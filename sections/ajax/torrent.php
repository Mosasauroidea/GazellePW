<?
require(CONFIG['SERVER_ROOT'] . '/sections/torrents/functions.php');

$GroupAllowed = array('WikiBody', 'MainWikiBody', 'WikiImage', 'ID', 'Name', 'Year', 'ReleaseType', 'CategoryID', 'Time');
$TorrentAllowed = array('ID',  'RemasterYear', 'RemasterTitle', 'Scene', 'FileCount', 'Size', 'Seeders', 'Leechers', 'Snatched', 'FreeTorrent', 'Time', 'Description', 'FileList', 'FilePath', 'UserID', 'Username');

$TorrentID = (int)$_GET['id'];
$TorrentHash = (string)$_GET['hash'];

if ($TorrentID && $TorrentHash) {
    ajax_json_error("bad parameters");
}

if ($TorrentHash) {
    if (!is_valid_torrenthash($TorrentHash)) {
        ajax_json_error("bad hash parameter");
    } else {
        $TorrentID = (int)torrenthash_to_torrentid($TorrentHash);
        if (!$TorrentID) {
            ajax_json_error("bad hash parameter");
        }
    }
}

if ($TorrentID <= 0) {
    ajax_json_error("bad id parameter");
}

$TorrentCache = Torrents::get_torrent($TorrentID, true, 0, true, true);

if (!$TorrentCache) {
    ajax_json_error("bad id parameter");
}

$Torrent = $TorrentCache;
$TorrentDetails = $TorrentCache['Group'];
$GroupID = $TorrentDetails['ID'];

if ($TorrentDetails['CategoryID'] == 0) {
    $CategoryName = "Unknown";
} else {
    $CategoryName = $Categories[$Group['CategoryID'] - 1];
}

$JsonTorrentDetails = array(
    'description' => html_entity_decode(Text::full_format($TorrentDetails['WikiBody'])),
    'mainDescription' => html_entity_decode(Text::full_format($TorrentDetails['MainWikiBody'])),
    'conver' => $TorrentDetails['WikiImage'],
    'id' => (int)$TorrentDetails['ID'],
    'name' => $TorrentDetails['Name'],
    'subName' => html_entity_decode($TorrentDetails['SubName']),
    'year' => (int)$TorrentDetails['Year'],
    'releaseType' => t('server.torrents.release_types')[$TorrentDetails['ReleaseType']],
    'categoryId' => (int)$TorrentDetails['CategoryID'],
    'categoryName' => $CategoryName,
    'time' => $TorrentDetails['Time'],
    'isBookmarked' => Bookmarks::has_bookmarked('torrent', $GroupID),
    'tags' => $TorrentDetails['TorrentTags'],
    'imdbId' => $TorrentDetails['IMDBID'],
    'imdbRating' => $TorrentDetails['IMDBRating'],
    'imdbVote' => $TorrentDetails['IMDBVote'],
    'doubanId' => $TorrentDetails['DoubanID'],
    'doubanRating' => $TorrentDetails['DoubanRating'],
    'doubanVote' => $TorrentDetails['DoubanVote'],
    'rtRating' => $TorrentDetails['RTRating'],
    'duration' => $TorrentDetails['Duration'],
    'releaseDate' => $TorrentDetails['ReleaseDate'],
    'region' => $TorrentDetails['Region'],
    'language' => $TorrentDetails['Language'],
);

$Reports = Reports::get_reports($TorrentID);
if (count($Reports) > 0) {
    $Torrent['Reported'] = true;
} else {
    $Torrent['Reported'] = false;
}
// Convert file list back to the old format
$FileList = explode("\n", $Torrent['FileList']);
foreach ($FileList as &$File) {
    $File = Torrents::filelist_old_format($File);
}
unset($File);
$FileList = implode('|||', $FileList);
$Userinfo = Users::user_info($Torrent['UserID']);
$JsonTorrentList[] = array(
    'id' => (int)$Torrent['ID'],
    'infoHash' => $Torrent['InfoHash'],
    'remasterYear' => (int)$Torrent['RemasterYear'],
    'remasterTitle' => $Torrent['RemasterTitle'],
    'remasterCustomTitle' => $Torrent['RemasterCustomTitle'],
    'scene' => $Torrent['Scene'] == 1,
    'fileCount' => (int)$Torrent['FileCount'],
    'size' => (int)$Torrent['Size'],
    'seeders' => (int)$Torrent['Seeders'],
    'leechers' => (int)$Torrent['Leechers'],
    'snatched' => (int)$Torrent['Snatched'],
    'freeTorrent' => Torrents::torrent_freeleech($Torrent),
    'freeType' => Torrents::torrent_freetype($Torrent),
    'reported' => $Torrent['Reported'],
    'time' => $Torrent['Time'],
    'description' => $Torrent['Description'],
    'mediainfos' => isset($Torrent['MediaInfo']) ? json_decode($Torrent['MediaInfo']) : [],
    'fileName' => Torrents::filename($Torrent),
    'releaseGroup' => Torrents::release_group($Torrent),
    'fileList' => $FileList,
    'filePath' => $Torrent['FilePath'],
    'userId' => (int)$Torrent['UserID'],
    'username' => $Userinfo['Username'],
    'resolution' => $Torrent['Resolution'],
    'source' => $Torrent['Source'],
    'codec' => $Torrent['Codec'],
    'container' => $Torrent['Container'],
    'processing' => $Torrent['Processing'],
    'chineseDubbed' => $Torrent['ChineseDubbed'],
    'specialSub' => $Torrent['SpecialSub'],
    'subtitleType' => $Torrent['SubtitleType'],
    'subtitles' => $Torrent['Subtitles'],
);

ajax_json_success(array('group' => $JsonTorrentDetails, 'torrent' => array_pop($JsonTorrentList)));
