<?php

use Gazelle\Torrent\TorrentSlot;
use Gazelle\Torrent\TorrentSlotType;

include(SERVER_ROOT . '/classes/torrenttable.class.php');
function compare($X, $Y) {
    return ($Y['score'] - $X['score']);
}
header('Access-Control-Allow-Origin: *');

define('MAX_PERS_COLLAGES', 3); // How many personal collages should be shown by default
define('MAX_COLLAGES', 5); // How many normal collages should be shown by default

$GroupID = ceil($_GET['id']);
if (!empty($_GET['revisionid']) && is_number($_GET['revisionid'])) {
    $RevisionID = $_GET['revisionid'];
} else {
    $RevisionID = 0;
}

include(SERVER_ROOT . '/sections/torrents/functions.php');
$TorrentCache = Torrents::get_group($GroupID, true, $RevisionID);
$TorrentDetails = $TorrentCache;
$TorrentList = $TorrentCache['Torrents'];
$View = isset($_GET['view']) ? $_GET['view'] : '';

// Group details
$WikiBody = $TorrentDetails['WikiBody'];
$WikiImage = $TorrentDetails['WikiImage'];
$GropuID = $TorrentDetails['ID'];
$IMDBID = $TorrentDetails['IMDBID'];
$IMDBRating = $TorrentDetails['IMDBRating'];
$Duration = $TorrentDetails['Duration'];
$ReleaseDate = $TorrentDetails['ReleaseDate'];
$Region = $TorrentDetails['Region'];
$Language = $TorrentDetails['Language'];
$RTRating = $TorrentDetails['RTRating'];
$DoubanRating = $TorrentDetails['DoubanRating'];
$IMDBVote = $TorrentDetails['IMDBVote'];
$DoubanVote = $TorrentDetails['DoubanVote'];
$DoubanID  = $TorrentDetails['DoubanID'];
$RTTitle = $TorrentDetails['RTTitle'];
$GroupName = $TorrentDetails['Name'];
$GroupYear = $TorrentDetails['Year'];
$ReleaseType = $TorrentDetails['ReleaseType'];
$GroupCategoryID = $TorrentDetails['CategoryID'];
$GroupTime = $TorrentDetails['Time'];
$TorrentTags = $TorrentDetails['TorrentTags'];
$TorrentTagIDs = $TorrentDetails['TorrentTagIDs'];
$TorrentTagUserIDs = $TorrentDetails['TorrentTagUserIDs'];
$TagPositiveVotes = $TorrentDetails['TagPositiveVotes'];
$TagNegativeVotes = $TorrentDetails['TagNegativeVotes'];
$SubName = $TorrentDetails['SubName'];
$RawName = Torrents::group_name($TorrentDetails, false);
$DisplayName = "<span dir=\"ltr\">$RawName</span>";

$Requests = get_group_requests($GroupID);
$HasRequest = false;
if (empty($LoggedUser['DisableRequests']) && count($Requests) > 0) {
    $HasRequest = true;
}
$WikiBody = Text::full_format($WikiBody);

$Artists = Artists::get_artist($GroupID);
$Director = null;
foreach ($Artists[1] as $ID => $Artist) {
    $Director = $Artist;
    break;
}

$Title = $RawName;
$AltName = $RawName;

$Tags = array();
$TagNames = array();
if ($TorrentTags != '') {
    $TorrentTags = explode('|', $TorrentTags);
    $TorrentTagIDs = explode('|', $TorrentTagIDs);
    $TorrentTagUserIDs = explode('|', $TorrentTagUserIDs);
    $TagPositiveVotes = explode('|', $TagPositiveVotes);
    $TagNegativeVotes = explode('|', $TagNegativeVotes);

    foreach ($TorrentTags as $TagKey => $TagName) {
        $Tags[$TagKey]['name'] = $TagName;
        $Tags[$TagKey]['score'] = ($TagPositiveVotes[$TagKey] - $TagNegativeVotes[$TagKey]);
        $Tags[$TagKey]['id'] = $TorrentTagIDs[$TagKey];
        $Tags[$TagKey]['userid'] = $TorrentTagUserIDs[$TagKey];
        $TagNames[] = $TagName;
    }
    uasort($Tags, 'compare');
}



