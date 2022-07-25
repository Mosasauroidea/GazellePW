<?
include(CONFIG['SERVER_ROOT'] . '/sections/torrents/functions.php');

if (!empty($_GET['order_way']) && $_GET['order_way'] == 'asc') {
    $OrderWay = 'asc';
} else {
    $OrderWay = 'desc';
}

if (empty($_GET['order_by']) || !isset(TorrentSearch::$SortOrders[$_GET['order_by']])) {
    $OrderBy = 'time';
} else {
    $OrderBy = $_GET['order_by'];
}

$GroupResults = !isset($_GET['group_results']) || $_GET['group_results'] != '0';
$Page = !empty($_GET['page']) ? (int)$_GET['page'] : 1;
$Search = new TorrentSearch($GroupResults, $OrderBy, $OrderWay, $Page, CONFIG['TORRENTS_PER_PAGE']);
$Results = $Search->query($_GET);
$Groups = $Search->get_groups();

$NumResults = $Search->record_count();

if ($Results === false) {
    ajax_json_error('Search returned an error. Make sure all parameters are valid and of the expected types.');
}
if ($NumResults == 0) {
    ajax_json_success(array(
        'results' => array(),
        'youMightLike' => array() // This slow and broken feature has been removed
    ));
}

$Bookmarks = Bookmarks::all_bookmarks('torrent');

