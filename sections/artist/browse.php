<?
//~~~~~~~~~~~ Main artist page ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~//
include(CONFIG['SERVER_ROOT'] . '/classes/torrenttable.class.php');

//For sorting tags
function compare($X, $Y) {
    return ($Y['count'] - $X['count']);
}

function sectionTitle($id) {
    global $ReleaseTypes;
    $text = isset(t('server.torrents.release_types')[$id]) ? t('server.torrents.release_types')[$id] : $ReleaseTypes[$id];
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
    list($K, list($Name, $Image, $Body, $MainBody, $IMDBID, $SubName, $Birthday, $PlaceOfBirth, $NumSimilar, $SimilarArray,,,)) = each($Data);
} else {
    if ($RevisionID) {
        $sql = "
			SELECT
				wiki.Name,
				wiki.Image,
				wiki.Body,
				wiki.MainBody,
				wiki.IMDBID,
                wiki.SubName,
                a.Birthday,
                a.PlaceOfBirth
			FROM wiki_artists AS wiki
				LEFT JOIN artists_group AS a ON wiki.RevisionID = a.RevisionID
			WHERE wiki.RevisionID = '$RevisionID' ";
    } else {
        $sql = "
			SELECT
				a.Name,
				a.Image,
				a.Body,
                a.MainBody,
				a.IMDBID,
                a.SubName,
                a.Birthday,
                a.PlaceOfBirth
			FROM artists_group AS a
			WHERE a.ArtistID = '$ArtistID' ";
    }
    $sql .= "
			GROUP BY a.ArtistID";
    $DB->query($sql);

    if (!$DB->has_results()) {
        error(404);
    }

    list($Name, $Image, $Body, $MainBody, $IMDBID, $SubName, $Birthday, $PlaceOfBirth) = G::$DB->next_record(MYSQLI_NUM);
}

//----------------- Build list and get stats

ob_start();

$Requests = Requests::get_artist_requests($ArtistID);

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
            $CinematographerAlbums = true;
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
    $ReleaseTypes[1021] = t('server.artist.1021');
}
if (!empty($WritterAlbums)) {
    $ReleaseTypes[1022] = t('server.artist.1022');
}
if (!empty($ProducerAlbums)) {
    $ReleaseTypes[1023] = t('server.artist.1023');
}
if (!empty($ComposerAlbums)) {
    $ReleaseTypes[1024] = t('server.artist.1024');
}
if (!empty($CinematographerAlbums)) {
    $ReleaseTypes[1025] = t('server.artist.1025');
}
if (!empty($ActorAlbums)) {
    $ReleaseTypes[1026] = t('server.artist.1026');
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
        if ($NumRequests > 0) {
        ?>
            <a href="#requests" class="brackets"><?= t('server.common.requests') ?></a>
        <?
        }
        foreach ($UsedReleases as $ReleaseID) {
            $DisplayName = sectionTitle($ReleaseID);
        ?>
            <a href="#torrents_<?= $ReleaseID ?>" class="brackets"><?= $DisplayName ?></a>
        <?
        }
        ?>

    </div>
<? }

$NumTorrents = 0;
$NumSeeders = 0;
$NumLeechers = 0;
$NumSnatches = 0;