$CoverArt = $Cache->get_value("torrents_cover_art_$GroupID");
if (!$CoverArt) {
    $DB->query("
		SELECT ID, Image, Summary, UserID, Time
		FROM cover_art
		WHERE GroupID = '$GroupID'
		ORDER BY Time ASC");
    $CoverArt = array();
    $CoverArt = $DB->to_array();
    if ($DB->has_results()) {
        $Cache->cache_value("torrents_cover_art_$GroupID", $CoverArt, 0);
    }
}

// Comments (must be loaded before View::show_header so that subscriptions and quote notifications are handled properly)
list($NumComments, $Page, $Thread, $LastRead) = Comments::load('torrents', $GroupID);


$ThumbCounts = Torrents::get_thumb_counts($GroupID);
$BonusSended = Torrents::get_bonus_sended($GroupID);


// Start output
View::show_header($Title, 'browse,comments,torrent,bbcode,recommend,cover_art,subscriptions,sendbonus,thumb', 'PageTorrentShow');
?>
<div class="LayoutBody">
    <div class="BodyHeader">
        <div class="BodyNavLinks">
            <? if (check_perms('site_edit_wiki')) { ?>
                <a href="torrents.php?action=editgroup&amp;groupid=<?= $GroupID ?>" class="brackets"><?= Lang::get('torrents', 'editgroup') ?></a>
            <?  } ?>
            <a href="torrents.php?action=editrequest&amp;groupid=<?= $GroupID ?>" class="brackets"><?= Lang::get('torrents', 'editrequest') ?></a>
            <a href="torrents.php?action=history&amp;groupid=<?= $GroupID ?>" class="brackets"><?= Lang::get('torrents', 'viewhistory') ?></a>
            <? if ($RevisionID && check_perms('site_edit_wiki')) { ?>
                <a href="torrents.php?action=revert&amp;groupid=<?= $GroupID ?>&amp;revisionid=<?= $RevisionID ?>&amp;auth=<?= $LoggedUser['AuthKey'] ?>" class="brackets"><?= Lang::get('torrents', 'revert') ?></a>
            <?
            }
            if (Bookmarks::has_bookmarked('torrent', $GroupID)) {
            ?>
                <a href="#" id="bookmarklink_torrent_<?= $GroupID ?>" class="remove_bookmark brackets" onclick="Unbookmark('torrent', <?= $GroupID ?>, '<?= Lang::get('global', 'add_bookmark') ?>'); return false;"><?= Lang::get('global', 'remove_bookmark') ?></a>
            <?  } else { ?>
                <a href="#" id="bookmarklink_torrent_<?= $GroupID ?>" class="add_bookmark brackets" onclick="Bookmark('torrent', <?= $GroupID ?>, '<?= Lang::get('global', 'remove_bookmark') ?>'); return false;"><?= Lang::get('global', 'add_bookmark') ?></a>
            <?  } ?>
            <a href="#" id="subscribelink_torrents<?= $GroupID ?>" class="brackets" onclick="SubscribeComments('torrents', <?= $GroupID ?>); return false;"><?= Subscriptions::has_subscribed_comments('torrents', $GroupID) !== false ? Lang::get('torrents', 'unsubscribe') : Lang::get('torrents', 'subscribe') ?></a>
            <!-- <a href="#" id="recommend" class="brackets">Recommend</a> -->
            <?
            if ($Categories[$GroupCategoryID - 1] == 'Movies') { ?>
                <a href="upload.php?groupid=<?= $GroupID ?>" class="brackets"><?= Lang::get('torrents', 'add_format') ?></a>
            <?
            }
            if (check_perms('site_submit_requests')) { ?>
                <a href="requests.php?action=new&amp;groupid=<?= $GroupID ?>" class="brackets"><?= Lang::get('torrents', 'req_format') ?></a>
            <?  } ?>
            <a href="torrents.php?action=grouplog&amp;groupid=<?= $GroupID ?>" class="brackets"><?= Lang::get('torrents', 'viewlog') ?></a>
        </div>
    </div>
    <!-- IMDB -->
    <div class="MovieInfo MovieInfoMovie Box">
        <div class="MovieInfo-left">
            <img class="MovieInfo-poster" src="<?= ImageTools::process($WikiImage) ?>" onclick="lightbox.init(this, $(this).width());">
        </div>
        <div class="MovieInfo-titleContainer">
            <a class="MovieInfo-title" href="/torrents.php?id=<?= $GroupID ?>">
                <?= $GroupName ?>
            </a>
            <i class="MovieInfo-year">(<? print_r($GroupYear) ?>)</i>
            <? if ($SubName) {
                echo "<div class='MovieInfo-subTitle'><a href=\"torrents.php?searchstr=" . $SubName . "\">$SubName</a></div>";
            } ?>
        </div>
        <div class="MovieInfo-tagContainer">
            <div class="MovieInfo-facts">
                <a class="MovieInfo-fact" data-tooltip="<?= Lang::get('global', 'imdb_rating') ?>, <?= $IMDBVote . ' ' . Lang::get('torrents', 'movie_votes') ?>" target="_blank" href="https://www.imdb.com/title/<? print_r($IMDBID) ?>">
                    <?= icon('imdb') ?>
                    <span><?= !empty($IMDBRating) ? sprintf("%.1f", $IMDBRating) : '--' ?></span>
                </a>
                <a class="MovieInfo-fact" data-tooltip="<?= Lang::get('global', 'douban_rating') ?>, <?= ($DoubanVote ? $DoubanVote : '?') . ' ' . Lang::get('torrents', 'movie_votes') ?>" target="_blank" href="https://movie.douban.com/subject/<?= $DoubanID ?>/">
                    <?= icon('douban') ?>
                    <span><?= !empty($DoubanRating) ? sprintf("%.1f", $DoubanRating) : '--' ?></span>
                </a>
                <a class="MovieInfo-fact <?= empty($RTRating) ? 'lack_of_info' : '' ?>" data-tooltip="<?= Lang::get('global', 'rt_rating') ?>" target="_blank" href="https://www.rottentomatoes.com/m/<?= $RTTitle ?>">
                    <?= icon('rotten-tomatoes') ?>
                    <span><?= !empty($RTRating) ? $RTRating : '--' ?></span>
                </a>
                <a class="MovieInfo-fact" data-tooltip="<?= Lang::get('upload', 'director') ?>" href="/artist.php?id=<?= $Director['id'] ?>" dir="ltr">
                    <?= icon('movie-director') ?>
                    <span><?= Artists::display_artist($Director, false) ?></span>
                </a>
                <? if (!empty($Duration)) { ?>
                    <span class="MovieInfo-fact" data-tooltip="<?= Lang::get('torrents', 'imdb_runtime') ?>">
                        <?= icon('movie-runtime') ?>
                        <span><?= $Duration . " min" ?></span>
                    </span>
                <?  } ?>
                <? if (!empty($Region)) { ?>
                    <span class="MovieInfo-fact" data-tooltip="<?= Lang::get('torrents', 'imdb_region') ?>">
                        <?= icon('movie-country') ?>
                        <span><? print_r(implode(', ', array_slice(explode(',', $Region), 0, 2))) ?></span>
                    </span>
                <?  } ?>
                <? if (!empty($Language)) { ?>
                    <span class="MovieInfo-fact" data-tooltip="<?= Lang::get('torrents', 'imdb_language') ?>">
                        <?= icon('movie-language') ?>
                        <span><? print_r(implode(', ', array_slice(explode(',', $Language), 0, 2))) ?></span>
                    </span>
                <?  } ?>
            </div>
            <div class="MovieInfo-tags">
                <? foreach ($TagNames as $TagName) { ?>
                    <span class="MovieInfo-tag" data-tooltip="<?= Lang::get('torrents', 'tag') ?>">
                        <?= $TagName ?>
                    </span>
                <? } ?>
            </div>
        </div>

        <div class="MovieInfo-synopsis" data-tooltip="<?= Lang::get('torrents', 'fold_tooltip') ?>">
            <p class="HtmlText">
                <? print_r($WikiBody) ?>
            </p>
        </div>
        <div class="MovieInfo-artists">
            <?
            for ($i = 0; $i < 10 && $i < count($Artists[6]); $i++) {
            ?>
                <a class="MovieInfo-artist" href="<? echo " artist.php?id=" . $Artists[6][$i]['id'] ?>">
                    <img class="MovieInfo-artistPhoto <?= $Artists[6][$i]['image'] ? '' : 'default_photo' ?>" src="<?= ImageTools::process($Artists[6][$i]['image']) ?>">
                    <div class="MovieInfo-artistName" data-tooltip="<? echo $Artists[6][$i]['name'] ?>"><? echo $Artists[6][$i]['name'] ?></div>
                    <div class="MovieInfo-artistSubName" data-tooltip="<? echo $Artists[6][$i]['cname'] ?>"><? echo $Artists[6][$i]['cname'] ?></div>
                </a>
            <?
            }
            ?>
        </div>
    </div>
    <div class="LayoutMainSidebar">
        <div class="Sidebar LayoutMainSidebar-sidebar">
            <div class="SidebarTags SidebarItem Box">
                <div class="SidebarItem-header Box-header">
                    <span><?= Lang::get('torrents', 'tag') ?></span>
                    <?
                    $DeletedTag = $Cache->get_value("deleted_tags_$GroupID" . '_' . $LoggedUser['ID']);
                    if (!empty($DeletedTag)) { ?>
                        <form style="display: none;" id="undo_tag_delete_form" name="tags" action="torrents.php" method="post">
                            <input type="hidden" name="action" value="add_tag" />
                            <input type="hidden" name="auth" value="<?= $LoggedUser['AuthKey'] ?>" />
                            <input type="hidden" name="groupid" value="<?= $GroupID ?>" />
                            <input type="hidden" name="tagname" value="<?= $DeletedTag ?>" />
                            <input type="hidden" name="undo" value="true" />
                        </form>
                        <a class="brackets" href="#" onclick="$('#undo_tag_delete_form').raw().submit(); return false;"><?= Lang::get('torrents', 'undo_delete') ?></a>

                    <?              } ?>
                </div>

                <?
                if (count($Tags) > 0) {
                ?>
                    <ul class="SidebarList SidebarItem-body Box-body">
                        <? foreach ($Tags as $TagKey => $Tag) { ?>
                            <li class="SidebarList-item u-hoverToShow-hover">
                                <a href="torrents.php?taglist=<?= $Tag['name'] ?>"><?= display_str($Tag['name']) ?></a>
                                <div class="SidebarList-actions">
                                    <? if (check_perms('users_warn')) { ?>
                                        <a class="SidebarList-action u-hoverToShow-hide" href="user.php?id=<?= $Tag['userid'] ?>" data-tooltip="<?= Lang::get('torrents', 'view_the_profile_of_the_user_that_added_this_tag') ?>">
                                            <?= icon('user') ?>
                                        </a>
                                    <? } ?>
                                    <? if (empty($LoggedUser['DisableTagging']) && check_perms('site_delete_tag')) { ?>
                                        <a class="SidebarList-action  u-hoverToShow-hide" href="torrents.php?action=delete_tag&amp;groupid=<?= $GroupID ?>&amp;tagid=<?= $Tag['id'] ?>&amp;auth=<?= $LoggedUser['AuthKey'] ?>" data-tooltip="<?= Lang::get('torrents', 'remove_tag') ?>">
                                            <?= icon('remove') ?>
                                        </a>
                                    <? } ?>
                                    <a class="SidebarList-action" href="torrents.php?action=vote_tag&amp;way=up&amp;groupid=<?= $GroupID ?>&amp;tagid=<?= $Tag['id'] ?>&amp;auth=<?= $LoggedUser['AuthKey'] ?>" data-tooltip="<?= Lang::get('torrents', 'vote_this_tag_up') ?>">
                                        <?= icon('vote-up') ?>
                                    </a>
                                    <span class="SidebarTags-score"><?= $Tag['score'] ?></span>
                                    <a class="SidebarList-action" href="torrents.php?action=vote_tag&amp;way=down&amp;groupid=<?= $GroupID ?>&amp;tagid=<?= $Tag['id'] ?>&amp;auth=<?= $LoggedUser['AuthKey'] ?>" data-tooltip="<?= Lang::get('torrents', 'vote_this_tag_down') ?>">
                                        <?= icon('vote-down') ?>
                                    </a>
                                </div>
                            </li>
                        <?
                        }
                        ?>
                    </ul>
                <?
                } else { // The "no tags to display" message was wrapped in <ul> tags to pad the text.
                ?>
                    <ul>
                        <li><?= Lang::get('torrents', 'there_are_no_tags_to_display') ?></li>
                    </ul>
                <?
                }
                ?>
            </div>
            <?
            if (empty($LoggedUser['DisableTagging'])) {
            ?>
                <div class="SidebarItemTagAdd SidebarItem Box">
                    <div class="SidebarItem-header Box-header"><span><?= Lang::get('torrents', 'add_tag') ?></span></div>
                    <div class="SidebarItem-body Box-body">
                        <form class="FormOneLine FormTorrentAddTag" name="tags" action="torrents.php" method="post">
                            <input type="hidden" name="action" value="add_tag" />
                            <input type="hidden" name="auth" value="<?= $LoggedUser['AuthKey'] ?>" />
                            <input type="hidden" name="groupid" value="<?= $GroupID ?>" />
                            <input class="Input" type="text" name="tagname" id="tagname" size="20" <? Users::has_autocomplete_enabled('other'); ?> />
                            <input class="Button" type="submit" value="+" />
                        </form>
                        <span><a href="rules.php?p=tag" class="brackets"><?= Lang::get('torrents', 'tag_rules') ?></a></span>
                    </div>
                </div>
            <?
            }
            ?>

            <?
            if ($Categories[$GroupCategoryID - 1] == 'Movies') {
                $ShownWith = false;
            ?>
                <div class="SidebarItemArtists SidebarItem Box is-limitHeight">
                    <div class="SidebarItem-header Box-header u-hoverToShow-hover">
                        <span><?= Lang::get('global', 'artist') ?></span>
                        <?= check_perms('torrents_edit') ? '<a class="u-hoverToShow-hide" onclick="ArtistManager(); return false;" href="#">' . Lang::get('global', 'edit') . '</a>' : '' ?>
                    </div>
                    <ul class="SidebarItem-body Box-body SidebarList" id="artist_list">
                        <?
                        if (!empty($Artists[1])) {
                            print '<li class="SidebarList-item"><strong class="artists_label">' . Lang::get('torrents', 'director') . ':</strong></li>';
                        }
                        foreach ($Artists[1] as $Artist) {
                        ?>
                            <li class="SidebarList-item u-hoverToShow-hover">
                                <?= Artists::display_artist($Artist) ?>
                                <?
                                if (check_perms('torrents_edit')) {
                                    $AliasID = $Artist['id'];
                                ?>
                                    <div class="SidebarList-actions">
                                        <a class="SidebarList-action u-hoverToShow-hide" href="javascript:void(0);" onclick="ajax.get('torrents.php?action=delete_alias&amp;auth=' + authkey + '&amp;groupid=<?= $GroupID ?>&amp;artistid=<?= $Artist['id'] ?>&amp;importance=1'); this.parentNode.parentNode.style.display = 'none';" data-tooltip="<?= Lang::get('torrents', 'remove_artist') ?>">
                                            <?= icon('remove') ?>
                                        </a>
                                    </div>
                                <? } ?>
                            </li>
                            <?
                        }


                        if (!empty($Artists[2]) && count($Artists[2]) > 0) {
                            print '				<li class="SidebarList-item"><strong class="artists_label">' .  Lang::get('torrents', 'writer') . ':</strong></li>';
                            foreach ($Artists[2] as $Artist) {
                            ?>
                                <li class="SidebarAritsts-item">
                                    <?= Artists::display_artist($Artist) ?>
                                    <? if (check_perms('torrents_writter')) {
                                        $AliasID = $Artist['id'];
                                    ?>
                                        <div class="SidebarList-actions">
                                            <a class="SidebarList-action u-hoverToShow-hide" href="javascript:void(0);" onclick="ajax.get('torrents.php?action=delete_alias&amp;auth=' + authkey + '&amp;groupid=<?= $GroupID ?>&amp;artistid=<?= $Artist['id'] ?>&amp;importance=2'); this.parentNode.parentNode.style.display = 'none';" data-tooltip="<?= Lang::get('torrents', 'remove_artist') ?>">
                                                <?= icon('remove') ?>
                                            </a>
                                        </div>
                                    <?          } ?>
                                </li>
                            <?
                            }
                        }


                        if (!empty($Artists[3]) && count($Artists[3]) > 0) {
                            print '				<li class="SidebarList-item"><strong class="artists_label">' . Lang::get('torrents', 'movie_producer') . ':</strong></li>';
                            foreach ($Artists[3] as $Artist) {
                            ?>
                                <li class="SidebarList-item u-hoverToShow-hover">
                                    <?= Artists::display_artist($Artist) ?>
                                    <? if (check_perms('torrents_edit')) {
                                        $AliasID = $Artist['id'];
                                    ?>
                                        <span class="SidebarList-actions">
                                            <a class="SidebarList-action  u-hoverToShow-hide" href="javascript:void(0);" onclick="ajax.get('torrents.php?action=delete_alias&amp;auth=' + authkey + '&amp;groupid=<?= $GroupID ?>&amp;artistid=<?= $Artist['id'] ?>&amp;importance=3'); this.parentNode.parentNode.style.display = 'none';" data-tooltip="<?= Lang::get('torrents', 'remove_artist') ?>">
                                                <?= icon('remove') ?>
                                            </a>
                                        </span>
                                    <?          } ?>
                                </li>
                            <?
                            }
                        }
                        if (!empty($Artists[4]) && count($Artists[4]) > 0) {
                            print '<li class="SidebarList-item "><strong class="artists_label">' . Lang::get('torrents', 'composer') . ':</strong></li>';
                            foreach ($Artists[4] as $Artist) {
                            ?>
                                <li class="SidebarList-item u-hoverToShow-hover">
                                    <?= Artists::display_artist($Artist) ?>
                                    <?
                                    if (check_perms('torrents_edit')) {
                                        $DB->query("
					SELECT AliasID
					FROM artists_alias
					WHERE ArtistID = " . $Artist['id'] . "
						AND ArtistID != AliasID
						AND Name = '" . db_string($Artist['name']) . "'");
                                        list($AliasID) = $DB->next_record();
                                        if (empty($AliasID)) {
                                            $AliasID = $Artist['id'];
                                        }
                                    ?>
                                        <span class="SidebarList-actions">
                                            <a class="SidebarList-action u-hoverToShow-hide" href="javascript:void(0);" onclick="ajax.get('torrents.php?action=delete_alias&amp;auth=' + authkey + '&amp;groupid=<?= $GroupID ?>&amp;artistid=<?= $Artist['id'] ?>&amp;importance=4'); this.parentNode.parentNode.style.display = 'none';" data-tooltip="<?= Lang::get('torrents', 'remove_artist') ?>">
                                                <?= icon('remove') ?>
                                            </a>
                                        </span>
                                    <?          } ?>
                                </li>
                            <?
                            }
                        }
                        if (!empty($Artists[5]) && count($Artists[5]) > 0) {
                            print '<li class="SidebarList-item"><strong class="artists_label">' . Lang::get('torrents', 'cinematographer') . ':</strong></li>';
                            foreach ($Artists[5] as $Artist) {
                            ?>
                                <li class="SidebarList-item u-hoverToShow-hover">
                                    <?= Artists::display_artist($Artist) ?>
                                    <? if (check_perms('torrents_edit')) {
                                        $AliasID = $Artist['id'];
                                    ?>
                                        <span class="SidebarList-actions">
                                            <a class="SidebarList-action u-hoverToShow-hide" href="javascript:void(0);" onclick="ajax.get('torrents.php?action=delete_alias&amp;auth=' + authkey + '&amp;groupid=<?= $GroupID ?>&amp;artistid=<?= $Artist['id'] ?>&amp;importance=5'); this.parentNode.parentNode.style.display = 'none';" data-tooltip="<?= Lang::get('torrents', 'remove_conductor') ?>">
                                                <?= icon('remove') ?>
                                            </a>
                                        </span>
                                    <?          } ?>
                                </li>
                            <?
                            }
                        }
                        if (!empty($Artists[6]) && count($Artists[6]) > 0) {
                            print '<li class="SidebarList-item"><strong class="artists_label">' . Lang::get('torrents', 'actor') . ':</strong></li>';
                            foreach ($Artists[6] as $Artist) {
                            ?>
                                <li class="SidebarList-item u-hoverToShow-hover">
                                    <?= Artists::display_artist($Artist) ?>
                                    <? if (check_perms('torrents_edit')) {
                                        $AliasID = $Artist['id'];
                                    ?>
                                        <span class="SidebarList-actions">
                                            <a class="SidebarList-action u-hoverToShow-hide" href="javascript:void(0);" onclick="ajax.get('torrents.php?action=delete_alias&amp;auth=' + authkey + '&amp;groupid=<?= $GroupID ?>&amp;artistid=<?= $Artist['id'] ?>&amp;importance=6'); this.parentNode.parentNode.style.display = 'none';" data-tooltip="<?= Lang::get('torrents', 'remove_artist') ?>">
                                                <?= icon('remove') ?>
                                            </a>
                                        </span>
                                    <?          } ?>
                                </li>
                        <?
                            }
                        }
                        ?>
                    </ul>
                </div>
                <? if (check_perms('torrents_add_artist')) { ?>
                    <div class="SidebarItemArtistAdd SidebarItem Box">
                        <div class="SidebarItem-header Box-header u-hoverToShow-hover">
                            <span><?= Lang::get('torrents', 'add_artist') ?></span>
                            <a class="u-hoverToShow-hide" onclick="globalapp.browseAddArtistField(); return false;" href="#">+</a>
                        </div>
                        <div class="SidebarItem-body Box-body">
                            <form class="FormOneLine FormTorrentAddArtist" name="artists" action="torrents.php" method="post">
                                <div id="AddArtists">
                                    <input type="hidden" name="action" value="add_alias" />
                                    <input type="hidden" name="auth" value="<?= $LoggedUser['AuthKey'] ?>" />
                                    <input type="hidden" name="groupid" value="<?= $GroupID ?>" />
                                    <input class="Input" type="text" id="artist" name="aliasname[]" size="17" <? Users::has_autocomplete_enabled('other'); ?> />
                                    <select class="Input" name="importance[]">
                                        <option class="Select-option" value="1"><?= Lang::get('torrents', 'director') ?></option>
                                        <option class="Select-option" value="2"><?= Lang::get('torrents', 'writer') ?></option>
                                        <option class="Select-option" value="3"><?= Lang::get('torrents', 'movie_producer') ?></option>
                                        <option class="Select-option" value="4"><?= Lang::get('torrents', 'composer') ?></option>
                                        <option class="Select-option" value="5"><?= Lang::get('torrents', 'cinematographer') ?></option>
                                        <option class="Select-option" value="6"><?= Lang::get('torrents', 'actor') ?></option>
                                    </select>
                                </div>
                                <input class="Button" type="submit" value="<?= Lang::get('global', 'add') ?>" />
                            </form>
                        </div>
                    </div>
            <?
                }
            }
            if (ENABLE_COLLAGES) {
                include(SERVER_ROOT . '/sections/torrents/collage.php');
            }
            include(SERVER_ROOT . '/sections/torrents/vote_ranks.php');
            include(SERVER_ROOT . '/sections/torrents/vote.php');
            ?>
        </div>
        <?
        if (check_perms('torrents_check')) {
            $CheckAllTorrents = !$LoggedUser['DisableCheckAll'];
        } else {
            $CheckAllTorrents = false;
        }
        if (check_perms('self_torrents_check')) {
            $CheckSelfTorrents = !$LoggedUser['DisableCheckSelf'];
        } else {
            $CheckSelfTorrents = false;
        }


        if ($CheckAllTorrents || $CheckSelfTorrents) {
        ?>
            <script>
                function torrent_check(event) {
                    var id = event.data.id,
                        checked = event.data.checked
                    $.get("torrents.php", {
                            action: "torrent_check",
                            torrentid: id,
                            checked: checked
                        },
                        function(data) {
                            var obj = eval("(" + data + ")");
                            if (obj.ret == "success") {
                                if (checked == 1) {
                                    $('#torrent' + id + '_check1').show()
                                    $('#slot-torrent' + id + '_check1').show()
                                    $('#torrent' + id + '_check0').hide()
                                    $('#slot-torrent' + id + '_check0').hide()
                                } else {
                                    $('#torrent' + id + '_check0').show()
                                    $('#slot-torrent' + id + '_check0').show()
                                    $('#torrent' + id + '_check1').hide()
                                    $('#slot-torrent' + id + '_check1').hide()
                                }
                            } else {
                                alert('失败');
                            }
                        });
                }
            </script>
        <? } ?>
        <div class="LayoutMainSidebar-main u-tab">
            <div class="TableContainer u-tabItem u-tabItemTorrent" style="<?= $View == 'slot' ? "display:none" : "" ?>">
                <table class="TableTorrent Table has-slots" header-large last-tr-hidden id="torrent_details">
                    <tr class="Table-rowHeader">
                        <td class="TableTorrent-cellName Table-cell" colspan="1">
                            <span>
                                <?= Lang::get('global', 'torrents') ?>
                                <span> | <span>
                                        <a href='#' onclick='globalapp.toggleTab(event, ".u-tabItemSlot")'><?= Lang::get('torrents', 'slot_table') ?></a>
                                    </span>
                                    <? if ($HasRequest) { ?>
                                        | <span>
                                            <a href='#' onclick='globalapp.toggleTab(event, ".u-tabItemRequest")'><?= Lang::get('global', 'requests') ?></a>
                                        </span>
                                    <? } ?>
                        </td>
                        <td class="TableTorrent-cellSize Table-cell TableTorrent-cellStat">
                            <span aria-hidden="true" data-tooltip="<?= Lang::get('global', 'size') ?>">
                                <?= icon('torrent-size') ?>
                            </span>
                        </td>
                        <td class="TableTorrent-cellSnatches Table-cell TableTorrent-cellStat">
                            <span aria-hidden="true" data-tooltip="<?= Lang::get('global', 'snatched') ?>">
                                <?= icon('torrent-snatches') ?>
                            </span>
                        </td>
                        <td class="TableTorrent-cellSeeders Table-cell TableTorrent-cellStat">
                            <i aria-hidden="true" data-tooltip="<?= Lang::get('global', 'seeders') ?>">
                                <?= icon('torrent-seeders') ?>
                            </i>
                        </td>
                        <td class="TableTorrent-cellLeechers Table-cell TableTorrent-cellStat">
                            <i aria-hidden="true" data-tooltip="<?= Lang::get('global', 'leechers') ?>">
                                <?= icon('torrent-leechers') ?>
                            </i>
                        </td>
                    </tr>
                    <tr class="TableTorrent-rowSlotFilter Table-row id=" slot_filter_container">
                        <td class="TableTorrent-cellSlotFilter Table-cell" colspan="5">
                            <div class="TableTorrent-slotFilters" id="slot_filter">
                                <a href="#" class="TableTorrent-slotFilterButton" data-slot="quality" onclick='globalapp.filterSlot(event, ["quality", "en_quality", "cn_quality", "feature"])' data-tooltip="<?= Lang::get('torrents', 'all_quality_slot') ?>"><?= icon('Table/quality') ?></a>
                                <a href="#" class="TableTorrent-slotFilterButton" data-slot="cn_quality" onclick='globalapp.filterSlot(event, ["cn_quality"])' data-tooltip="<?= Lang::get('torrents', 'cn_quality_slot') ?>"><?= icon('Table/cn-quality') ?></a>
                                <a href="#" class="TableTorrent-slotFilterButton" data-slot="en_quality" onclick='globalapp.filterSlot(event, ["en_quality"])' data-tooltip="<?= Lang::get('torrents', 'en_quality_slot') ?>"><?= icon('Table/en-quality') ?></a>
                                <a href="#" class="TableTorrent-slotFilterButton" data-slot="retention" onclick='globalapp.filterSlot(event, ["retention"])' data-tooltip="<?= Lang::get('torrents', 'retention_slot') ?>"><?= icon('Table/retention') ?></a>
                                <a href="#" class="TableTorrent-slotFilterButton" data-slot="feature" onclick='globalapp.filterSlot(event, ["feature"])' data-tooltip="<?= Lang::get('torrents', 'feature_slot') ?>"><?= icon('Table/feature') ?></a>
                                <a href="#" class="TableTorrent-slotFilterButton" data-slot="remux" onclick='globalapp.filterSlot(event, ["remux"])' data-tooltip="<?= Lang::get('torrents', 'remux_slot') ?>"><?= icon('Table/remux') ?></a>
                                <a href="#" class="TableTorrent-slotFilterButton" data-slot="untouched" onclick='globalapp.filterSlot(event, ["untouched"])' data-tooltip="<?= Lang::get('torrents', 'untouched_slot') ?>"><?= icon('Table/untouched') ?></a>
                                <a href="#" class="TableTorrent-slotFilterButton" data-slot="diy" onclick='globalapp.filterSlot(event, ["diy"])' data-tooltip="<?= Lang::get('torrents', 'diy_slot') ?>"><?= icon('Table/diy') ?></a>
                                <a href="#" class="TableTorrent-slotFilterButton" data-slot="clear" onclick='globalapp.filterSlot(event, [])' style="visibility: hidden;" data-tooltip="<?= Lang::get('torrents', 'clear_slot') ?>"><?= icon('Table/clear') ?></a>
                            </div>
                        </td>
                    </tr>
                    <?


                    $CheckAllTorrents = false;
                    if (check_perms('torrents_check')) {
                        $CheckAllTorrents = !$LoggedUser['DisableCheckAll'];
                    }
                    $CheckSelfTorrents = false;
                    if (check_perms('self_torrents_check')) {
                        $CheckSelfTorrents = !$LoggedUser['DisableCheckSelf'];
                    }

                    $LastTorrent = [];
                    $EditionID = 0;
                    $SnatchedGroupClass = Torrents::parse_group_snatched($TorrentDetails) ? ' snatched_group' : '';
                    foreach ($TorrentList as $Torrent) {
                        $SnatchedTorrentClass = $Torrent['IsSnatched'] ? ' snatched_torrent' : '';
                        $TorrentID = $Torrent['ID'];
                        $Size = $Torrent['Size'];
                        $Seeders = $Torrent['Seeders'];
                        $Leechers = $Torrent['Leechers'];
                        $Snatched = $Torrent['Snatched'];
                        $Slot = $Torrent['Slot'];
                        $Source = $Torrent['Source'];
                        $Codec = $Torrent['Codec'];
                        $Container = $Torrent['Container'];
                        $Resolution = $Torrent['Resolution'];
                        $Processing = $Torrent['Processing'];
                        $UserID = $Torrent['UserID'];
                        $HasFile = $Torrent['HasFile'];
                        $ExtraInfo = Torrents::torrent_info($Torrent, true, [
                            'SettingTorrentTitle' => G::$LoggedUser['SettingTorrentTitle']
                        ]);
                        $NewEdition = Torrents::get_new_edition_title($LastTorrent, $Torrent);
                        if ($NewEdition) {
                            $EditionID++;
                    ?>
                            <tr class="TableTorrent-rowCategory Table-row" group-id="<?= $GroupID ?>">
                                <td class="TableTorrent-cellCategory Table-cell" colspan="5">
                                    <a class="u-toggleEdition-button" href="#" onclick="globalapp.toggleEdition(event, <?= $GroupID ?>, <?= $EditionID ?>)" data-tooltip="<?= Lang::get('global', 'collapse_this_edition_title') ?>">&minus;</a>
                                    <?= $NewEdition ?>
                                </td>
                            </tr>
                        <?
                        }
                        $LastTorrent = $Torrent;

                        $TorrentCheckedBy = 'unknown';

                        $TorrentChecked = Torrents::get_torrent_checked($TorrentID);
                        if ($TorrentChecked) {
                            $TorrentCheckedBy = Users::user_info($TorrentChecked)['Username'];
                        }
                        $CanEdit = (check_perms('torrents_edit') || (($UserID == $LoggedUser['ID'] && !$LoggedUser['DisableWiki'])));
                        $CanUseToken = Torrents::can_use_token($Torrent);
                        $CanDelete = check_perms('torrents_delete') || $UserID == $LoggedUser['ID'];
                        ?>
                        <tr class="TableTorrent-rowTitle Table-row releases_<?= $ReleaseType ?>  <?= $SnatchedGroupClass . $SnatchedTorrentClass ?>" id="torrent<?= $TorrentID ?>" group-id="<?= $GroupID ?>" edition-id="<?= $EditionID ?>" data-slot="<?= TorrentSlot::slot_name($Slot) ?>" data-source="<?= $Source ?>" data-codec="<?= $Codec ?>" data-container="<?= $Container ?>" data-resolution="<?= $Resolution ?>" data-processing="<?= $Processing ?>">
                            <td class="Table-cell">
                                <span class="TableTorrent-titleActions">
                                    [
                                    <a href="torrents.php?action=download&amp;id=<?= $TorrentID ?>&amp;authkey=<?= $LoggedUser['AuthKey'] ?>&amp;torrent_pass=<?= $LoggedUser['torrent_pass'] ?>" data-tooltip="<?= Lang::get('global', 'download') ?>"><?= ($HasFile ? 'DL' : 'Missing') ?></a>
                                    <? if ($CanUseToken) { ?>
                                        |
                                        <a href="torrents.php?action=download&amp;id=<?= $TorrentID ?>&amp;authkey=<?= $LoggedUser['AuthKey'] ?>&amp;torrent_pass=<?= $LoggedUser['torrent_pass'] ?>&amp;usetoken=1" data-tooltip="<?= Lang::get('global', 'use_fl_tokens') ?>" onclick="return confirm('<?= FL_confirmation_msg($Seeders, $Size) ?>');">FL</a>
                                    <? } ?>
                                    |
                                    <a href="reportsv2.php?action=report&amp;id=<?= $TorrentID ?>" data-tooltip="<?= Lang::get('torrents', 'report') ?>">RP</a>
                                    <? if ($CanEdit) { ?>
                                        |
                                        <a href="torrents.php?action=edit&amp;id=<?= $TorrentID ?>" data-tooltip="<?= Lang::get('global', 'edit') ?>">ED</a>
                                    <? }
                                    if ($CanDelete) { ?>
                                        |
                                        <a href="torrents.php?action=delete&amp;torrentid=<?= $TorrentID ?>" data-tooltip="<?= Lang::get('torrents', 'remove') ?>">RM</a>
                                    <? } ?>
                                    |
                                    <a href="torrents.php?torrentid=<?= $TorrentID ?>" data-tooltip="<?= Lang::get('torrents', 'permalink') ?>">PL</a>
                                    ]
                                </span>
                                <?
                                if ($CheckAllTorrents || ($CheckSelfTorrents && $UserID == $LoggedUser['ID'])) {
                                    if (!$CheckAllTorrents) {
                                        $TorrentCheckedBy = "someone";
                                    }
                                ?>
                                    <script>
                                        $(document).ready(function() {
                                            $('#torrent<?= $TorrentID ?>_check0').bind('click', {
                                                id: <?= $TorrentID ?>,
                                                checked: 1,
                                            }, torrent_check)
                                            $('#torrent<?= $TorrentID ?>_check1').bind('click', {
                                                id: <?= $TorrentID ?>,
                                                checked: 0,
                                            }, torrent_check)
                                        })
                                    </script>
                                    <a href="javascript:void(0)" id="torrent<?= $TorrentID ?>_check1" style="display:<?= $TorrentChecked ? "inline-block" : "none" ?>;color:#649464;" data-tooltip="<?= Lang::get('torrents', 'checked_by_before') ?><?= $TorrentChecked ? $TorrentCheckedBy : $LoggedUser['Username'] ?><?= Lang::get('torrents', 'checked_by_after') ?>">
                                        <?= icon("Table/checked") ?>
                                    </a>
                                    <a href="javascript:void(0)" id="torrent<?= $TorrentID ?>_check0" style="display:<?= $TorrentChecked ? "none" : "inline-block" ?>;color:#CF3434;" data-tooltip="<?= Lang::get('torrents', 'turn_me_green') ?>">
                                        <?= icon("Table/unchecked") ?>
                                    </a>
                                <?
                                } else {
                                ?>
                                    <i style="color: <?= $TorrentChecked ? "#74B274" : "#A6A6A6" ?>;" data-tooltip="<?= $TorrentChecked ? Lang::get('torrents', 'has_been_checked') : Lang::get('torrents', 'has_not_been_checked') ?><?= Lang::get('torrents', 'checked_explanation') ?>">
                                        <?= icon("Table/" . ($TorrentChecked ? "checked" : "unchecked")) ?>
                                    </i>
                                <?
                                }
                                ?>
                                &nbsp;
                                <a data-tooltip-html href="#" onclick="globalapp.toggleTorrentDetail(event, '#torrent_detail_<?= $TorrentID ?>')">
                                    <?= $ExtraInfo; ?>
                                    <div data-tooltip-html-content>
                                        <div><?= Lang::get('torrents', TorrentSlot::slot_name($Slot) . '_slot') ?></div>
                                    </div>
                                </a>
                            </td>
                            <td class="TableTorrent-cellSize Table-cell TableTorrent-cellStat"><?= Format::get_size($Size) ?></td>
                            <td class="TableTorrent-cellSnatches Table-cell TableTorrent-cellStat"><?= number_format($Snatched) ?></td>
                            <td class="TableTorrent-cellSeeders Table-cell TableTorrent-cellStat"><?= number_format($Seeders) ?></td>
                            <td class="TableTorrent-cellLeechers Table-cell TableTorrent-cellStat"><?= number_format($Leechers) ?></td>
                        </tr>
                        <tr class="TableTorrent-rowDetail Table-row u-toggleEdition-alwaysHidden releases_<?= $ReleaseType ?>  <? if (!isset($_GET['torrentid']) || $_GET['torrentid'] != $TorrentID) { ?>u-hidden<? } ?>" id="torrent_detail_<?= $TorrentID; ?>" group-id="<?= $GroupID ?>" edition-id="<?= $EditionID ?>">
                            <td class="TableTorrent-cellDetail Table-cell" colspan="5">
                                <?
                                $Render = new TorrentTableView();
                                $Option = new DetailOption();
                                $Option->ThumbCounts = $ThumbCounts;
                                $Option->BonusSended = $BonusSended;
                                $Option->Expand = true;
                                $Render->with_detail('detail', $Option);
                                $Render->render_torrent_detail($TorrentDetails, $Torrent);
                                ?>
                            </td>
                        </tr>
                    <?
                    }
                    ?>
                </table>
            </div>
            <form id="slot" class="u-tabItem u-tabItemSlot" style="<?= $View == 'slot' ? "" : "display: none" ?>" method="post">
                <input type="hidden" name="action" value="takeeditslot" />
                <input type="hidden" name="auth" value="<?= $LoggedUser['AuthKey'] ?>" />
                <input type="hidden" name="groupid" value="<?= $GroupID ?>" />
                <div class="TableContainer">
                    <table class="TableTorrent Table" variant="slot" header-large>
                        <tr class="Table-rowHeader">
                            <td class="TableTorrent-cellName Table-cell" colspan="1">
                                <span>
                                    <a href="#" onclick="globalapp.toggleTab(event, '.u-tabItemTorrent')"><?= Lang::get('global', 'torrents') ?></a>
                                    <span> | </span>
                                    <?= Lang::get('torrents', 'slot_table') ?>
                                </span>
                                <a href="wiki.php?action=article&id=66" data-tooltip="<?= Lang::get('torrents', 'slot_wiki') ?>">[?]</a>
                                <? if ($HasRequest) { ?>
                                    | <span>
                                        <a href='#' onclick='globalapp.toggleTab(event, ".u-tabItemRequest")'><?= Lang::get('global', 'requests') ?></a>
                                    </span>
                                <? } ?>
                            </td>
                            <td class="TableTorrent-cellSize Table-cell TableTorrent-cellStat">
                                <span aria-hidden="true" data-tooltip="<?= Lang::get('global', 'size') ?>">
                                    <?= icon('torrent-size') ?>
                                </span>
                            </td>
                            <td class="TableTorrent-cellSlotName Table-cell">
                                <span><?= Lang::get('torrents', 'slot_action') ?></span>
                            </td>
                        </tr>

                        <?
                        $SlotTorrents = TorrentSlot::convert_slot_torrents($TorrentList);
                        $MissingSlots = $SlotTorrents[1];
                        $Torrents = $SlotTorrents[0];
                        $EditionID = 0;
                        $LastTorrent = [];
                        foreach ($Torrents as $Torrent) {
                            $Resolution = $Torrent['Resolution'];
                            $Missing = isset($Torrent['Missing']);
                            $Dupe = isset($Torrent['Dupe']);
                            $LastResolution = !empty($LastTorrent) ? $LastTorrent['Resolution'] : '';
                            if (!$Missing) {
                                $TorrentID = $Torrent['ID'];
                                $Size = $Torrent['Size'];
                                $IsExtraSlot = $Torrent['IsExtraSlot'];
                                $TorrentInfo = Torrents::torrent_info($Torrent, true, [
                                    'SettingTorrentTitle' => G::$LoggedUser['SettingTorrentTitle']
                                ]);
                            }
                            $Slot = $Torrent['Slot'];
                            $NewEdition = Torrents::get_new_edition_title($LastTorrent, $Torrent);
                            if ($NewEdition) {
                                $EditionID++;
                        ?>
                                <tr class="TableTorrent-rowCategory Table-row" group-id="<?= $GroupID ?>">
                                    <td class="TableTorrent-cellCategory Table-cell" colspan="3">
                                        <a class="u-toggleEdition-button" href="#" onclick="globalapp.toggleEdition(event, <?= $GroupID ?>, <?= $EditionID ?>)" data-tooltip="<?= Lang::get('global', 'collapse_this_edition_title') ?>">&minus;</a>
                                        <?= $NewEdition ?>
                                    </td>
                                </tr>
                                <?
                            }
                            if (TorrentSlot::get_slot_resolution($Resolution) != TorrentSlot::get_slot_resolution($LastResolution)) {
                                $MissSlots = $MissingSlots[TorrentSlot::get_slot_resolution($Resolution)];
                                $MissSlotNames = [];
                                foreach ($MissSlots as $MissingSlot) {
                                    if ($MissingSlot == TorrentSlotType::None) {
                                        continue;
                                    }
                                    $SlotTooltip = TorrentSlot::empty_slot_tooltip($MissingSlot);
                                    $MissSlotNames[] = "<span data-tooltip='$SlotTooltip'><i>" . Lang::get('torrents', TorrentSlot::slot_option_lang($MissingSlot)) . "</i></span>";
                                }
                                if (count($MissSlotNames) > 0) {
                                ?>
                                    <tr class="TableTorrent-rowEmptySlotNote Table-row releases_<?= $ReleaseType ?>" group-id="<?= $GroupID ?>" edition-id="<?= $EditionID ?>">
                                        <td class="TableTorrent-cellEmptySlotNote" colspan="3"><i><?= Lang::get('torrents', TorrentSlot::empty_slot_title(TorrentSlot::get_slot_resolution($Resolution))) ?></i><?= implode(' / ', $MissSlotNames) ?></td>
                                    </tr>
                                <?
                                }
                            }
                            if (!$Missing) {
                                ?>

                                <tr class="TableTorrent-rowTitle Table-row releases_<?= $ReleaseType ?>" group-id="<?= $GroupID ?>" edition-id="<?= $EditionID ?>" <?= !$Missing ? "id='torrent$TorrentID'" : '' ?> data-slot="<?= TorrentSlot::slot_name($Slot) ?>">
                                    <td class="TableTorrent-cellName Table-cell">
                                        &nbsp;
                                        <?
                                        if (($Dupe || empty($Slot)) && check_perms("torrents_slot_edit")) {
                                            $TorrentInfo = "<strong style='display:inline' class='u-colorWarning'>$TorrentInfo</strong>";
                                        }
                                        ?>
                                        <a data-tooltip="<?= Lang::get('torrents', TorrentSlot::slot_name($Slot) . '_slot') ?>" href="#" onclick="globalapp.toggleTorrentDetail(event, '#torrent_slot_<?= $TorrentID ?>'); return false;"><?= $TorrentInfo ?></a>

                                    </td>
                                    <td class="TableTorrent-cellSize Table-cell TableTorrent-cellStat">
                                        <?= Format::get_size($Size) ?>
                                    </td>
                                    <?
                                    if (check_perms("torrents_slot_edit")) {
                                    ?>
                                        <td class="TableTorrent-cellSlotName Table-cell">
                                            <input type="hidden" name="torrents[]" value="<?= $TorrentID ?>" />
                                            <select class="Input" name="slots[]">
                                                <?
                                                foreach (TorrentSlot::get_resolution_slots($Resolution) as $RSlot) {
                                                ?>
                                                    <?= TorrentSlot::slot_option($RSlot, false, $Slot, $IsExtraSlot) ?>
                                                <?
                                                }
                                                $extraName = Lang::get('torrents', 'additional_slots');
                                                ?>
                                                <optgroup class="Select-group" label="<?= $extraName ?>">
                                                    <?
                                                    foreach (TorrentSlot::get_resolution_slots($Resolution) as $RSlot) {
                                                        if ($RSlot == TorrentSlotType::None) {
                                                            continue;
                                                        }
                                                    ?>
                                                        <?= TorrentSlot::slot_option($RSlot, true, $Slot, $IsExtraSlot) ?>
                                                    <?
                                                    }
                                                    ?>
                                                </optgroup>
                                            </select>
                                        </td>
                                    <?
                                    } else {
                                        $SlotName = TorrentSlot::slot_option_lang($Slot);
                                        if (empty($SlotName)) {
                                            $SlotName = '---';
                                        } else {
                                            $SlotName = Lang::get('torrents', $SlotName);
                                        }
                                        if ($IsExtraSlot) {
                                            $SlotName .= '*';
                                        }
                                    ?>
                                        <td class="TableTorrent-cellSlotName Table-cell"><?= $SlotName ?></td>
                                    <?
                                    }
                                    ?>

                                </tr>
                                <tr class="TableTorrent-rowDetail TorrenTable-row u-toggleEdition-alwaysHidden releases_<?= $ReleaseType ?>  <? if (!isset($_GET['torrentid']) || $_GET['torrentid'] != $TorrentID) { ?>u-hidden<? } ?>" id="torrent_slot_<?= $TorrentID; ?>" group-id="<?= $GroupID ?>" edition-id="<?= $EditionID ?>">
                                    <td class="TableTorrent-cellDetail Table-cell" colspan="3">
                                        <?
                                        $Render = new TorrentTableView();
                                        $Option = new DetailOption();
                                        $Option->WithReport = false;
                                        $Option->ReadOnly = true;
                                        $Option->Expand = true;
                                        $Render->with_detail('slot', $Option);
                                        $Render->render_torrent_detail($TorrentDetails, $Torrent);
                                        ?>
                                    </td>
                                </tr>
                            <?
                            }
                            ?>
                        <?

                            $LastTorrent = $Torrent;
                        } ?>

                        <?
                        if (check_perms("torrents_slot_edit")) {
                        ?>
                            <tr class="submit_tr">
                                <td colspan="3" class="center no_padding"><input class="Button" type="submit" /></td>
                            </tr>
                        <?
                        }
                        ?>
                    </table>
                </div>
            </form>
            <?

            if ($HasRequest) {
                $i = 0;
            ?>
                <div class="TableContainer">
                    <table class="TableTorrent TableRequest Table u-tabItem u-tabItemRequest" style="<?= $View == 'request' ? "" : "display:none" ?>" id="torrent_details">
                        <tr class="Table-rowHeader">
                            <td class="TableRequest-cellName Table-cell" colspan="1">
                                <span>
                                    <a href="#" onclick="globalapp.toggleTab(event, '.u-tabItemTorrent')"><?= Lang::get('global', 'torrents') ?></a>
                                </span>
                                <span> | </span>
                                <span>
                                    <a href='#' onclick='globalapp.toggleTab(event, ".u-tabItemSlot")'><?= Lang::get('torrents', 'slot_table') ?></a>
                                </span>
                                <span> | </span>
                                <?= Lang::get('global', 'requests') ?>
                            </td>
                            <td class="TableRequest-cellVotes Table-cell TableRequest-cellValue"><?= Lang::get('torrents', 'votes') ?></td>
                            <td class="TableRequest-cellBounty Table-cell TableRequest-cellValue"><?= Lang::get('torrents', 'bounty') ?></td>
                        </tr>
                        <? foreach ($Requests as $Request) {
                            $RequestVotes = Requests::get_votes_array($Request['ID']);

                            $CodecString = implode(', ', explode('|', $Request['CodecList']));
                            $SourceString = implode(', ', explode('|', $Request['SourceList']));
                            $ContainerString = implode(', ', explode('|', $Request['ContainerList']));
                            $ResolutionString = implode(', ', explode('|', $Request['ResolutionList']));

                        ?>
                            <tr class="Table-row">
                                <td class="TableRequest-cellName Table-cell">
                                    <a href="requests.php?action=view&amp;id=<?= $Request['ID'] ?>"><?= $CodecString ?> /
                                        <?= $SourceString ?> / <?= $ResolutionString ?> / <?= $ContainerString ?></a>
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
                            </tr>
                        <?  } ?>

                    </table>
                </div>
            <?
            }
            $Collages = $Cache->get_value("torrent_collages_$GroupID");
            if (!is_array($Collages)) {
                $DB->query("
		SELECT c.Name, c.NumTorrents, c.ID
		FROM collages AS c
			JOIN collages_torrents AS ct ON ct.CollageID = c.ID
		WHERE ct.GroupID = '$GroupID'
			AND Deleted = '0'
			AND CategoryID != '0'");
                $Collages = $DB->to_array();
                $Cache->cache_value("torrent_collages_$GroupID", $Collages, 600 * 6);
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
                <table class="Table TableCollage">
                    <tr class="Table-rowHeader">
                        <td class="Table-cell" width="85%"><?= Lang::get('torrents', 'this_album_is_in_collages_1') ?>
                            <?= number_format(count($Collages)) ?>
                            <?= Lang::get('torrents', 'this_album_is_in_collages_2') ?><?= ((count($Collages) > 1) ? Lang::get('torrents', 'this_album_is_in_collages_3') : '') ?><?= $SeeAll ?>
                        </td>
                        <td class="Table-cell"><?= Lang::get('torrents', 'torrents_count') ?></td>
                    </tr>
                    <? foreach ($Indices as $i) {
                        list($CollageName, $CollageTorrents, $CollageID) = $Collages[$i];
                        unset($Collages[$i]);
                    ?>
                        <tr class="Table-row">
                            <td class="Table-cell"><a href="collages.php?id=<?= $CollageID ?>"><?= $CollageName ?></a></td>
                            <td class="Table-cell" class="number_column"><?= number_format($CollageTorrents) ?></td>
                        </tr>
                    <?  }
                    foreach ($Collages as $Collage) {
                        list($CollageName, $CollageTorrents, $CollageID) = $Collage;
                    ?>
                        <tr class="Table-row hidden">
                            <td class="Table-cell"><a href="collages.php?id=<?= $CollageID ?>"><?= $CollageName ?></a></td>
                            <td class="Table-cell Table-cellRight"><?= number_format($CollageTorrents) ?></td>
                        </tr>
                    <?  } ?>
                </table>
            <?
            }

            $PersonalCollages = $Cache->get_value("torrent_collages_personal_$GroupID");
            if (!is_array($PersonalCollages)) {
                $DB->query("
		SELECT c.Name, c.NumTorrents, c.ID
		FROM collages AS c
			JOIN collages_torrents AS ct ON ct.CollageID = c.ID
		WHERE ct.GroupID = '$GroupID'
			AND Deleted = '0'
			AND CategoryID = '0'");
                $PersonalCollages = $DB->to_array(false, MYSQLI_NUM);
                $Cache->cache_value("torrent_collages_personal_$GroupID", $PersonalCollages, 600 * 6);
            }


            if (count($PersonalCollages) > 0) {
                if (count($PersonalCollages) > MAX_PERS_COLLAGES) {
                    // Pick some at random
                    $Range = range(0, count($PersonalCollages) - 1);
                    shuffle($Range);
                    $Indices = array_slice($Range, 0, MAX_PERS_COLLAGES);
                    $SeeAll = ' <a href="#" onclick="$(\'.personal_rows\').gtoggle(); return false;">(See all)</a>';
                } else {
                    $Indices = range(0, count($PersonalCollages) - 1);
                    $SeeAll = '';
                }
            ?>
                <table class="TableCollage Table">
                    <tr class="Table-rowHeader">
                        <td class="Table-cell" width="85%"><?= Lang::get('torrents', 'this_album_is_in_personal_collages_1') ?>
                            <?= number_format(count($PersonalCollages)) ?>
                            <?= Lang::get('torrents', 'this_album_is_in_personal_collages_2') ?><?= ((count($PersonalCollages) > 1) ? Lang::get('torrents', 'this_album_is_in_personal_collages_3') : '') ?><?= $SeeAll ?>
                        </td>
                        <td class="Table-cell"><?= Lang::get('torrents', 'torrents_count') ?></td>
                    </tr>
                    <? foreach ($Indices as $i) {
                        list($CollageName, $CollageTorrents, $CollageID) = $PersonalCollages[$i];
                        unset($PersonalCollages[$i]);
                    ?>
                        <tr class="Table-row">
                            <td class="Table-cell"><a href="collages.php?id=<?= $CollageID ?>"><?= $CollageName ?></a></td>
                            <td class="Table-cell Table-cellRight"><?= number_format($CollageTorrents) ?></td>
                        </tr>
                    <?  }
                    foreach ($PersonalCollages as $Collage) {
                        list($CollageName, $CollageTorrents, $CollageID) = $Collage;
                    ?>
                        <tr class="Table-row hidden">
                            <td><a href="collages.php?id=<?= $CollageID ?>"><?= $CollageName ?></a></td>
                            <td class="Table-cell Table-cellRight"><?= number_format($CollageTorrents) ?></td>
                        </tr>
                    <?  } ?>
                </table>
            <?
            }
            // Matched Votes
            include(SERVER_ROOT . '/sections/torrents/voter_picks.php');
            $Pages = Format::get_pages($Page, $NumComments, TORRENT_COMMENTS_PER_PAGE, 9, '#comments');
            ?>
            <div class="u-vstack" id="torrent_comments">
                <div class="BodyNavLinks"><a name="comments"></a>
                    <?= $Pages ?>
                </div>
                <?
                CommentsView::render_comments($Thread, $LastRead, "torrents.php?id=$GroupID");
                ?>
                <div class="BodyNavLinks">
                    <?= $Pages ?>
                </div>
                <?
                View::parse('generic/reply/quickreply.php', array(
                    'InputName' => 'pageid',
                    'InputID' => $GroupID,
                    'Action' => 'comments.php?page=torrents',
                    'InputAction' => 'take_post',
                    'TextareaCols' => 65,
                    'SubscribeBox' => true
                ));
                ?>
            </div>
        </div>
    </div>
</div>
<?
View::show_footer();
