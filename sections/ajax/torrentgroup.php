<?php

require(CONFIG['SERVER_ROOT'] . '/sections/torrents/functions.php');

$GroupAllowed = array('WikiBody', 'MainWikiBody', 'WikiImage', 'ID', 'Name', 'Year',  'ReleaseType', 'CategoryID', 'Time');
$TorrentAllowed = array('ID', 'RemasterYear', 'RemasterTitle',  'Scene', 'FileCount', 'Size', 'Seeders', 'Leechers', 'Snatched', 'FreeTorrent', 'Time', 'Description', 'FileList', 'FilePath', 'UserID', 'Username');

$GroupID = (int)$_GET['id'];
$TorrentHash = (string)$_GET['hash'];

if ($GroupID && $TorrentHash) {
    ajax_json_error("bad parameters");
}

if ($TorrentHash) {
    if (!is_valid_torrenthash($TorrentHash)) {
        ajax_json_error("bad hash parameter");
    } else {
        $GroupID = (int)torrenthash_to_groupid($TorrentHash);
        if (!$GroupID) {
            ajax_json_error("bad hash parameter");
        }
    }
}

if ($GroupID <= 0) {
    ajax_json_error("bad id parameter");
}

$TorrentCache = Torrents::get_group($GroupID, true, 0, true, true);

if (!$TorrentCache) {
    ajax_json_error("bad id parameter");
}

$TorrentDetails = $TorrentCache;
$TorrentList = $TorrentDetails['Torrents'];

$CategoryName = $Categories[$TorrentDetails['CategoryID'] - 1];

$TagList = explode('|', $TorrentDetails['GROUP_CONCAT(DISTINCT tags.Name SEPARATOR \'|\')']);

$JsonTorrentDetails = array(
    'wikiBody'        => html_entity_decode(Text::full_format($TorrentDetails['WikiBody'])),
    'MainwikiBody'        => html_entity_decode(Text::full_format($TorrentDetails['MainWikiBody'])),
    'wikiImage'       => $TorrentDetails['WikiImage'],
    'id'              => (int)$TorrentDetails['ID'],
    'name'            => $TorrentDetails['Name'],
    'subName' => html_entity_decode($TorrentDetails['SubName']),
    'year'            => (int)$TorrentDetails['Year'],
    'releaseType' => t('server.torrents.release_types')[$TorrentDetails['ReleaseType']],
    'categoryId'      => (int)$TorrentDetails['CategoryID'],
    'categoryName'    => $CategoryName,
    'time'            => $TorrentDetails['Time'],
    'isBookmarked'    => Bookmarks::has_bookmarked('torrent', $GroupID),
    'tags'            => $TagList
);

$JsonTorrentList = array();
foreach ($TorrentList as $Torrent) {
    // Convert file list back to the old format
    $FileList = explode("\n", $Torrent['FileList']);
    foreach ($FileList as &$File) {
        $File = Torrents::filelist_old_format($File);
    }
    unset($File);
    $FileList = implode('|||', $FileList);
    $Userinfo = Users::user_info($Torrent['UserID']);
    $Reports = Reports::get_reports($Torrent['ID']);
    $Torrent['Reported'] = count($Reports) > 0;
    $JsonTorrentList[] = array(
        'id'                      => (int)$Torrent['ID'],
        'remasterYear'            => (int)$Torrent['RemasterYear'],
        'remasterTitle'           => $Torrent['RemasterTitle'],
        'scene'       => $Torrent['Scene'] == 1,
        'fileCount'   => (int)$Torrent['FileCount'],
        'size'        => (int)$Torrent['Size'],
        'seeders'     => (int)$Torrent['Seeders'],
        'leechers'    => (int)$Torrent['Leechers'],
        'snatched'    => (int)$Torrent['Snatched'],
        'freeTorrent' => Torrents::torrent_freeleech($Torrent),
        'freeType' => Torrents::torrent_freetype($Torrent),
        'reported'    => $Torrent['Reported'],
        'time'        => $Torrent['Time'],
        'description' => $Torrent['Description'],
        'fileList'    => $FileList,
        'filePath'    => $Torrent['FilePath'],
        'userId'      => (int)$Torrent['UserID'],
        'username'    => $Userinfo['Username']
    );
}

ajax_json_success(array('group' => $JsonTorrentDetails, 'torrents' => $JsonTorrentList));