$JsonGroups = array();
foreach ($Results as $Key => $GroupID) {
    $GroupInfo = $Groups[$GroupID];
    if (empty($GroupInfo['Torrents'])) {
        continue;
    }
    $CategoryID = $GroupInfo['CategoryID'];
    $GroupYear = $GroupInfo['Year'];
    $GroupName = $GroupInfo['Name'];
    $GroupSubName = html_entity_decode($GroupInfo['SubName']);
    $ReleaseType = $GroupInfo['ReleaseType'];
    if ($GroupResults) {
        $Torrents = $GroupInfo['Torrents'];
        $GroupTime = $MaxSize = $TotalLeechers = $TotalSeeders = $TotalSnatched = 0;
        foreach ($Torrents as $T) {
            $GroupTime = max($GroupTime, strtotime($T['Time']));
            $MaxSize = max($MaxSize, $T['Size']);
            $TotalLeechers += $T['Leechers'];
            $TotalSeeders += $T['Seeders'];
            $TotalSnatched += $T['Snatched'];
        }
    } else {
        $TorrentID = $Key;
        $Torrents = array($TorrentID => $GroupInfo['Torrents'][$TorrentID]);
    }
    $TagList = explode(' ', str_replace('_', '.', $GroupInfo['TagList']));
    if ($GroupResults && (count($Torrents) > 1 || isset($GroupedCategories[$CategoryID - 1]))) {
        $JsonTorrents = array();
        foreach ($Torrents as $TorrentID => $Data) {
            $freeType = $Data['FreeTorrent'];
            if (Torrents::global_freeleech()) {
                $FreeType = 1;
            }
            $JsonTorrents[] = array(
                'torrentId' => (int)$TorrentID,
                'editionId' => (int)$EditionID,
                'remasterYear' => (int)$Data['RemasterYear'],
                'remasterTitle' => $Data['RemasterTitle'],
                'remasterCustomTitle' => $Data['RemasterCustomTitle'],
                'scene' => $Data['Scene'] == '1',
                'jinzhuan' => $Data['Jinzhuan'] == '1',
                'fileCount' => (int)$Data['FileCount'],
                'time' => $Data['Time'],
                'size' => (int)$Data['Size'],
                'snatches' => (int)$Data['Snatched'],
                'seeders' => (int)$Data['Seeders'],
                'leechers' => (int)$Data['Leechers'],
                'isFreeleech' => $Data['FreeTorrent'] == '1' || Torrents::global_freeleech(),
                'isNeutralLeech' => $Data['FreeTorrent'] == '2',
                'freeType' => $freeType,
                'isPersonalFreeleech' => $Data['PersonalFL'],
                'canUseToken' => Torrents::can_use_token($Data),
                'hasSnatched' => $Data['IsSnatched'],
                'resolution' => $Data['Resolution'],
                'source' => $Data['Source'],
                'codec' => $Data['Codec'],
                'container' => $Data['Container'],
                'processing' => $Data['Processing'],
                'chineseDubbed' => $Data['ChineseDubbed'],
                'specialSub' => $Data['SpecialSub'],
                'subtitles' => $Data['Subtitles'],
                'fileName' => Torrents::filename($Data),
                'releaseGroup' => Torrents::release_group($Data),
            );
        }

        $JsonGroups[] = array(
            'groupId' => (int)$GroupID,
            'groupName' => $GroupName,
            'groupSubName' => $GroupSubName,
            'cover' => $GroupInfo['WikiImage'],
            'tags' => $TagList,
            'bookmarked' => in_array($GroupID, $Bookmarks),
            'groupYear' => (int)$GroupYear,
            'releaseType' => t('server.torrents.release_types')[$ReleaseType],
            'groupTime' => (string)$GroupTime,
            'maxSize' => (int)$MaxSize,
            'totalSnatched' => (int)$TotalSnatched,
            'totalSeeders' => (int)$TotalSeeders,
            'totalLeechers' => (int)$TotalLeechers,
            'imdbId' => $GroupInfo['IMDBID'],
            'imdbRating' => $GroupInfo['IMDBRating'],
            'imdbVote' => $GroupInfo['IMDBVote'],
            'doubanId' => $GroupInfo['DoubanID'],
            'doubanRating' => $GroupInfo['DoubanRating'],
            'doubanVote' => $GroupInfo['DoubanVote'],
            'rtRating' => $GroupInfo['RTRating'],
            'region' => $GroupInfo['Region'],
            'torrents' => $JsonTorrents,
        );
    } else {
        // Viewing a type that does not require grouping
        list($TorrentID, $Data) = each($Torrents);
        $freeType = $Data['FreeTorrent'];
        if (Torrents::global_freeleech()) {
            $FreeType = 1;
        }
        $JsonGroups[] = array(

            'imdbId' => $GroupInfo['IMDBID'],
            'imdbRating' => $GroupInfo['IMDBRating'],
            'imdbVote' => $GroupInfo['IMDBVote'],
            'doubanId' => $GroupInfo['DoubanID'],
            'doubanRating' => $GroupInfo['DoubanRating'],
            'doubanVote' => $GroupInfo['DoubanVote'],
            'rtRating' => $GroupInfo['RTRating'],
            'region' => $GroupInfo['Region'],
            'groupId' => (int)$GroupID,
            'groupName' => $GroupName,
            'cover' => $GroupInfo['WikiImage'],
            'groupSubName' => $GroupSubName,
            'groupYear' => (int)$GroupYear,
            'torrentId' => (int)$TorrentID,
            'tags' => $TagList,
            'category' => $Categories[$CategoryID - 1],
            'fileCount' => (int)$Data['FileCount'],
            'groupTime' => (string)strtotime($Data['Time']),
            'size' => (int)$Data['Size'],
            'snatches' => (int)$Data['Snatched'],
            'seeders' => (int)$Data['Seeders'],
            'leechers' => (int)$Data['Leechers'],
            'isFreeleech' => $Data['FreeTorrent'] == '1' || Torrents::global_freeleech(),
            'isNeutralLeech' => $Data['FreeTorrent'] == '2',
            'isPersonalFreeleech' => $Data['PersonalFL'],
            'canUseToken' => Torrents::can_use_token($Data),
            'hasSnatched' => $Data['IsSnatched'],
            'resolution' => $Data['Resolution'],
            'source' => $Data['Source'],
            'codec' => $Data['Codec'],
            'container' => $Data['Container'],
            'processing' => $Data['Processing'],
            'chineseDubbed' => $Data['ChineseDubbed'],
            'specialSub' => $Data['SpecialSub'],
            'subtitles' => $Data['Subtitles'],
            'freeType' => $freeType,
        );
    }
}
ajax_json_success(array(
    'currentPage' => intval($Page),
    'pages' => ceil($NumResults / CONFIG['TORRENTS_PER_PAGE']),
    'results' => $JsonGroups
));
