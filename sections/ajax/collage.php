<?
if (empty($_GET['id']) || !is_number($_GET['id'])) {
    ajax_json_error("bad parameters");
}
$CollageID = $_GET['id'];

$CacheKey = "collage_$CollageID";
$CollageData = $Cache->get_value($CacheKey);
if ($CollageData) {
    list($Name, $Description, $CommentList, $Deleted, $CollageCategoryID, $CreatorID, $Locked, $MaxGroups, $MaxGroupsPerUser, $Updated, $Subscribers) = $CollageData;
} else {
    $DB->query("
		SELECT
			Name,
			Description,
			UserID,
			Deleted,
			CategoryID,
			Locked,
			MaxGroups,
			MaxGroupsPerUser,
			Updated,
			Subscribers
		FROM collages
		WHERE ID = '$CollageID'");
    if (!$DB->has_results()) {
        ajax_json_error();
    }
    list($Name, $Description, $CreatorID, $Deleted, $CollageCategoryID, $Locked, $MaxGroups, $MaxGroupsPerUser, $Updated, $Subscribers) = $DB->next_record(MYSQLI_NUM);
    $CommentList = null;
    $SetCache = true;
}

// TODO: Cache this
$DB->query("
	SELECT GroupID
	FROM collages_torrents
	WHERE CollageID = $CollageID");
$TorrentGroups = $DB->collect('GroupID');

$JSON = array(
    'id'                  => (int)$CollageID,
    'name'                => $Name,
    'description'         => Text::full_format($Description),
    'creatorID'           => (int)$CreatorID,
    'deleted'             => (bool)$Deleted,
    'collageCategoryID'   => (int)$CollageCategoryID,
    'locked'              => (bool)$Locked,
    'maxGroups'           => (int)$MaxGroups,
    'maxGroupsPerUser'    => (int)$MaxGroupsPerUser,
    'hasBookmarked'       => Bookmarks::has_bookmarked('collage', $CollageID),
    'subscriberCount'     => (int)$Subscribers,
    'torrentGroupIDList'  => $TorrentGroups
);

// torrent collage
$TorrentGroups = array();
$DB->query("
		SELECT
			ct.GroupID
		FROM collages_torrents AS ct
			JOIN torrents_group AS tg ON tg.ID = ct.GroupID
		WHERE ct.CollageID = '$CollageID'
		ORDER BY ct.Sort");
$GroupIDs = $DB->collect('GroupID');
$GroupList = Torrents::get_groups($GroupIDs);
foreach ($GroupIDs as $GroupID) {
    if (!empty($GroupList[$GroupID])) {
        $GroupDetails = Torrents::array_group($GroupList[$GroupID]);
        $TorrentList = array();
        foreach ($GroupDetails['Torrents'] as $Torrent) {
            $TorrentList[] = array(
                'torrentid'               => (int)$Torrent['ID'],
                'remasterYear'            => (int)$Torrent['RemasterYear'],
                'remasterTitle'           => $Torrent['RemasterTitle'],
                'scene'                   => ($Torrent['Scene'] == 1),
                'fileCount'               => (int)$Torrent['FileCount'],
                'size'                    => (int)$Torrent['Size'],
                'seeders'                 => (int)$Torrent['Seeders'],
                'leechers'                => (int)$Torrent['Leechers'],
                'snatched'                => (int)$Torrent['Snatched'],
                'freeTorrent'             => Torrents::torrent_freeleech($Torrent),
                'reported'                => (count(Reports::get_reports((int)$Torrent['ID'])) > 0),
                'time'                    => $Torrent['Time']
            );
        }
        $TorrentGroups[] = array(
            'id'              => $GroupDetails['GroupID'],
            'name'            => $GroupDetails['GroupName'],
            'year'            => $GroupDetails['GroupYear'],
            'categoryId'      => $GroupDetails['GroupCategoryID'],
            'tagList'         => $GroupDetails['TagList'],
            'releaseType'     => $GroupDetails['ReleaseType'],
            'wikiImage'       => $GroupDetails['WikiImage'],
            'torrents'        => $TorrentList
        );
    }
}
$JSON['torrentgroups'] = $TorrentGroups;

if (isset($SetCache)) {
    $CollageData = array(
        $Name,
        $Description,
        $CommentList,
        (bool)$Deleted,
        (int)$CollageCategoryID,
        (int)$CreatorID,
        (bool)$Locked,
        (int)$MaxGroups,
        (int)$MaxGroupsPerUser,
        $Updated,
        (int)$Subscribers
    );
    $Cache->cache_value($CacheKey, $CollageData, 3600);
}
ajax_json_success($JSON);
