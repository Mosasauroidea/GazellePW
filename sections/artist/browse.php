<?
//~~~~~~~~~~~ Main artist page ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~//
include(CONFIG['SERVER_ROOT'] . '/classes/torrenttable.class.php');

//For sorting tags
function compare($X, $Y) {
    return ($Y['count'] - $X['count']);
}

function sectionTitle($id) {
    global $ReleaseTypes;
    $text = isset(Lang::get('torrents', 'release_types')[$id]) ? Lang::get('torrents', 'release_types')[$id] : $ReleaseTypes[$id];
    return $text;
}

// Similar Artist Map
include(CONFIG['SERVER_ROOT'] . '/classes/artists_similar.class.php');

$UserVotes = Votes::get_user_votes($LoggedUser['ID']);

$ArtistID = $_GET['id'];
if (!is_number($ArtistID)) {
    error(0);
}


if (!empty($_GET['revisionid'])) { // if they're viewing an old revision
    $RevisionID = $_GET['revisionid'];
    if (!is_number($RevisionID)) {
        error(0);
    }
    $Data = $Cache->get_value("artist_{$ArtistID}_revision_$RevisionID", true);
} else { // viewing the live version
    $Data = $Cache->get_value("artist_$ArtistID", true);
    $RevisionID = false;
}

if ($Data) {
    list($K, list($Name, $Image, $Body, $IMDBID, $ChineseName, $Birthday, $PlaceOfBirth, $NumSimilar, $SimilarArray,,,)) = each($Data);
} else {
    if ($RevisionID) {
        $sql = "
			SELECT
				a.Name,
				wiki.Image,
				wiki.body,
				wiki.IMDBID,
                wiki.ChineseName,
                wiki.Birthday,
                wiki.PlaceOfBirth
			FROM wiki_artists AS wiki
				LEFT JOIN artists_group AS a ON wiki.RevisionID = a.RevisionID
			WHERE wiki.RevisionID = '$RevisionID' ";
    } else {
        $sql = "
			SELECT
				a.Name,
				wiki.Image,
				wiki.body,
				wiki.IMDBID,
                wiki.ChineseName,
                wiki.Birthday,
                wiki.PlaceOfBirth
			FROM artists_group AS a
				LEFT JOIN wiki_artists AS wiki ON wiki.RevisionID = a.RevisionID
			WHERE a.ArtistID = '$ArtistID' ";
    }
    $sql .= "
			GROUP BY a.ArtistID";
    $DB->query($sql);

    if (!$DB->has_results()) {
        error(404);
    }

    list($Name, $Image, $Body, $IMDBID, $ChineseName, $Birthday, $PlaceOfBirth) = $DB->next_record(MYSQLI_NUM, array(0));
}


//----------------- Build list and get stats

ob_start();