foreach ($TorrentList as $GroupID => $Group) {
    // $Tags array is for the sidebar on the right.
    $TorrentTags = new Tags(Torrents::tags($Group), true);

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


$TorrentDisplayList = ob_get_clean();

//----------------- End building list and getting stats

// Comments (must be loaded before View::show_header so that subscriptions and quote notifications are handled properly)
list($NumComments, $Page, $Thread, $LastRead) = Comments::load('artist', $ArtistID);

$ArtistHeaderName = Artists::display_artist(['Name' => $Name, 'SubName' => $SubName], false);
View::show_header($ArtistHeaderName, 'browse,bbcode,comments,voting,recommend,subscriptions', 'PageArtistHome');
?>
<div class="LayoutBody">
    <div class="BodyHeader">
        <div class="BodyHeader-nav TorrentViewWrapper">
            <?= t('server.common.artists') ?>
            <? renderTorrentViewButton(TorrentViewScene::Artist) ?>

        </div>
        <div class="BodyNavLinks">
            <? if (check_perms('site_edit_wiki')) { ?>
                <a href="artist.php?action=edit&amp;artistid=<?= $ArtistID ?>" class="brackets"><?= t('server.common.edit') ?></a>
            <? } ?>
            <a href="artist.php?action=editrequest&amp;artistid=<?= $ArtistID ?>" class="brackets"><?= t('server.artist.editrequest') ?></a>
            <?
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
                    <a href="artist.php?action=notify&amp;artistid=<?= $ArtistID ?>&amp;auth=<?= $LoggedUser['AuthKey'] ?>" class="brackets"><?= t('server.artist.torrents_notify') ?></a>
                <?  } else { ?>
                    <a href="artist.php?action=notifyremove&amp;artistid=<?= $ArtistID ?>&amp;auth=<?= $LoggedUser['AuthKey'] ?>" class="brackets"><?= t('server.artist.torrents_unnotify') ?></a>
                <?
                }
            }
            if (Bookmarks::has_bookmarked('artist', $ArtistID)) {
                ?>
                <a href="#" id="bookmarklink_artist_<?= $ArtistID ?>" onclick="Unbookmark('artist', <?= $ArtistID ?>, '<?= t('server.common.add_bookmark') ?>'); return false;" class="brackets"><?= t('server.common.remove_bookmark') ?></a>
            <?  } else { ?>
                <a href="#" id="bookmarklink_artist_<?= $ArtistID ?>" onclick="Bookmark('artist', <?= $ArtistID ?>, '<?= t('server.common.remove_bookmark') ?>'); return false;" class="brackets"><?= t('server.common.add_bookmark') ?></a>
            <?  } ?>
            <?
            if (Subscriptions::has_subscribed_comments('artist', $ArtistID) !== false) {
            ?>
                <a href="#" id="subscribelink_artist<?= $ArtistID ?>" class="brackets" onclick="SubscribeComments('artist', <?= $ArtistID ?>, '<?= t('server.torrents.subscribe') ?>');return false;"><?= t('server.torrents.unsubscribe') ?></a>
            <?
            } else { ?>
                <a href="#" id="subscribelink_artist<?= $ArtistID ?>" class="brackets" onclick="SubscribeComments('artist', <?= $ArtistID ?>,  '<?= t('server.torrents.unsubscribe') ?>');return false;"><?= t('server.torrents.subscribe') ?></a>
            <? } ?>

            <a href="artist.php?action=history&amp;artistid=<?= $ArtistID ?>" class="brackets"><?= t('server.artist.viewhistory') ?></a>
            <? if ($RevisionID && check_perms('site_edit_wiki')) { ?>
                <a href="artist.php?action=revert&amp;artistid=<?= $ArtistID ?>&amp;revisionid=<?= $RevisionID ?>&amp;auth=<?= $LoggedUser['AuthKey'] ?>" class="brackets"><?= t('server.artist.revert') ?></a>
            <?  } ?>
            <a href="artist.php?id=<?= $ArtistID ?>#artistcomments" class="brackets"><?= t('server.artist.artistcomments') ?></a>
            <? if (check_perms('site_delete_artist') && check_perms('torrents_delete')) { ?>
                <a href="artist.php?action=delete&amp;artistid=<?= $ArtistID ?>&amp;auth=<?= $LoggedUser['AuthKey'] ?>" class="brackets"><?= t('server.common.delete') ?></a>
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
                <?= Lang::choose_content($Name, $SubName) ?>
            </div>
            <div class="MovieInfo-subTitle"><?= Lang::choose_content($SubName, $Name) ?></div>
        </div>
        <div class="MovieInfo-tagContainer">
            <div class="MovieInfo-facts">
                <span class="MovieInfo-fact" data-tooltip="<?= t('server.artist.imdb_born_date') ?>">
                    <?= icon("MovieInfo/birthday") ?>
                    <span><?= $Birthday ? $Birthday : '--' ?></span>
                </span>
                <span class="MovieInfo-fact" data-tooltip="<?= t('server.artist.imdb_born_area') ?>">
                    <?= icon("MovieInfo/place-of-birth") ?>
                    <span><?= $PlaceOfBirth ? $PlaceOfBirth : '--' ?></span>
                </span>
                <span class="MovieInfo-fact" data-tooltip="<?= t('server.artist.imdb_link') ?>">
                    <?= icon("imdb-gray") ?>
                    <a target="_blank" href="<? echo "https://www.imdb.com/name/" . $IMDBID ?>"><?= $IMDBID ? $IMDBID : '--' ?></a>
                </span>
            </div>
            <div class="MovieInfo-synopsis" data-tooltip="<?= t('server.torrents.fold_tooltip') ?>">
                <div class="HtmlText">
                    <?
                    $Content = Text::full_format(Lang::choose_content($MainBody, $Body));
                    if ($Content) {
                        View::long_text('movie_info_synopsis', display_str($Content), 5);
                    } else {
                        echo '<i>' . t('server.artist.empty_introduction_note') . '</i>';
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
    <div class="LayoutMainSidebar">
        <div class="Sidebar LayoutMainSidebar-sidebar">
            <div class="SidebarItemSearch SidebarItem Box">
                <div class="SidebarItem-header Box-header">
                    <strong><?= t('server.artist.box_search') ?></strong>
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
                    <strong><?= t('server.artist.tag') ?></strong>
                </div>
                <ul class="Sidebar-list SidebarItem-body Box-body">
                    <? Tags::format_top(50, 'torrents.php?action=advanced&taglist=', $Name, "Sidebar-item"); ?>
                </ul>
            </div>
            <?
            // Stats
            ?>
            <div class="SidebarItemStats SidebarItem Box">
                <div class="SidebarItem-header Box-header">
                    <strong><?= t('server.artist.statistics') ?></strong>
                </div>
                <ul class="Sidebar-list SidebarItem-body Box-body">
                    <li class="Sidebar-item"><?= t('server.artist.number_of_groups') ?>: <?= number_format($NumGroups) ?></li>
                    <li class="Sidebar-item"><?= t('server.artist.number_of_torrents') ?>: <?= number_format($NumTorrents) ?></li>
                    <li class="Sidebar-item"><?= t('server.artist.number_of_seeders') ?>: <?= number_format($NumSeeders) ?></li>
                    <li class="Sidebar-item"><?= t('server.artist.number_of_leechers') ?>: <?= number_format($NumLeechers) ?></li>
                    <li class="Sidebar-item"><?= t('server.artist.number_of_snatches') ?>: <?= number_format($NumSnatches) ?></li>
                </ul>
            </div>
            <?


            if (empty($SimilarArray)) {
                $DB->query("
		SELECT
			s2.ArtistID,
			a.Name,
            a.SubName,
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
                    <strong><?= t('server.artist.similarartist') ?></strong>
                </div>
                <ul class="SidebarList SidebarItem-body Box-body">
                    <? if ($NumSimilar == 0) { ?>
                        <li class="SidebarList-item"><span style="font-style: italic;"><?= t('server.artist.similarartist_note') ?></span></li>
                    <?
                    }
                    $First = true;
                    foreach ($SimilarArray as $SimilarArtist) {
                        list($Artist2ID, $Artist2Name, $Artist2SubName, $Score, $SimilarID) = $SimilarArtist;
                        $Score = $Score / 100;
                        if ($First) {
                            $Max = $Score + 1;
                            $First = false;
                        }

                        $FontSize = (ceil(((($Score - 2) / $Max - 2) * 4))) + 8;
                        $ArtistDisplayName = Artists::display_artist(['Name' => $Artist2Name, 'SubName' => $Artist2SubName, 'ArtistID' => $Artist2ID])

                    ?>
                        <li class="SidebarList-item u-hoverToShow-hover">
                            <span data-tooltip="<?= $Score ?>"><?= $ArtistDisplayName ?></span>
                            <div class="SidebarList-actions">
                                <? if (check_perms('site_delete_tag')) { ?>
                                    <a class="SidebarList-action u-hoverToShow-hide remove remove_artist" href="artist.php?action=delete_similar&amp;artistid=<?= $ArtistID ?>&amp;similarid=<?= $SimilarID ?>&amp;auth=<?= $LoggedUser['AuthKey'] ?>" class="brackets" data-tooltip="<?= t('server.artist.remove_similar_artist_title') ?>">
                                        <?= icon('remove') ?>
                                    </a>
                                <?      } ?>

                                <a href=" artist.php?action=vote_similar&amp;artistid=<?= $ArtistID ?>&amp;similarid=<?= $SimilarID ?>&amp;way=up" class="SidebarList-action brackets vote_artist_up" data-tooltip="<?= t('server.artist.vote_up_similar_artist_title') ?>">
                                    <?= icon('vote-up') ?>
                                </a>
                                <a href="artist.php?action=vote_similar&amp;artistid=<?= $ArtistID ?>&amp;similarid=<?= $SimilarID ?>&amp;way=down" class="SidebarList-action brackets vote_artist_down" data-tooltip="<?= t('server.artist.vote_down_similar_artist_title') ?>">
                                    <?= icon('vote-down') ?>
                                </a>
                            </div>
                            <br style="clear: both;" />
                        </li>
                    <?      } ?>
                </ul>
            </div>
            <div class="SidebarItemArtistAdd SidebarItem Box">
                <div class="SidebarItem-header Box-header">
                    <strong><?= t('server.artist.add_similarartist') ?></strong>
                </div>
                <div class="SidebarItem-body Box-body">
                    <form class="FormAddSimilarArtist" name="similar_artists" action="artist.php" method="post">
                        <input type="hidden" name="action" value="add_similar" />
                        <input type="hidden" name="auth" value="<?= $LoggedUser['AuthKey'] ?>" />
                        <input type="hidden" name="artistid" value="<?= $ArtistID ?>" />
                        <div class="Form-row FormOneLine">
                            <input class="Input" type="text" placeholder="<?= t('server.artist.search_auto_fill') ?>" autocomplete="off" id="artistsimilar" size="20" <? Users::has_autocomplete_enabled('other'); ?> />
                        </div>
                        <div class="Form-row FormOneLine">
                            <input type="text" placeholder=" <?= t('server.artist.artist_id') ?>" class="Input" id="similar_artistid" name="similar_artistid" />
                            <input class="Button" type="submit" value="+" />
                        </div>
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
                    $SeeAll = ' <a href="#" onclick="$(\'.collage_rows\').gtoggle(); return false;">(' . t('server.common.see_full') . ')</a>';
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
                <div class="Group" id="requests">
                    <div class="Group-header">
                        <div class="Group-headerTitle">
                            <?= t('server.common.requests') ?>
                        </div>
                    </div>
                    <div class="Group-body">
                        <div class="TableContainer">
                            <table class="TableRequest Table" cellpadding="6" cellspacing="1" border="0" width="100%">
                                <tr class="Table-rowHeader">
                                    <td class="Table-cell">
                                        <?= t('server.artist.request_name') ?>
                                    </td>
                                    <td class="Table-cell">
                                        <?= t('server.requests.request_type') ?>
                                    </td>
                                    <td class="Table-cell TableRequest-cellValue">
                                        <?= t('server.artist.vote') ?>
                                    </td>
                                    <td class="Table-cell TableRequest-cellValue">
                                        <?= t('server.artist.bounty') ?>
                                    </td>
                                    <td class="Table-cell TableRequest-cellValue">
                                        <?= t('server.artist.added') ?>
                                    </td>
                                </tr>
                                <?
                                foreach ($Requests as $Request) {
                                    $RequestVotes = Requests::get_votes_array($Request['ID']);
                                    $RequestID = $Request['ID'];
                                    $RequestType = $Request['RequestType'];
                                    $RequestName = Torrents::group_name($Request, false);
                                    $FullName = "<a href=\"requests.php?action=view&amp;id=$RequestID\">$RequestName</a>";
                                    $Tags = $Request['Tags'];
                                ?>
                                    <tr class="Table-row">
                                        <td class="TableRequest-cellName Table-cell">
                                            <?= $FullName ?>
                                            <div class="torrent_info">
                                                <?
                                                if ($RequestType == 2) {
                                                ?>
                                                    <a href="<?= $Request['SourceTorrent'] ?>"><?= str_replace('|', ', ', $Request['CodecList']) . ' / ' . str_replace('|', ', ', $Request['SourceList']) . ' / ' . str_replace('|', ', ', $Request['ResolutionList']) . ' / ' . str_replace('|', ', ', $Request['ContainerList']) ?></a>
                                                <?
                                                } else {
                                                ?>
                                                    <?= str_replace('|', ', ', $Request['CodecList']) . ' / ' . str_replace('|', ', ', $Request['SourceList']) . ' / ' . str_replace('|', ', ', $Request['ResolutionList']) . ' / ' . str_replace('|', ', ', $Request['ContainerList']) ?>
                                                <?

                                                }
                                                ?>
                                            </div>
                                        </td>
                                        <td class="TableRequest-cellType Table-cell">
                                            <?= $RequestType  == 2 ? t('server.requests.seed_torrent') : t('server.requests.new_torrent') ?>
                                        </td>
                                        <td class="TableRequest-cellVotes Table-cell TableRequest-cellValue">
                                            <span id="vote_count_<?= $Request['ID'] ?>"><?= count($RequestVotes['Voters']) ?></span>
                                            <? if (check_perms('site_vote')) { ?>
                                                &nbsp;&nbsp; <a href="javascript:globalapp.requestVote(0, <?= $Request['ID'] ?>)" class="brackets">+</a>
                                            <?          } ?>
                                        </td>
                                        <td class="TableRequest-cellBounty Table-cell TableRequest-cellValue">
                                            <?= Format::get_size($RequestVotes['TotalBounty']) ?>
                                        </td>
                                        <td class="TableRequest-cellCreatedAt TableRequest-cellValue Table-cell">
                                            <?= time_diff($Request['TimeAdded'], 1) ?>
                                        </td>
                                    </tr>
                                <?  } ?>
                            </table>
                        </div>
                    </div>
                </div>
            <?
            }
            ?>

            <?
            foreach ($TorrentGroups as $ReleaseType => $GroupInfo) {
                $DisplayName = sectionTitle($ReleaseType);
            ?>
                <div class="Group">
                    <div class="Group-header">
                        <div class="Group-headerTitle">
                            <div id="torrents_<?= $ReleaseType ?>">
                                <?= $DisplayName ?>
                            </div>
                        </div>
                        <div class="Group-headerActions">
                            <a href="#" onclick="globalapp.toggleAny(event, '.torrent_table_<?= $ReleaseType ?>');return false;">
                                <span class="u-toggleAny-show u-hidden"><?= t('server.common.show') ?></span>
                                <span class="u-toggleAny-hide"><?= t('server.common.hide') ?></span>
                            </a>
                        </div>
                    </div>
                    <div class="Group-body torrent_table_<?= $ReleaseType ?>" id="torrent_table_<?= $ID ?>">
                        <?
                        $tableRender =  newGroupTorrentView(TorrentViewScene::Artist, $GroupInfo);
                        $tableRender->render();
                        ?>
                    </div>
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
                if (false) {
                ?>
                    <div id="similar_artist_map" class="box">
                        <div id="flipper_head" class="head">
                            <strong id="flipper_title"><?= t('server.artist.similar_artist_map') ?></strong>
                            <a id="flip_to" class="brackets" href="#" onclick="flipView(); return false;"><?= t('server.artist.switch_to_cloud') ?></a>
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
                            <strong style="margin-left: 10px;"><a id="currentArtist" href="#null"><?= t('server.artist.loading') ?></a></strong>
                        </div>
                    </div>
                <?
                }
                ?>

                <script type="text/javascript">
                    //<![CDATA[
                    var cloudLoaded = false;

                    function flipView() {
                        var state = document.getElementById('flip_view_1').style.display == 'block';

                        if (state) {
                            document.getElementById('flip_view_1').style.display = 'none';
                            document.getElementById('flip_view_2').style.display = 'block';
                            document.getElementById('flipper_title').innerHTML = '<?= t('server.artist.similar_artist_cloud') ?>';
                            document.getElementById('flip_to').innerHTML = '<?= t('server.artist.switch_to_map') ?>';

                            if (!cloudLoaded) {
                                require("static/functions/tagcanvas.js", function() {
                                    require("static/functions/artist_cloud.js", function() {});
                                });
                                cloudLoaded = true;
                            }
                        } else {
                            document.getElementById('flip_view_1').style.display = 'block';
                            document.getElementById('flip_view_2').style.display = 'none';
                            document.getElementById('flipper_title').innerHTML = '<?= t('server.artist.similar_artist_map') ?>';
                            document.getElementById('flip_to').innerHTML = '<?= t('server.artist.switch_to_cloud') ?>';
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
            <div class="Group">
                <div class="Group-header">
                    <div class="Group-headerTitle">
                        <?= t('server.artist.artistcomments') ?>
                    </div>
                </div>
                <div id="artistcomments" class="Group-body">
                    <? View::pages($Pages) ?>
                    <?

                    //---------- Begin printing
                    CommentsView::render_comments($Thread, $LastRead, "artist.php?id=$ArtistID");
                    ?>
                    <? View::pages($Pages) ?>
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

$Data = array(array($Name, $Image, $Body, $MainBody, $IMDBID, $SubName, $Birthday, $PlaceOfBirth, $NumSimilar, $SimilarArray, array(), array()));

$Cache->cache_value($Key, $Data, 3600);