// Requests
$Requests = array();
if (empty($LoggedUser['DisableRequests'])) {
    $Requests = $Cache->get_value("artists_requests_$ArtistID");
    if (!is_array($Requests)) {
        $DB->query("
			SELECT
				r.ID,
				r.CategoryID,
				r.Title,
				r.Year,
				r.TimeAdded,
				COUNT(rv.UserID) AS Votes,
				SUM(rv.Bounty) AS Bounty
			FROM requests AS r
				LEFT JOIN requests_votes AS rv ON rv.RequestID = r.ID
				LEFT JOIN requests_artists AS ra ON r.ID = ra.RequestID
			WHERE ra.ArtistID = $ArtistID
				AND r.TorrentID = 0
			GROUP BY r.ID
			ORDER BY Votes DESC");

        if ($DB->has_results()) {
            $Requests = $DB->to_array('ID', MYSQLI_ASSOC, false);
        } else {
            $Requests = array();
        }
        $Cache->cache_value("artists_requests_$ArtistID", $Requests);
    }
}
$NumRequests = count($Requests);


if (($Importances = $Cache->get_value("artist_groups_$ArtistID")) === false) {
    $DB->query("
		SELECT
			DISTINCTROW ta.GroupID, ta.Importance, tg.Year
		FROM torrents_artists AS ta
			JOIN torrents_group AS tg ON tg.ID = ta.GroupID
		WHERE ta.ArtistID = '$ArtistID'
		ORDER BY tg.Year DESC, tg.Name DESC");
    $GroupIDs = $DB->collect('GroupID');
    $Importances = $DB->to_array(false, MYSQLI_BOTH, false);
    $Cache->cache_value("artist_groups_$ArtistID", $Importances, 0);
} else {
    $GroupIDs = array();
    foreach ($Importances as $Group) {
        $GroupIDs[] = $Group['GroupID'];
    }
}
if (count($GroupIDs) > 0) {
    $TorrentList = Torrents::get_groups($GroupIDs, true, true);
} else {
    $TorrentList = array();
}
$NumGroups = count($TorrentList);

// Deal with torrents without release types, which can end up here
// if they're uploaded with a non-grouping category ID
$UnknownRT = array_search('Unknown', $ReleaseTypes);
if ($UnknownRT === false) {
    $UnknownRT = 1025;
    $ReleaseTypes[$UnknownRT] = 'Unknown';
}

// Get list of used release types
$UsedReleases = array();
foreach ($Importances as $ID => $Group) {
    switch ($Importances[$ID]['Importance']) {
            // 导演
        case '1':
            $Importances[$ID]['ReleaseType'] = 1021;
            $DirectorAlbums = true;
            break;

            // 编剧
        case '2':
            $Importances[$ID]['ReleaseType'] = 1022;
            $WritterAlbums = true;
            break;

            // 制作人
        case '3':
            $Importances[$ID]['ReleaseType'] = 1023;
            $ProducerAlbums = true;
            break;
            // 作曲
        case '4':
            $Importances[$ID]['ReleaseType'] = 1024;
            $ComposerAlbums = true;
            break;

            // 摄影
        case '5':
            $Importances[$ID]['ReleaseType'] = 1025;
            $CameraAlbums = true;
            break;
        case '6':
            $Importances[$ID]['ReleaseType'] = 1026;
            $ActorAlbums = true;
            break;

        default:
            if (!isset($ReleaseTypes[$TorrentList[$Group['GroupID']]['ReleaseType']])) {
                $TorrentList[$Group['GroupID']]['ReleaseType'] = $UnknownRT;
            }
            $Importances[$ID]['ReleaseType'] = $TorrentList[$Group['GroupID']]['ReleaseType'];
    }

    $Importances[$ID]['GroupInfo'] = $TorrentList[$Group['GroupID']];
    if (!isset($UsedReleases[$Importances[$ID]['ReleaseType']])) {
        $UsedReleases[$Importances[$ID]['ReleaseType']] = true;
    }
    $Importances[$ID]['Sort'] = $ID;
}

if (!empty($DirectorAlbums)) {
    $ReleaseTypes[1021] = Lang::get('artist', '1021');
}
if (!empty($WritterAlbums)) {
    $ReleaseTypes[1022] = Lang::get('artist', '1022');
}
if (!empty($ProducerAlbums)) {
    $ReleaseTypes[1023] = Lang::get('artist', '1023');
}
if (!empty($ComposerAlbums)) {
    $ReleaseTypes[1024] = Lang::get('artist', '1024');
}
if (!empty($CameraAlbums)) {
    $ReleaseTypes[1025] = Lang::get('artist', '1025');
}
if (!empty($ActorAlbums)) {
    $ReleaseTypes[1026] = Lang::get('artist', '1026');
}
//Custom sorting for releases
if (!empty($LoggedUser['SortHide'])) {
    $SortOrder = array_flip(array_keys($LoggedUser['SortHide']));
} else {
    $SortOrder = $ReleaseTypes;
}
// If the $SortOrder array doesn't have all release types, put the missing ones at the end
$MissingTypes = array_diff_key($ReleaseTypes, $SortOrder);
if (!empty($MissingTypes)) {
    $MaxOrder = max($SortOrder);
    foreach (array_keys($MissingTypes) as $Missing) {
        $SortOrder[$Missing] = ++$MaxOrder;
    }
}
uasort($Importances, function ($A, $B) use ($SortOrder) {
    if ($SortOrder[$A['ReleaseType']] == $SortOrder[$B['ReleaseType']]) {
        return (($A['Sort'] < $B['Sort']) ? -1 : 1);
    }
    return (($SortOrder[$A['ReleaseType']] < $SortOrder[$B['ReleaseType']]) ? -1 : 1);
});
// Sort the anchors at the top of the page the same way as release types
$UsedReleases = array_flip(array_intersect_key($SortOrder, $UsedReleases));

reset($TorrentList);
if (!empty($UsedReleases)) { ?>
    <div class="BodyNavLinks">
        <?
        foreach ($UsedReleases as $ReleaseID) {
            $DisplayName = sectionTitle($ReleaseID);
        ?>
            <a href="#torrents_<?= $ReleaseID ?>" class="brackets"><?= $DisplayName ?></a>
        <?
        }
        if ($NumRequests > 0) {
        ?>
            <a href="#requests" class="brackets"><?= Lang::get('global', 'requests') ?></a>
        <? } ?>
    </div>
<? }

$NumTorrents = 0;
$NumSeeders = 0;
$NumLeechers = 0;
$NumSnatches = 0;

foreach ($TorrentList as $GroupID => $Group) {
    // $Tags array is for the sidebar on the right.
    $TorrentTags = new Tags($Group['TagList'], false);

    foreach ($Group['Torrents'] as $TorrentID => $Torrent) {
        $NumTorrents++;

        $Torrent['Seeders'] = (int)$Torrent['Seeders'];
        $Torrent['Leechers'] = (int)$Torrent['Leechers'];
        $Torrent['Snatched'] = (int)$Torrent['Snatched'];

        $NumSeeders += $Torrent['Seeders'];
        $NumLeechers += $Torrent['Leechers'];
        $NumSnatches += $Torrent['Snatched'];
    }
}

$ShowGroups = !isset($LoggedUser['TorrentGrouping']) || $LoggedUser['TorrentGrouping'] == 0;
$HideTorrents = ($ShowGroups ? '' : ' hidden');
$TorrentGroups = [];
foreach ($Importances as $Group) {
    $ReleaseType = $Group['ReleaseType'];
    $TorrentGroups[$ReleaseType][$Group['GroupID']] = $Group['GroupInfo'];
}
foreach ($TorrentGroups as $ReleaseType => $GroupInfo) {
    $DisplayName =  sectionTitle($ReleaseType);
?>
    <h3 id="torrents_<?= $ReleaseType ?>"><strong><?= $DisplayName ?></strong>&nbsp;(<a href="#" onclick="$('.torrent_table_<?= $ReleaseType ?>').gtoggle(true); return false;"><?= Lang::get('artist', 'view') ?></a>)</h3>
    <div class="Box">
        <div class="Box-body torrent_table_<?= $ReleaseType ?>" id="torrent_table_<?= $ID ?>">
            <?
            $tableRender = new TorrentGroupCoverTableView($GroupInfo);
            $tableRender->render();
            ?>
        </div>
    </div>
<?
}

$TorrentDisplayList = ob_get_clean();

//----------------- End building list and getting stats

// Comments (must be loaded before View::show_header so that subscriptions and quote notifications are handled properly)
list($NumComments, $Page, $Thread, $LastRead) = Comments::load('artist', $ArtistID);
View::show_header(($ChineseName ? '[' . $ChineseName . '] ' : '') . $Name, 'browse,bbcode,comments,voting,recommend,subscriptions', 'PageArtistHome');
?>
<div class="LayoutBody">
    <div class="BodyHeader">
        <div class="BodyNavLinks">
            <a href="artist.php?action=editrequest&amp;artistid=<?= $ArtistID ?>" class="brackets"><?= Lang::get('artist', 'editrequest') ?></a>
            <? if (check_perms('site_submit_requests')) { ?>
                <a href="requests.php?action=new&amp;artistid=<?= $ArtistID ?>" class="brackets"><?= Lang::get('artist', 're_torrents') ?></a>
                <?
            }
            if (check_perms('site_torrents_notify')) {
                if (($Notify = $Cache->get_value('notify_artists_' . $LoggedUser['ID'])) === false) {
                    $DB->query("
			SELECT ID, Artists
			FROM users_notify_filters
			WHERE UserID = '$LoggedUser[ID]'
				AND Label = 'Artist notifications'
			LIMIT 1");
                    $Notify = $DB->next_record(MYSQLI_ASSOC, false);
                    $Cache->cache_value('notify_artists_' . $LoggedUser['ID'], $Notify, 0);
                }
                if (stripos($Notify['Artists'], "|$Name|") === false) {
                ?>
                    <a href="artist.php?action=notify&amp;artistid=<?= $ArtistID ?>&amp;auth=<?= $LoggedUser['AuthKey'] ?>" class="brackets"><?= Lang::get('artist', 'torrents_notify') ?></a>
                <?  } else { ?>
                    <a href="artist.php?action=notifyremove&amp;artistid=<?= $ArtistID ?>&amp;auth=<?= $LoggedUser['AuthKey'] ?>" class="brackets"><?= Lang::get('artist', 'torrents_unnotify') ?></a>
                <?
                }
            }
            if (Bookmarks::has_bookmarked('artist', $ArtistID)) {
                ?>
                <a href="#" id="bookmarklink_artist_<?= $ArtistID ?>" onclick="Unbookmark('artist', <?= $ArtistID ?>, '<?= Lang::get('global', 'add_bookmark') ?>'); return false;" class="brackets"><?= Lang::get('global', 'remove_bookmark') ?></a>
            <?  } else { ?>
                <a href="#" id="bookmarklink_artist_<?= $ArtistID ?>" onclick="Bookmark('artist', <?= $ArtistID ?>, '<?= Lang::get('global', 'remove_bookmark') ?>'); return false;" class="brackets"><?= Lang::get('global', 'add_bookmark') ?></a>
            <?  } ?>
            <a href="#" id="subscribelink_artist<?= $ArtistID ?>" class="brackets" onclick="SubscribeComments('artist', <?= $ArtistID ?>);return false;"><?= Subscriptions::has_subscribed_comments('artist', $ArtistID) !== false ? Lang::get('torrents', 'unsubscribe') : Lang::get('torrents', 'subscribe') ?></a>
            <!--    <a href="#" id="recommend" class="brackets">Recommend</a> -->
            <?
            if (check_perms('site_edit_wiki')) {
            ?>
                <a href="artist.php?action=edit&amp;artistid=<?= $ArtistID ?>" class="brackets"><?= Lang::get('global', 'edit') ?></a>
            <?  } ?>
            <a href="artist.php?action=history&amp;artistid=<?= $ArtistID ?>" class="brackets"><?= Lang::get('artist', 'viewhistory') ?></a>
            <? if ($RevisionID && check_perms('site_edit_wiki')) { ?>
                <a href="artist.php?action=revert&amp;artistid=<?= $ArtistID ?>&amp;revisionid=<?= $RevisionID ?>&amp;auth=<?= $LoggedUser['AuthKey'] ?>" class="brackets"><?= Lang::get('artist', 'revert') ?></a>
            <?  } ?>
            <a href="artist.php?id=<?= $ArtistID ?>#artistcomments" class="brackets"><?= Lang::get('artist', 'artistcomments') ?></a>
            <? if (check_perms('site_delete_artist') && check_perms('torrents_delete')) { ?>
                <a href="artist.php?action=delete&amp;artistid=<?= $ArtistID ?>&amp;auth=<?= $LoggedUser['AuthKey'] ?>" class="brackets"><?= Lang::get('global', 'delete') ?></a>
            <?  } ?>
        </div>
    </div>
    <? /* Misc::display_recommend($ArtistID, "artist"); */ ?>
    <div class="MovieInfo MovieInfoArtist Box">
        <div class="MovieInfo-left">
            <img class="MovieInfo-poster <?= $Image ? '' : 'empty_photo' ?>" style="max-width: 250px;" src="<?= ImageTools::process($Image, true) ?>" alt="<?= $Name ?>" onclick="lightbox.init(this, 250);" />
        </div>
        <div class="MovieInfo-titleContainer">
            <div class="MovieInfo-title">
                <? echo $Name ?>
            </div>
            <a class="MovieInfo-subTitle" href="torrents.php?searchstr=<?= $Name ?>"><?= $ChineseName ?></a>
        </div>
        <div class="MovieInfo-tagContainer">
            <div class="MovieInfo-facts">
                <span class="MovieInfo-fact" data-tooltip="<?= Lang::get('artist', 'imdb_born_date') ?>">
                    <?= icon("MovieInfo/birthday") ?>
                    <span><?= $Birthday ?></span>
                </span>
                <span class="MovieInfo-fact" data-tooltip="<?= Lang::get('artist', 'imdb_born_area') ?>">
                    <?= icon("MovieInfo/place-of-birth") ?>
                    <span><?= $PlaceOfBirth ?></span>
                </span>
                <span class="MovieInfo-fact" data-tooltip="<?= Lang::get('artist', 'imdb_link') ?>">
                    <?= icon("imdb-gray") ?>
                    <a target="_blank" href="<? echo "https://www.imdb.com/name/" . $IMDBID ?>"><? print_r($IMDBID) ?></a>
                </span>
            </div>
            <div class="MovieInfo-synopsis" data-tooltip="<?= Lang::get('torrents', 'fold_tooltip') ?>">
                <p class="HtmlText">
                    <?= $Body ? Text::full_format($Body) : Lang::get('artist', 'empty_introduction_note') ?>
                </p>
            </div>
        </div>
    </div>
</div>
<div class="LayoutMainSidebar">
    <div class="Sidebar LayoutMainSidebar-sidebar">
        <div class="SidebarItemSearch SidebarItem Box">
            <div class="SidebarItem-header Box-header">
                <strong><?= Lang::get('artist', 'box_search') ?></strong>
            </div>
            <div class="SidebarItem-body Box-body">
                <form class="FormOneLine FormSearchFileLists" name="filelists" action="torrents.php">
                    <input type="hidden" name="artistname" value="<?= $Name ?>" />
                    <input type="hidden" name="action" value="advanced" />
                    <input class="Input" type="text" autocomplete="off" id="filelist" name="filelist" size="20" />
                    <input class="Button" type="submit" value="&gt;" />
                </form>
            </div>
        </div>
        <div class="SidebarItemTags SidebarItem Box">
            <div class="SidebarItem-header Box-header">
                <strong><?= Lang::get('artist', 'tag') ?></strong>
            </div>
            <ul class="Sidebar-list SidebarItem-body Box-body">
                <? Tags::format_top(50, 'torrents.php?taglist=', $Name, "Sidebar-item"); ?>
            </ul>
        </div>
        <?
        // Stats
        ?>
        <div class="SidebarItemStats SidebarItem Box">
            <div class="SidebarItem-header Box-header">
                <strong><?= Lang::get('artist', 'statistics') ?></strong>
            </div>
            <ul class="Sidebar-list SidebarItem-body Box-body">
                <li class="Sidebar-item"><?= Lang::get('artist', 'number_of_groups') ?>: <?= number_format($NumGroups) ?></li>
                <li class="Sidebar-item"><?= Lang::get('artist', 'number_of_torrents') ?>: <?= number_format($NumTorrents) ?></li>
                <li class="Sidebar-item"><?= Lang::get('artist', 'number_of_seeders') ?>: <?= number_format($NumSeeders) ?></li>
                <li class="Sidebar-item"><?= Lang::get('artist', 'number_of_leechers') ?>: <?= number_format($NumLeechers) ?></li>
                <li class="Sidebar-item"><?= Lang::get('artist', 'number_of_snatches') ?>: <?= number_format($NumSnatches) ?></li>
            </ul>
        </div>
        <?


        if (empty($SimilarArray)) {
            $DB->query("
		SELECT
			s2.ArtistID,
			a.Name,
			ass.Score,
			ass.SimilarID
		FROM artists_similar AS s1
			JOIN artists_similar AS s2 ON s1.SimilarID = s2.SimilarID AND s1.ArtistID != s2.ArtistID
			JOIN artists_similar_scores AS ass ON ass.SimilarID = s1.SimilarID
			JOIN artists_group AS a ON a.ArtistID = s2.ArtistID
		WHERE s1.ArtistID = '$ArtistID'
		ORDER BY ass.Score DESC
		LIMIT 30
	");
            $SimilarArray = $DB->to_array();
            $NumSimilar = count($SimilarArray);
        }
        ?>
        <div class="SidbarItemArtists SidebarItem Box">
            <div class="SidebarItem-header Box-header">
                <strong><?= Lang::get('artist', 'similarartist') ?></strong>
            </div>
            <ul class="SidebarList SidebarItem-body Box-body">
                <? if ($NumSimilar == 0) { ?>
                    <li class="SidebarList-item"><span style="font-style: italic;"><?= Lang::get('artist', 'similarartist_note') ?></span></li>
                <?
                }
                $First = true;
                foreach ($SimilarArray as $SimilarArtist) {
                    list($Artist2ID, $Artist2Name, $Score, $SimilarID) = $SimilarArtist;
                    $Score = $Score / 100;
                    if ($First) {
                        $Max = $Score + 1;
                        $First = false;
                    }

                    $FontSize = (ceil(((($Score - 2) / $Max - 2) * 4))) + 8;

                ?>
                    <li class="SidebarList-item">
                        <span data-tooltip="<?= $Score ?>"><a href="artist.php?id=<?= $Artist2ID ?>" style="float: left; display: block;"><?= $Artist2Name ?></a></span>
                        <div style="float: right; display: block; letter-spacing: -1px;">
                            <a href="artist.php?action=vote_similar&amp;artistid=<?= $ArtistID ?>&amp;similarid=<?= $SimilarID ?>&amp;way=up" class="brackets vote_artist_up" data-tooltip="<?= Lang::get('artist', 'vote_up_similar_artist_title') ?>">
                                <?= icon('vote-up') ?>
                            </a>
                            <a href="artist.php?action=vote_similar&amp;artistid=<?= $ArtistID ?>&amp;similarid=<?= $SimilarID ?>&amp;way=down" class="brackets vote_artist_down" data-tooltip="<?= Lang::get('artist', 'vote_down_similar_artist_title') ?>">
                                <?= icon('vote-down') ?>
                            </a>
                            <? if (check_perms('site_delete_tag')) { ?>
                                <span class="remove remove_artist"><a href="artist.php?action=delete_similar&amp;artistid=<?= $ArtistID ?>&amp;similarid=<?= $SimilarID ?>&amp;auth=<?= $LoggedUser['AuthKey'] ?>" class="brackets" data-tooltip="<?= Lang::get('artist', 'remove_similar_artist_title') ?>">X</a></span>
                            <?      } ?>
                        </div>
                        <br style="clear: both;" />
                    </li>
                <?      } ?>
            </ul>
        </div>
        <div class="SidebarItemArtistAdd SidebarItem Box">
            <div class="SidebarItem-header Box-header">
                <strong><?= Lang::get('artist', 'add_similarartist') ?></strong>
            </div>
            <div class="SidebarItem-body Box-body">
                <form class="FormOneLine FormAddSimilarArtist" name="similar_artists" action="artist.php" method="post">
                    <input type="hidden" name="action" value="add_similar" />
                    <input type="hidden" name="auth" value="<?= $LoggedUser['AuthKey'] ?>" />
                    <input type="hidden" name="artistid" value="<?= $ArtistID ?>" />
                    <input class="Input" type="text" autocomplete="off" id="artistsimilar" name="artistname" size="20" <? Users::has_autocomplete_enabled('other'); ?> />
                    <input class="Button" type="submit" value="+" />
                </form>
            </div>
        </div>
    </div>
    <div class="LayoutMainSidebar-main">
        <?

        echo $TorrentDisplayList;

        $Collages = $Cache->get_value("artists_collages_$ArtistID");
        if (!is_array($Collages)) {
            $DB->query("
		SELECT c.Name, c.NumTorrents, c.ID
		FROM collages AS c
			JOIN collages_artists AS ca ON ca.CollageID = c.ID
		WHERE ca.ArtistID = '$ArtistID'
			AND Deleted = '0'
			AND CategoryID = '7'");
            $Collages = $DB->to_array();
            $Cache->cache_value("artists_collages_$ArtistID", $Collages, 3600 * 6);
        }
        if (count($Collages) > 0) {
            if (count($Collages) > MAX_COLLAGES) {
                // Pick some at random
                $Range = range(0, count($Collages) - 1);
                shuffle($Range);
                $Indices = array_slice($Range, 0, MAX_COLLAGES);
                $SeeAll = ' <a href="#" onclick="$(\'.collage_rows\').gtoggle(); return false;">(See all)</a>';
            } else {
                $Indices = range(0, count($Collages) - 1);
                $SeeAll = '';
            }
        ?>
            <table class="TableCollage Table" id="collages">
                <tr class="Table-rowHeader">
                    <td class="Table-cell" width="85%">This artist is in <?= number_format(count($Collages)) ?> collage<?= ((count($Collages) > 1) ? 's' : '') ?><?= $SeeAll ?></td>
                    <td class="Table-cell"># artists</td>
                </tr>
                <?
                foreach ($Indices as $i) {
                    list($CollageName, $CollageArtists, $CollageID) = $Collages[$i];
                    unset($Collages[$i]);
                ?>
                    <tr class="Table-row">
                        <td class="Table-cell"><a href="collages.php?id=<?= $CollageID ?>"><?= $CollageName ?></a></td>
                        <td class="Table-cell"><?= number_format($CollageArtists) ?></td>
                    </tr>
                <?
                }
                foreach ($Collages as $Collage) {
                    list($CollageName, $CollageArtists, $CollageID) = $Collage;
                ?>
                    <tr class="Table-row hidden">
                        <td class="Table-cell"><a href="collages.php?id=<?= $CollageID ?>"><?= $CollageName ?></a></td>
                        <td class="Table-cell"><?= number_format($CollageArtists) ?></td>
                    </tr>
                <?          } ?>
            </table>
        <?
        }

        if ($NumRequests > 0) {

        ?>
            <div class="TableContainer">
                <table class="TableRequest Table" cellpadding="6" cellspacing="1" border="0" width="100%" id="requests">
                    <tr class="Table-rowHeader">
                        <td class="Table-cell" style="width: 48%;">
                            <?= Lang::get('artist', 'request_name') ?>
                        </td>
                        <td class="Table-cell">
                            <?= Lang::get('artist', 'vote') ?>
                        </td>
                        <td class="Table-cell">
                            <?= Lang::get('artist', 'bounty') ?>
                        </td>
                        <td class="Table-cell">
                            <?= Lang::get('artist', 'added') ?>
                        </td>
                    </tr>
                    <?
                    $Tags = Requests::get_tags(array_keys($Requests));
                    $Row = 'b';
                    foreach ($Requests as $RequestID => $Request) {
                        $CategoryName = $Categories[$Request['CategoryID'] - 1];
                        $Title = display_str($Request['Title']);
                        if ($CategoryName == 'Movies') {
                            $ArtistForm = Requests::get_artists($RequestID);
                            $ArtistLink = Artists::display_artists($ArtistForm, true, true);
                            $FullName = $ArtistLink . "<a href=\"requests.php?action=view&amp;id=$RequestID\"><span dir=\"ltr\">$Title</span> [$Request[Year]]</a>";
                        } elseif ($CategoryName == 'Audiobooks' || $CategoryName == 'Comedy') {
                            $FullName = "<a href=\"requests.php?action=view&amp;id=$RequestID\"><span dir=\"ltr\">$Title</span> [$Request[Year]]</a>";
                        } else {
                            $FullName = "<a href=\"requests.php?action=view&amp;id=$RequestID\" dir=\"ltr\">$Title</a>";
                        }

                        if (!empty($Tags[$RequestID])) {
                            $ReqTagList = array();
                            foreach ($Tags[$RequestID] as $TagID => $TagName) {
                                $ReqTagList[] = "<a href=\"requests.php?tags=$TagName\">" . display_str($TagName) . '</a>';
                            }
                            $ReqTagList = implode(', ', $ReqTagList);
                        } else {
                            $ReqTagList = '';
                        }
                    ?>
                        <tr class="TableRequest-row Table-row">
                            <td class="TableRequest-cellName Table-cell">
                                <?= $FullName ?>
                                <div class="tags"><?= $ReqTagList ?></div>
                            </td>
                            <td class="TableRequest-cellVotes Table-cell">
                                <span id="vote_count_<?= $RequestID ?>"><?= $Request['Votes'] ?></span>
                                <? if (check_perms('site_vote')) { ?>
                                    <input type="hidden" id="auth" name="auth" value="<?= $LoggedUser['AuthKey'] ?>" />
                                    &nbsp;&nbsp; <a href="javascript:globalapp.requestVote(0, <?= $RequestID ?>)" class="brackets">+</a>
                                <? } ?>
                            </td>
                            <td class="TableRequest-cellBounty Table-cell">
                                <span id="bounty_<?= $RequestID ?>"><?= Format::get_size($Request['Bounty']) ?></span>
                            </td>
                            <td class="TableRequest-cellCreatedAt TableRequest-cellTime Table-cell">
                                <?= time_diff($Request['TimeAdded']) ?>
                            </td>
                        </tr>
                    <?  } ?>
                </table>
            </div>
        <?
        }

        // Similar Artist Map

        if ($NumSimilar > 0) {
            if ($SimilarData = $Cache->get_value("similar_positions_$ArtistID")) {
                $Similar = new ARTISTS_SIMILAR($ArtistID, $Name);
                $Similar->load_data($SimilarData);
                if (!(current($Similar->Artists)->NameLength)) {
                    unset($Similar);
                }
            }
            if (empty($Similar) || empty($Similar->Artists)) {
                include(CONFIG['SERVER_ROOT'] . '/classes/image.class.php');
                $Img = new IMAGE;
                $Img->create(WIDTH, HEIGHT);
                $Img->color(255, 255, 255, 127);

                $Similar = new ARTISTS_SIMILAR($ArtistID, $Name);
                $Similar->set_up();
                $Similar->set_positions();
                $Similar->background_image();

                $SimilarData = $Similar->dump_data();

                $Cache->cache_value("similar_positions_$ArtistID", $SimilarData, 3600 * 24);
            }
        ?>
            <div id="similar_artist_map" class="box">
                <div id="flipper_head" class="head">
                    <strong id="flipper_title"><?= Lang::get('artist', 'similar_artist_map') ?></strong>
                    <a id="flip_to" class="brackets" href="#" onclick="flipView(); return false;"><?= Lang::get('artist', 'switch_to_cloud') ?></a>
                </div>
                <div id="flip_view_1" style="display: block; width: 100%; height: <?= (HEIGHT) ?>px; position: relative; background-image: url(static/similar/<?= ($ArtistID) ?>.png?t=<?= (time()) ?>);">
                    <?
                    $Similar->write_artists();
                    ?>
                </div>
                <div id="flip_view_2" style="display: none; width: <?= WIDTH ?>px; height: <?= HEIGHT ?>px;">
                    <canvas width="<?= WIDTH ?>px" height="<?= (HEIGHT - 20) ?>px" id="similarArtistsCanvas"></canvas>
                    <div id="artistTags" style="display: none;">
                        <ul>
                            <li></li>
                        </ul>
                    </div>
                    <strong style="margin-left: 10px;"><a id="currentArtist" href="#null"><?= Lang::get('artist', 'loading') ?></a></strong>
                </div>
            </div>

            <script type="text/javascript">
                //<![CDATA[
                var cloudLoaded = false;

                function flipView() {
                    var state = document.getElementById('flip_view_1').style.display == 'block';

                    if (state) {
                        document.getElementById('flip_view_1').style.display = 'none';
                        document.getElementById('flip_view_2').style.display = 'block';
                        document.getElementById('flipper_title').innerHTML = '<?= Lang::get('artist', 'similar_artist_cloud') ?>';
                        document.getElementById('flip_to').innerHTML = '<?= Lang::get('artist', 'switch_to_map') ?>';

                        if (!cloudLoaded) {
                            require("static/functions/tagcanvas.js", function() {
                                require("static/functions/artist_cloud.js", function() {});
                            });
                            cloudLoaded = true;
                        }
                    } else {
                        document.getElementById('flip_view_1').style.display = 'block';
                        document.getElementById('flip_view_2').style.display = 'none';
                        document.getElementById('flipper_title').innerHTML = '<?= Lang::get('artist', 'similar_artist_map') ?>';
                        document.getElementById('flip_to').innerHTML = '<?= Lang::get('artist', 'switch_to_cloud') ?>';
                    }
                }

                //TODO move this to global, perhaps it will be used elsewhere in the future
                //http://stackoverflow.com/questions/7293344/load-javascript-dynamically
                function require(file, callback) {
                    var script = document.getElementsByTagName('script')[0],
                        newjs = document.createElement('script');

                    // IE
                    newjs.onreadystatechange = function() {
                        if (newjs.readyState === 'loaded' || newjs.readyState === 'complete') {
                            newjs.onreadystatechange = null;
                            callback();
                        }
                    };
                    // others
                    newjs.onload = function() {
                        callback();
                    };
                    newjs.src = file;
                    script.parentNode.insertBefore(newjs, script);
                }
                //]]>
            </script>

        <? } /* if $NumSimilar > 0 */ ?>
        <?
        // --- Comments ---
        $Pages = Format::get_pages($Page, $NumComments, CONFIG['TORRENT_COMMENTS_PER_PAGE'], 9, '#comments');

        ?>
        <div id="artistcomments">
            <div class="BodyNavLinks"><a name="comments"></a>
                <?= ($Pages) ?>
            </div>
            <?

            //---------- Begin printing
            CommentsView::render_comments($Thread, $LastRead, "artist.php?id=$ArtistID");
            ?>
            <div class="BodyNavLinks">
                <?= ($Pages) ?>
            </div>
            <?
            View::parse('generic/reply/quickreply.php', array(
                'InputName' => 'pageid',
                'InputID' => $ArtistID,
                'Action' => 'comments.php?page=artist',
                'InputAction' => 'take_post',
                'SubscribeBox' => true
            ));
            ?>
        </div>
    </div>
</div>
<?
View::show_footer();


// Cache page for later use

if ($RevisionID) {
    $Key = "artist_$ArtistID" . "_revision_$RevisionID";
} else {
    $Key = "artist_$ArtistID";
}

$Data = array(array($Name, $Image, $Body, $IMDBID, $ChineseName, $Birthday, $PlaceOfBirth, $NumSimilar, $SimilarArray, array(), array()));

$Cache->cache_value($Key, $Data, 3600);
