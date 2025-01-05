<?

use Gazelle\Torrent\TorrentSlot;

include(CONFIG['SERVER_ROOT'] . '/classes/torrenttable.class.php');
function compare($X, $Y) {
    return ($Y['score'] - $X['score']);
}
header('Access-Control-Allow-Origin: *');


$GroupID = ceil($_GET['id']);
if (!empty($_GET['revisionid']) && is_number($_GET['revisionid'])) {
    $RevisionID = $_GET['revisionid'];
} else {
    $RevisionID = 0;
}

include(CONFIG['SERVER_ROOT'] . '/sections/torrents/functions.php');
$TorrentCache = Torrents::get_group($GroupID, true, $RevisionID);
$TorrentDetails = $TorrentCache;
$TorrentList = $TorrentCache['Torrents'];
$View = isset($_GET['view']) ? $_GET['view'] : '';

// Group details
$WikiBody = Lang::choose_content($TorrentDetails['MainWikiBody'], $TorrentDetails['WikiBody']);

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
$Name = $TorrentDetails['Name'];

$RTTitle = empty($TorrentDetails['RTTitle']) ? Torrents::sanitizeName(html_entity_decode($Name)) : $TorrentDetails['RTTitle'];

$GroupName = Lang::choose_content($TorrentDetails['Name'], $TorrentDetails['SubName']);
$SubName = Lang::choose_content($TorrentDetails['SubName'], $TorrentDetails['Name']);
$GroupYear = $TorrentDetails['Year'];
$ReleaseType = $TorrentDetails['ReleaseType'];
$GroupCategoryID = $TorrentDetails['CategoryID'];
$GroupTime = $TorrentDetails['Time'];
$TorrentTags = $TorrentDetails['TagList'];
$TagSubList = $TorrentDetails['TagSubList'];
$TorrentTagIDs = $TorrentDetails['TorrentTagIDs'];
$TorrentTagUserIDs = $TorrentDetails['TorrentTagUserIDs'];
$TagPositiveVotes = $TorrentDetails['TagPositiveVotes'];
$TagNegativeVotes = $TorrentDetails['TagNegativeVotes'];
$RawName = Torrents::group_name($TorrentDetails, false);
$DisplayName = "<span dir=\"ltr\">$RawName</span>";

$Requests = Requests::get_group_requests($GroupID);
$HasRequest = false;
if (empty($LoggedUser['DisableRequests']) && count($Requests) > 0) {
    $HasRequest = true;
}
$WikiBody = Text::full_format($WikiBody);

$Artists = Artists::get_artist($GroupID);
$Director = null;
foreach ($Artists[Artists::Director] as $ID => $Artist) {
    $Director = $Artist;
    break;
}
$Actors = $Artists[Artists::Actor];
$Directors = $Artists[Artists::Director];
$Writters = $Artists[Artists::Writter];
$Cinematographers = $Artists[Artists::Cinematographer];
$Composers = $Artists[Artists::Composer];
$Producers = $Artists[Artists::Producer];

$Title = $RawName;
$AltName = $RawName;

$Tags = array();
$TagNames = array();
if ($TorrentTags != '') {
    $TorrentTags = explode(' ', $TorrentTags);
    $TorrentTagIDs = explode(' ', $TorrentTagIDs);
    $TorrentTagUserIDs = explode(' ', $TorrentTagUserIDs);
    $TagSubList = explode(' ', $TagSubList);
    $TagPositiveVotes = explode(' ', $TagPositiveVotes);
    $TagNegativeVotes = explode(' ', $TagNegativeVotes);
    $SubNames = Tags::get_sub_name($TorrentTags);
    foreach ($TorrentTags as $TagKey => $TagName) {
        $Tags[$TagKey]['name'] = $TagName;
        $Tags[$TagKey]['subname'] = $SubNames[$TagName];
        $Tags[$TagKey]['score'] = ($TagPositiveVotes[$TagKey] - $TagNegativeVotes[$TagKey]);
        $Tags[$TagKey]['id'] = $TorrentTagIDs[$TagKey];
        $Tags[$TagKey]['userid'] = $TorrentTagUserIDs[$TagKey];
        $TagNames[] = $SubNames[$TagName];
    }
    uasort($Tags, 'compare');
}
$TagsFormat = new Tags(implode(' ', $TagNames));



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

// Start output
View::show_header($Title, 'browse,comments,torrent,bbcode,recommend,cover_art,subscriptions,sendbonus,thumb', 'PageTorrentShow');
?>
<div class="LayoutBody">
    <div class="BodyHeader">
        <div class="BodyHeader-nav">
            <?= t('server.torrents.header') ?>
        </div>
        <div class="BodyNavLinks">
            <? if (check_perms('site_edit_wiki')) { ?>
                <a href="torrents.php?action=editgroup&amp;groupid=<?= $GroupID ?>" class="brackets"><?= t('server.common.edit') ?></a>
            <?  } ?>
            <a href="torrents.php?action=editrequest&amp;groupid=<?= $GroupID ?>" class="brackets"><?= t('server.torrents.editrequest') ?></a>
            <a href="torrents.php?action=history&amp;groupid=<?= $GroupID ?>" class="brackets"><?= t('server.torrents.viewhistory') ?></a>
            <? if ($RevisionID && check_perms('site_edit_wiki')) { ?>
                <a href="torrents.php?action=revert&amp;groupid=<?= $GroupID ?>&amp;revisionid=<?= $RevisionID ?>&amp;auth=<?= $LoggedUser['AuthKey'] ?>" class="brackets"><?= t('server.torrents.revert') ?></a>
            <?
            }
            if (Bookmarks::has_bookmarked('torrent', $GroupID)) {
            ?>
                <a href="#" id="bookmarklink_torrent_<?= $GroupID ?>" class="remove_bookmark brackets" onclick="Unbookmark('torrent', <?= $GroupID ?>, '<?= t('server.common.add_bookmark') ?>'); return false;"><?= t('server.common.remove_bookmark') ?></a>
            <?  } else { ?>
                <a href="#" id="bookmarklink_torrent_<?= $GroupID ?>" class="add_bookmark brackets" onclick="Bookmark('torrent', <?= $GroupID ?>, '<?= t('server.common.remove_bookmark') ?>'); return false;"><?= t('server.common.add_bookmark') ?></a>
            <?  } ?>
            <a href="#" id="subscribelink_torrents<?= $GroupID ?>" class="brackets" onclick="SubscribeComments('torrents', <?= $GroupID ?>, '<?= Subscriptions::has_subscribed_comments('torrents', $GroupID) !== false ? t('server.torrents.subscribe') : t('server.torrents.unsubscribe') ?>'); return false;"><?= Subscriptions::has_subscribed_comments('torrents', $GroupID) !== false ? t('server.torrents.unsubscribe') : t('server.torrents.subscribe') ?></a>
            <?
            if ($Categories[$GroupCategoryID - 1] == 'Movies') { ?>
                <a href="upload.php?groupid=<?= $GroupID ?>" class="brackets"><?= t('server.torrents.add_format') ?></a>
            <?
            }
            if (check_perms('site_submit_requests')) { ?>
                <a href="requests.php?action=new&type=1&amp;groupid=<?= $GroupID ?>" class="brackets"><?= t('server.torrents.req_format') ?></a>
            <?  } ?>
            <a href="torrents.php?action=grouplog&amp;groupid=<?= $GroupID ?>" class="brackets"><?= t('server.torrents.viewlog') ?></a>
        </div>
    </div>
    <!-- IMDB -->
    <div class="MovieInfo MovieInfoMovie Box">
        <div class="MovieInfo-left">
            <img class="MovieInfo-poster" src="<?= ImageTools::process($WikiImage) ?>" onclick="lightbox.init(this, $(this).width());">
        </div>
        <div class="MovieInfo-titleContainer">
            <span class="MovieInfo-title">
                <?= display_str($GroupName) ?>
            </span>
            <i class="MovieInfo-year">(<? print_r($GroupYear) ?>)</i>
            <? if ($SubName) {
                echo "<div class='MovieInfo-subTitle'>" . display_str($SubName) . "</div>";
            } ?>
        </div>
        <div class="MovieInfo-tagContainer">
            <div class="MovieInfo-facts">
                <a class="MovieInfo-fact" data-tooltip="<?= t('server.common.imdb_rating') ?>, <?= $IMDBVote . ' ' . t('server.torrents.movie_votes') ?>" target="_blank" href="https://www.imdb.com/title/<? print_r($IMDBID) ?>">
                    <?= icon('imdb') ?>
                    <span><?= !empty($IMDBRating) ? sprintf("%.1f", $IMDBRating) : '--' ?></span>
                </a>
                <a class="MovieInfo-fact" data-tooltip="<?= t('server.common.douban_rating') ?>, <?= ($DoubanVote ? $DoubanVote : '?') . ' ' . t('server.torrents.movie_votes') ?>" target="_blank" href="https://movie.douban.com/subject/<?= $DoubanID ?>/">
                    <?= icon('douban') ?>
                    <span><?= !empty($DoubanRating) ? sprintf("%.1f", $DoubanRating) : '--' ?></span>
                </a>
                <a class="MovieInfo-fact <?= empty($RTRating) ? 'lack_of_info' : '' ?>" data-tooltip="<?= t('server.common.rt_rating') ?>" target="_blank" href="https://www.rottentomatoes.com/m/<?= $RTTitle ?>">
                    <?= icon('rotten-tomatoes') ?>
                    <span><?= !empty($RTRating) ? $RTRating : '--' ?></span>
                </a>
                <a class="MovieInfo-fact" data-tooltip="<?= t('server.upload.director') ?>" href="/artist.php?id=<?= $Director['ArtistID'] ?>" dir="ltr">
                    <?= icon('movie-director') ?>
                    <span><?= Artists::display_artist($Director, false) ?></span>
                </a>
                <? if (!empty($Duration)) { ?>
                    <span class="MovieInfo-fact" data-tooltip="<?= t('server.torrents.imdb_runtime') ?>">
                        <?= icon('movie-runtime') ?>
                        <span><?= $Duration . ' ' . t('server.common.minutes') ?></span>
                    </span>
                <?  } ?>
                <? if (!empty($Region)) { ?>
                    <span class="MovieInfo-fact" data-tooltip="<?= t('server.torrents.imdb_region') ?>">
                        <?= icon('movie-country') ?>
                        <span><?= Torrents::format_region($Region) ?>
                        </span>
                    </span>
                <?  } ?>
                <? if (!empty($Language)) { ?>
                    <span class="MovieInfo-fact" data-tooltip="<?= t('server.torrents.imdb_language') ?>">
                        <?= icon('movie-language') ?>
                        <span><?= Torrents::format_language($Language) ?>
                        </span>
                    </span>
                <?  } ?>
                <span class="MovieInfo-fact" data-tooltip="<?= t('server.upload.movie_type') ?>">
                    <?= icon('movie-type') ?>
                    <span><?= t('server.torrents.release_types')[$ReleaseType] ?></span>
                </span>
            </div>
            <div class="MovieInfo-tags">
                <i>
                    <?= $TagsFormat->format('torrents.php?action=advanced&amp;taglist=', '', 'MovieInfo-tag')
                    ?>
                </i>
            </div>
        </div>

        <div class="MovieInfo-synopsis">
            <div class=" HtmlText">
                <?
                View::long_text('movie_info_synopsis', display_str($WikiBody), 2);
                ?>
            </div>
        </div>
        <div class="MovieInfo-artists u-hideScrollbar">
            <?
            for ($i = 0; $i < 7 && $i < count($Actors); $i++) {
            ?>
                <a class="MovieInfo-artist" href="<? echo " artist.php?id=" . $Actors[$i]['ArtistID'] ?>">
                    <img class="MovieInfo-artistPhoto <?= $Actors[$i]['Image'] ? '' : 'default_photo' ?>" src="<?= ImageTools::process($Actors[$i]['Image']) ?>">
                    <div class="MovieInfo-artistName" data-tooltip="<? echo Artists::get_artist_name($Actors[$i]) ?>"><? echo Artists::get_artist_name($Actors[$i]) ?></div>
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
                    <div class="SidebarItem-headerTitle">
                        <span><?= t('server.torrents.tag') ?></span>
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
                            <a class="brackets" href="#" onclick="$('#undo_tag_delete_form').raw().submit(); return false;"><?= t('server.torrents.undo_delete') ?></a>

                        <?              } ?>
                    </div>
                    <div class="SidebarItem-headerActions">
                        <?
                        if (empty($LoggedUser['DisableTagging'])) {
                        ?>
                            <a class="" onclick="$('#tag_edit_form').gtoggle(); return false;" href="#"><?= t('server.common.add') ?></a>'
                        <?
                        }
                        ?>
                    </div>
                </div>

                <?
                if (count($Tags) > 0) {
                ?>
                    <ul class="SidebarList SidebarItem-body Box-body">
                        <? foreach ($Tags as $TagKey => $Tag) { ?>
                            <li class="SidebarList-item u-hoverToShow-hover">
                                <a href="torrents.php?action=advanced&taglist=<?= Lang::choose_content($Tag['name'], $Tag['subname']) ?>"><?= display_str(Lang::choose_content($Tag['name'], $Tag['subname'])) ?></a>
                                <div class="SidebarList-actions">
                                    <? if (check_perms('users_warn')) { ?>
                                        <a class="SidebarList-action u-hoverToShow-hide" href="user.php?id=<?= $Tag['userid'] ?>" data-tooltip="<?= t('server.torrents.view_the_profile_of_the_user_that_added_this_tag') ?>">
                                            <?= icon('user') ?>
                                        </a>
                                    <? } ?>
                                    <? if (empty($LoggedUser['DisableTagging']) && check_perms('site_delete_tag')) { ?>
                                        <a class="SidebarList-action  u-hoverToShow-hide" href="torrents.php?action=delete_tag&amp;groupid=<?= $GroupID ?>&amp;tagid=<?= $Tag['id'] ?>&amp;auth=<?= $LoggedUser['AuthKey'] ?>" data-tooltip="<?= t('server.torrents.remove_tag') ?>">
                                            <?= icon('remove') ?>
                                        </a>
                                    <? } ?>
                                    <a class="SidebarList-action" href="torrents.php?action=vote_tag&amp;way=up&amp;groupid=<?= $GroupID ?>&amp;tagid=<?= $Tag['id'] ?>&amp;auth=<?= $LoggedUser['AuthKey'] ?>" data-tooltip="<?= t('server.torrents.vote_this_tag_up') ?>">
                                        <?= icon('vote-up') ?>
                                    </a>
                                    <span class="SidebarTags-score"><?= $Tag['score'] ?></span>
                                    <a class="SidebarList-action" href="torrents.php?action=vote_tag&amp;way=down&amp;groupid=<?= $GroupID ?>&amp;tagid=<?= $Tag['id'] ?>&amp;auth=<?= $LoggedUser['AuthKey'] ?>" data-tooltip="<?= t('server.torrents.vote_this_tag_down') ?>">
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
                        <li><?= t('server.torrents.there_are_no_tags_to_display') ?></li>
                    </ul>
                <?
                }
                ?>
            </div>
            <?
            if (empty($LoggedUser['DisableTagging'])) {
            ?>
                <div class="SidebarItemTagAdd SidebarItem Box hidden" id="tag_edit_form">
                    <div class="SidebarItem-header Box-header">
                        <div class="SidebarItem-headerTitle">
                            <span><?= t('server.torrents.add_tag') ?></span>
                        </div>
                        <div class="SidebarItem-headerActions">
                            <span><a href="rules.php?p=tag" class="brackets"><?= t('server.torrents.tag_rules') ?></a></span>
                        </div>
                    </div>
                    <div class="SidebarItem-body Box-body">
                        <form class="Fom-rowList FormTorrentAddTag" name="tags" action="torrents.php" method="post">
                            <input type="hidden" name="action" value="add_tag" />
                            <input type="hidden" name="auth" value="<?= $LoggedUser['AuthKey'] ?>" />
                            <input type="hidden" name="groupid" value="<?= $GroupID ?>" />
                            <div class="Form-row FormOneLine">
                                <input class="Input" type="text" <?= Users::has_autocomplete_enabled('search');
                                                                    ?> id="tagsearch" placeholder="<?= t('server.artist.search_auto_fill') ?>" size="17" />
                            </div>
                            <div class="Form-row FormOneLine">
                                <input class="Input" type="text" placeholder="<?= t('server.upload.english_name') ?>" id="tagname" name="tagname" size="17" />
                            </div>
                            <div class="Form-row FormOneLine">
                                <input class="Input" type="text" placeholder="<?= t('server.upload.sub_name') ?>" id="tagsubname" name="tagsubname" size="17" />
                            </div>
                            <div class="Form-row">
                                <input class="Button" type="submit" value="<?= t('server.common.add') ?>" />
                            </div>
                        </form>
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
                        <div class="SidebarItem-headerTitle">
                            <span><?= t('server.common.artist') ?></span>
                        </div>
                        <div class="SidebarItem-headerActions">
                            <?
                            if (check_perms('torrents_add_artist')) {
                            ?>
                                <a class="" onclick="$('#add_artist_form').gtoggle(); return false" href="#"><?= t('server.common.add') ?></a>
                            <?
                            }
                            ?>
                            <?= check_perms('torrents_edit') ? '<a class="" onclick="ArtistManager(); return false;" href="#">' . t('server.common.edit') . '</a>' : '' ?>
                        </div>
                    </div>
                    <ul class="SidebarItem-body Box-body SidebarList" id="artist_list">
                        <?
                        if (!empty($Directors) && count($Directors) > 0) {
                            print '<li class="SidebarList-item"><strong class="artists_label">' . t('server.torrents.director') . ':</strong></li>';
                        }
                        foreach ($Directors as $Artist) {
                        ?>
                            <li class="SidebarList-item u-hoverToShow-hover">
                                <?= Artists::display_artist($Artist) ?>
                                <?
                                if (check_perms('torrents_edit')) {
                                ?>
                                    <div class="SidebarList-actions">
                                        <a class="SidebarList-action u-hoverToShow-hide" href="javascript:void(0);" onclick="ajax.get('torrents.php?action=delete_alias&amp;auth=' + authkey + '&amp;groupid=<?= $GroupID ?>&amp;artistid=<?= $Artist['ArtistID'] ?>&amp;importance=1'); this.parentNode.parentNode.style.display = 'none';" data-tooltip="<?= t('server.torrents.remove_artist') ?>">
                                            <?= icon('remove') ?>
                                        </a>
                                    </div>
                                <? } ?>
                            </li>
                            <?
                        }


                        if (!empty($Writters) && count($Writters) > 0) {
                            print '<li class="SidebarList-item"><strong class="artists_label">' .  t('server.torrents.writer') . ':</strong></li>';
                            foreach ($Artists[2] as $Artist) {
                            ?>
                                <li class="SidebarList-item u-hoverToShow-hover">
                                    <?= Artists::display_artist($Artist) ?>
                                    <? if (check_perms('torrents_edit')) {
                                    ?>
                                        <div class="SidebarList-actions">
                                            <a class="SidebarList-action u-hoverToShow-hide" href="javascript:void(0);" onclick="ajax.get('torrents.php?action=delete_alias&amp;auth=' + authkey + '&amp;groupid=<?= $GroupID ?>&amp;artistid=<?= $Artist['ArtistID'] ?>&amp;importance=2'); this.parentNode.parentNode.style.display = 'none';" data-tooltip="<?= t('server.torrents.remove_artist') ?>">
                                                <?= icon('remove') ?>
                                            </a>
                                        </div>
                                    <?
                                    }
                                    ?>
                                </li>
                            <?
                            }
                        }


                        if (!empty($Producers) && count($Producers) > 0) {
                            print '<li class="SidebarList-item"><strong class="artists_label">' . t('server.torrents.movie_producer') . ':</strong></li>';
                            foreach ($Producers as $Artist) {
                            ?>
                                <li class="SidebarList-item u-hoverToShow-hover">
                                    <?= Artists::display_artist($Artist) ?>
                                    <? if (check_perms('torrents_edit')) {
                                    ?>
                                        <span class="SidebarList-actions">
                                            <a class="SidebarList-action  u-hoverToShow-hide" href="javascript:void(0);" onclick="ajax.get('torrents.php?action=delete_alias&amp;auth=' + authkey + '&amp;groupid=<?= $GroupID ?>&amp;artistid=<?= $Artist['ArtistID'] ?>&amp;importance=3'); this.parentNode.parentNode.style.display = 'none';" data-tooltip="<?= t('server.torrents.remove_artist') ?>">
                                                <?= icon('remove') ?>
                                            </a>
                                        </span>
                                    <?
                                    }
                                    ?>
                                </li>
                            <?
                            }
                        }
                        if (!empty($Composers) && count($Composers) > 0) {
                            print '<li class="SidebarList-item "><strong class="artists_label">' . t('server.torrents.composer') . ':</strong></li>';
                            foreach ($Composers as $Artist) {
                            ?>

                                <li class="SidebarList-item u-hoverToShow-hover">
                                    <?= Artists::display_artist($Artist) ?>
                                    <?
                                    if (check_perms('torrents_edit')) {
                                    ?>
                                        <span class="SidebarList-actions">
                                            <a class="SidebarList-action u-hoverToShow-hide" href="javascript:void(0);" onclick="ajax.get('torrents.php?action=delete_alias&amp;auth=' + authkey + '&amp;groupid=<?= $GroupID ?>&amp;artistid=<?= $Artist['ArtistID'] ?>&amp;importance=4'); this.parentNode.parentNode.style.display = 'none';" data-tooltip="<?= t('server.torrents.remove_artist') ?>">
                                                <?= icon('remove') ?>
                                            </a>
                                        </span>
                                    <?
                                    }
                                    ?>
                                </li>
                            <?
                            }
                        }
                        if (!empty($Cinematographers) && count($Cinematographers) > 0) {
                            print '<li class="SidebarList-item"><strong class="artists_label">' . t('server.torrents.cinematographer') . ':</strong></li>';
                            foreach ($Cinematographers as $Artist) {
                            ?>
                                <li class="SidebarList-item u-hoverToShow-hover">
                                    <?= Artists::display_artist($Artist) ?>
                                    <? if (check_perms('torrents_edit')) {
                                    ?>
                                        <span class="SidebarList-actions">
                                            <a class="SidebarList-action u-hoverToShow-hide" href="javascript:void(0);" onclick="ajax.get('torrents.php?action=delete_alias&amp;auth=' + authkey + '&amp;groupid=<?= $GroupID ?>&amp;artistid=<?= $Artist['ArtistID'] ?>&amp;importance=5'); this.parentNode.parentNode.style.display = 'none';" data-tooltip="<?= t('server.torrents.remove_conductor') ?>">
                                                <?= icon('remove') ?>
                                            </a>
                                        </span>
                                    <?
                                    } ?>
                                </li>
                            <?
                            }
                        }
                        if (!empty($Actors) && count($Actors) > 0) {
                            print '<li class="SidebarList-item"><strong class="artists_label">' . t('client.common.actor') . ':</strong></li>';
                            foreach ($Actors as $Artist) {
                            ?>
                                <li class="SidebarList-item u-hoverToShow-hover">
                                    <?= Artists::display_artist($Artist) ?>
                                    <? if (check_perms('torrents_edit')) {
                                    ?>
                                        <span class="SidebarList-actions">
                                            <a class="SidebarList-action u-hoverToShow-hide" href="javascript:void(0);" onclick="ajax.get('torrents.php?action=delete_alias&amp;auth=' + authkey + '&amp;groupid=<?= $GroupID ?>&amp;artistid=<?= $Artist['ArtistID'] ?>&amp;importance=6'); this.parentNode.parentNode.style.display = 'none';" data-tooltip="<?= t('server.torrents.remove_artist') ?>">
                                                <?= icon('remove') ?>
                                            </a>
                                        </span>
                                    <?
                                    } ?>
                                </li>
                        <?
                            }
                        }
                        ?>
                    </ul>
                </div>
                <? if (check_perms('torrents_add_artist')) { ?>
                    <div class="SidebarItemArtistAdd SidebarItem Box hidden" id="add_artist_form">
                        <div class="SidebarItem-header Box-header u-hoverToShow-hover">
                            <span><?= t('server.torrents.add_artist') ?></span>
                        </div>
                        <div class="SidebarItem-body Box-body">
                            <form class="FormTorrentAddArtist" name="artists" action="torrents.php" method="post">
                                <input type="hidden" name="action" value="add_alias" />
                                <input type="hidden" name="auth" value="<?= $LoggedUser['AuthKey'] ?>" />
                                <input type="hidden" name="groupid" value="<?= $GroupID ?>" />
                                <div class="Form-row FormOneLine">
                                    <input class="Input" type="text" <?= Users::has_autocomplete_enabled('search');
                                                                        ?> id="artist" placeholder=" <?= t('server.artist.search_auto_fill') ?>" id="artist" name="artist_id" size="17" />

                                </div>
                                <div class="Form-row FormOneLine">
                                    <input class="Input" type="text" placeholder="<?= t('server.upload.movie_imdb') ?>" id="artist_imdb" name="artist_id" size="17" />
                                    <select class="Input" name="importance">
                                        <option class="Select-option" value="1"><?= t('server.torrents.director') ?></option>
                                        <option class="Select-option" value="2"><?= t('server.torrents.writer') ?></option>
                                        <option class="Select-option" value="3"><?= t('server.torrents.movie_producer') ?></option>
                                        <option class="Select-option" value="4"><?= t('server.torrents.composer') ?></option>
                                        <option class="Select-option" value="5"><?= t('server.torrents.cinematographer') ?></option>
                                        <option class="Select-option" value="6"><?= t('client.common.actor') ?></option>
                                    </select>
                                </div>
                                <div class="Form-row FormOneLine">
                                    <input class="Input" type="text" placeholder="<?= t('server.upload.english_name') ?>" id="artist_name" name="artist" size="17" />
                                </div>
                                <div class="Form-row FormOneLine">
                                    <input class="Input" type="text" placeholder="<?= t('server.upload.sub_name') ?>" id="artist_sub_name" name="artist_sub" size="17" />
                                </div>


                                <div class="Form-row">
                                    <input class="Button" type="submit" value="<?= t('server.common.add') ?>" />
                                </div>
                            </form>
                        </div>
                    </div>
                <?
                }
            }
            if (CONFIG['ENABLE_COLLAGES']) {
                $Collages = $Cache->get_value("torrent_collages_$GroupID");
                if (!is_array($Collages)) {
                    $DB->query("
		SELECT c.Name, c.NumTorrents, c.ID
		FROM collages AS c
			JOIN collages_torrents AS ct ON ct.CollageID = c.ID
		WHERE ct.GroupID = '$GroupID'
			AND Deleted = '0'
			AND CategoryID != '$PersonalCollageCategoryCat'");
                    $Collages = $DB->to_array();
                    $Cache->cache_value("torrent_collages_$GroupID", $Collages, 600 * 6);
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
                    <div class="SidebarItem Box">
                        <div class="SidebarItem-header Box-header">
                            <div class="SidebarItem-headerTitle">
                                <?= t('server.collages.collages') ?>
                                <?= $SeeAll ?>
                            </div>
                            <div class="SidebarItem-headerActions">
                                <a class="" onclick="$('#add_collage_form').gtoggle(); return false;" href="#"><?= t('server.common.add') ?></a>
                            </div>
                        </div>
                        <ul class="SidebarList SidebarItem-body Box-body">
                            <?= t('server.torrents.this_album_is_in_collages', ['Values' => [
                                t('server.torrents.this_album_is_in_collages_count', ['Count' => $Collages, 'Values' => [number_format(count($Collages))]])
                            ]]) ?>
                            <? foreach ($Indices as $i) {
                                list($CollageName, $CollageTorrents, $CollageID) = $Collages[$i];
                                unset($Collages[$i]);
                            ?>
                                <li class="SidebarList-item">
                                    <a href="collages.php?id=<?= $CollageID ?>"><?= $CollageName ?></a>
                                </li>
                            <?  }
                            foreach ($Collages as $Collage) {
                                list($CollageName, $CollageTorrents, $CollageID) = $Collage;
                            ?>
                                <li class="SidebarList-item">
                                    <a href="collages.php?id=<?= $CollageID ?>"><?= $CollageName ?></a>
                                </li>
                            <?  } ?>
                        </ul>
                    </div>
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
			AND CategoryID = '$PersonalCollageCategoryCat'");
                    $PersonalCollages = $DB->to_array(false, MYSQLI_NUM);
                    $Cache->cache_value("torrent_collages_personal_$GroupID", $PersonalCollages, 600 * 6);
                }


                if (count($PersonalCollages) > 0) {
                    if (count($PersonalCollages) > MAX_PERS_COLLAGES) {
                        // Pick some at random
                        $Range = range(0, count($PersonalCollages) - 1);
                        shuffle($Range);
                        $Indices = array_slice($Range, 0, MAX_PERS_COLLAGES);
                        $SeeAll = ' <a href="#" onclick="$(\'.personal_rows\').gtoggle(); return false;">(' . t('server.common.see_full') . ')</a>';
                    } else {
                        $Indices = range(0, count($PersonalCollages) - 1);
                        $SeeAll = '';
                    }
                ?>
                    <div class="SidebarItem Box">
                        <div class="SidebarItem-header Box-header">
                            <div class="SidebarItem-headerTitle">
                                <?= t('server.collages.personal_collage') ?>
                                <?= $SeeAll ?>
                            </div>
                            <div class="SidebarItem-headerActions">
                                <a class="" onclick="$('#add_collage_form').gtoggle(); return false;" href="#"><?= t('server.common.add') ?></a>
                            </div>

                        </div>

                        <ul class="SidebarList SidebarItem-body Box-body">
                            <?= t('server.torrents.this_album_is_in_personal_collages', ['Values' => [
                                t('server.torrents.this_album_is_in_personal_collages_count', ['Count' => count($PersonalCollages), 'Values' => [
                                    number_format(count($PersonalCollages))
                                ]])
                            ]]) ?>
                            <? foreach ($Indices as $i) {
                                list($CollageName, $CollageTorrents, $CollageID) = $PersonalCollages[$i];
                                unset($PersonalCollages[$i]);
                            ?>
                                <li class="SidebarList-item">
                                    <a href="collages.php?id=<?= $CollageID ?>"><?= $CollageName ?></a>
                                </li>
                            <?  }
                            foreach ($PersonalCollages as $Collage) {
                                list($CollageName, $CollageTorrents, $CollageID) = $Collage;
                            ?>
                                <li class="SidebarList-item">
                                    <a href="collages.php?id=<?= $CollageID ?>"><?= $CollageName ?></a>
                                </li>
                            <?  } ?>
                        </ul>
                    </div>
            <?
                }
                include(CONFIG['SERVER_ROOT'] . '/sections/torrents/collage.php');
            }
            include(CONFIG['SERVER_ROOT'] . '/sections/torrents/vote_ranks.php');
            include(CONFIG['SERVER_ROOT'] . '/sections/torrents/vote.php');
            ?>
        </div>
        <div class="LayoutMainSidebar-main u-tab">
            <div class="TableContainer u-tabItem u-tabItemTorrent" style="<?= $View == 'slot' ? "display:none" : "" ?>">
                <?
                $TableTorrentClass = G::$LoggedUser['SettingTorrentTitle']['Alternative'] ? 'is-alternative' : '';
                ?>
                <table class="TableTorrent Table has-slots <?= $TableTorrentClass ?>" header-large last-tr-hidden id="torrent_details">
                    <tr class="Table-rowHeader">
                        <td class="TableTorrent-cellName Table-cell" colspan="1">
                            <span>
                                <?= t('server.common.torrents') ?>
                                <span> | <span>
                                        <a href='#' onclick='globalapp.toggleTab(event, ".u-tabItemSlot")'><?= t('server.torrents.slot_table') ?></a>
                                    </span>
                                    <? if ($HasRequest) { ?>
                                        | <span>
                                            <a href='#' onclick='globalapp.toggleTab(event, ".u-tabItemRequest")'><?= t('server.common.requests') ?></a>
                                        </span>
                                    <? } ?>
                        </td>
                        <td class="TableTorrent-cellSize Table-cell TableTorrent-cellStat TableTorrent-cellStatSize">
                            <span aria-hidden="true" data-tooltip="<?= t('server.common.size') ?>">
                                <?= icon('torrent-size') ?>
                            </span>
                        </td>
                        <td class="TableTorrent-cellSnatches Table-cell TableTorrent-cellStat">
                            <span aria-hidden="true" data-tooltip="<?= t('server.common.snatched') ?>">
                                <?= icon('torrent-snatches') ?>
                            </span>
                        </td>
                        <td class="TableTorrent-cellSeeders Table-cell TableTorrent-cellStat">
                            <i aria-hidden="true" data-tooltip="<?= t('server.common.seeders') ?>">
                                <?= icon('torrent-seeders') ?>
                            </i>
                        </td>
                        <td class="TableTorrent-cellLeechers Table-cell TableTorrent-cellStat">
                            <i aria-hidden="true" data-tooltip="<?= t('server.common.leechers') ?>">
                                <?= icon('torrent-leechers') ?>
                            </i>
                        </td>
                    </tr>
                    <tr class="TableTorrent-rowSlotFilter Table-row id=" slot_filter_container">
                        <td class="TableTorrent-cellSlotFilter Table-cell" colspan="5">
                            <div class="TableTorrent-slotFilters" id="slot_filter">
                                <!-- same line to prevent space -->
                                <a href="#" class="TableTorrent-slotFilterButton" data-slot="quality" onclick='globalapp.filterSlot(event, ["quality", "en_quality", "cn_quality", "feature"])' data-tooltip="<?= t('server.torrents.all_quality_slot') ?>"><?= icon('Torrent/slot_quality') ?></a>
                                <a href="#" class="TableTorrent-slotFilterButton" data-slot="cn_quality" onclick='globalapp.filterSlot(event, ["cn_quality"])' data-tooltip="<?= t('server.torrents.cn_quality_slot') ?>"><?= icon('Torrent/slot_cn_quality') ?></a>
                                <a href="#" class="TableTorrent-slotFilterButton" data-slot="en_quality" onclick='globalapp.filterSlot(event, ["en_quality"])' data-tooltip="<?= t('server.torrents.en_quality_slot') ?>"><?= icon('Torrent/slot_en_quality') ?></a>
                                <a href="#" class="TableTorrent-slotFilterButton" data-slot="retention" onclick='globalapp.filterSlot(event, ["retention"])' data-tooltip="<?= t('server.torrents.retention_slot') ?>"><?= icon('Torrent/slot_retention') ?></a>
                                <a href="#" class="TableTorrent-slotFilterButton" data-slot="feature" onclick='globalapp.filterSlot(event, ["feature"])' data-tooltip="<?= t('server.torrents.feature_slot') ?>"><?= icon('Torrent/slot_feature') ?></a>
                                <a href="#" class="TableTorrent-slotFilterButton" data-slot="remux" onclick='globalapp.filterSlot(event, ["remux"])' data-tooltip="<?= t('server.torrents.remux_slot') ?>"><?= icon('Torrent/slot_remux') ?></a>
                                <a href="#" class="TableTorrent-slotFilterButton" data-slot="untouched" onclick='globalapp.filterSlot(event, ["untouched"])' data-tooltip="<?= t('server.torrents.untouched_slot') ?>"><?= icon('Torrent/slot_untouched') ?></a>
                                <a href="#" class="TableTorrent-slotFilterButton" data-slot="diy" onclick='globalapp.filterSlot(event, ["diy"])' data-tooltip="<?= t('server.torrents.diy_slot') ?>"><?= icon('Torrent/slot_diy') ?></a>
                                <a href="#" class="TableTorrent-slotFilterButton" data-slot="clear" onclick='globalapp.filterSlot(event, [])' style="visibility: hidden;" data-tooltip="<?= t('server.torrents.clear_slot') ?>"><?= icon('Torrent/slot_clear') ?></a>
                            </div>
                        </td>
                    </tr>
                    <?
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
                                    <a class="u-toggleEdition-button" href="#" onclick="globalapp.toggleEdition(event, <?= $GroupID ?>, <?= $EditionID ?>)" data-tooltip="<?= t('server.common.collapse_this_edition_title') ?>">&minus;</a>
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
                        <tr class="TableTorrent-rowTitle Table-row releases_<?= $ReleaseType ?>  <?= $SnatchedGroupClass . $SnatchedTorrentClass ?>" id="torrent<?= $TorrentID ?>" group-id="<?= $GroupID ?>" edition-id="<?= $EditionID ?>" data-slot="<?= TorrentSlot::slot_filter_name($Slot) ?>" data-source="<?= $Source ?>" data-codec="<?= $Codec ?>" data-container="<?= $Container ?>" data-resolution="<?= $Resolution ?>" data-processing="<?= $Processing ?>">
                            <td class="Table-cell">
                                <div class="TableTorrent-title">
                                    <span class="TableTorrent-titleActions">
                                        [
                                        <a href="torrents.php?action=download&amp;id=<?= $TorrentID ?>&amp;authkey=<?= $LoggedUser['AuthKey'] ?>&amp;torrent_pass=<?= $LoggedUser['torrent_pass'] ?>" data-tooltip="<?= t('server.common.download') ?>"><?= ($HasFile ? 'DL' : 'Missing') ?></a>
                                        <? if ($CanUseToken) { ?>
                                            |
                                            <a href="torrents.php?action=download&amp;id=<?= $TorrentID ?>&amp;authkey=<?= $LoggedUser['AuthKey'] ?>&amp;torrent_pass=<?= $LoggedUser['torrent_pass'] ?>&amp;usetoken=1" data-tooltip="<?= t('server.common.use_fl_tokens') ?>" onclick="return confirm('<?= FL_confirmation_msg($Seeders, $Size) ?>');">FL</a>
                                        <? } ?>
                                        |
                                        <a href="reportsv2.php?action=report&amp;id=<?= $TorrentID ?>" data-tooltip="<?= t('server.torrents.report') ?>">RP</a>
                                        <? if ($CanEdit) { ?>
                                            |
                                            <a href="torrents.php?action=edit&amp;id=<?= $TorrentID ?>" data-tooltip="<?= t('server.common.edit') ?>">ED</a>
                                        <? }
                                        if ($CanDelete) { ?>
                                            |
                                            <a href="torrents.php?action=delete&amp;torrentid=<?= $TorrentID ?>" data-tooltip="<?= t('server.torrents.remove') ?>">RM</a>
                                        <? } ?>
                                        |
                                        <a href="torrents.php?torrentid=<?= $TorrentID ?>" data-tooltip="<?= t('server.torrents.permalink') ?>">PL</a>
                                        ]
                                    </span>
                                    <span class="TableTorrent-titleCheck">
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
                                            <a class="TableTorrent-check" href="javascript:void(0)" id="torrent<?= $TorrentID ?>_check1" style="display:<?= $TorrentChecked ? "inline-block" : "none" ?>;color:#649464;" data-tooltip="<?= t('server.torrents.checked_by', ['Values' => [$TorrentChecked ? $TorrentCheckedBy : $LoggedUser['Username']]]) ?>">
                                                <?= icon("Table/checked") ?>
                                            </a>
                                            <a class="TableTorrent-check" href="javascript:void(0)" id="torrent<?= $TorrentID ?>_check0" style="display:<?= $TorrentChecked ? "none" : "inline-block" ?>;color:#CF3434;" data-tooltip="<?= t('server.torrents.turn_me_green') ?>">
                                                <?= icon("Table/unchecked") ?>
                                            </a>
                                        <? } else { ?>
                                            <i class="TableTorrent-check" style="color: <?= $TorrentChecked ? "#74B274" : "#A6A6A6" ?>;" data-tooltip="<?= $TorrentChecked ? t('server.torrents.has_been_checked') : t('server.torrents.has_not_been_checked') ?><?= t('server.torrents.checked_explanation') ?>">
                                                <?= icon("Table/" . ($TorrentChecked ? "checked" : "unchecked")) ?>
                                            </i>
                                        <? } ?>
                                    </span>
                                    <a class="TableTorrent-titleTitle" data-tooltip-html href="#" onclick="globalapp.toggleTorrentDetail(event, '#torrent_detail_<?= $TorrentID ?>')">
                                        <?= $ExtraInfo; ?>
                                        <div data-tooltip-html-content>
                                            <div><?= t('server.torrents.' . TorrentSlot::slot_option_lang($Slot)) ?></div>
                                        </div>
                                    </a>

                            </td>
                            <td class="TableTorrent-cellSize Table-cell TableTorrent-cellStat TableTorrent-cellStatSize"><?= Format::get_size($Size) ?></td>
                            <td class="TableTorrent-cellSnatches Table-cell TableTorrent-cellStat"><?= number_format($Snatched) ?></td>
                            <td class="TableTorrent-cellSeeders Table-cell TableTorrent-cellStat <?= (($Seeders == 0) ? ' u-colorRatio00' : '') ?>"><?= number_format($Seeders) ?></td>
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
                    <table class="TableTorrent Table <?= $TableTorrentClass ?>" variant="slot" header-large>
                        <tr class="Table-rowHeader">
                            <td class="TableTorrent-cellName Table-cell" colspan="1">
                                <span>
                                    <a href="#" onclick="globalapp.toggleTab(event, '.u-tabItemTorrent')"><?= t('server.common.torrents') ?></a>
                                    <span> | </span>
                                    <?= t('server.torrents.slot_table') ?>
                                </span>
                                <a href="wiki.php?action=article&id=66" data-tooltip="<?= t('server.torrents.slot_wiki') ?>">[?]</a>
                                <? if ($HasRequest) { ?>
                                    | <span>
                                        <a href='#' onclick='globalapp.toggleTab(event, ".u-tabItemRequest")'><?= t('server.common.requests') ?></a>
                                    </span>
                                <? } ?>
                            </td>
                            <td class="TableTorrent-cellSize Table-cell TableTorrent-cellStat">
                                <span aria-hidden="true" data-tooltip="<?= t('server.common.size') ?>">
                                    <?= icon('torrent-size') ?>
                                </span>
                            </td>
                            <td class="TableTorrent-cellSlotName Table-cell Table-cellRight">
                                <span><?= t('server.torrents.slot_action') ?></span>
                                <?
                                if (check_perms("torrents_slot_edit")) {
                                ?>
                                    | <a href="#" onclick="$('#slot').submit(); return false"> <?= t('server.apply.saved') ?></a>
                                <?
                                }
                                ?>
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
                                <tr class=" TableTorrent-rowCategory Table-row" group-id="<?= $GroupID ?>">
                                    <td class="TableTorrent-cellCategory Table-cell" colspan="3">
                                        <a class="u-toggleEdition-button" href="#" onclick="globalapp.toggleEdition(event, <?= $GroupID ?>, <?= $EditionID ?>)" data-tooltip="<?= t('server.common.collapse_this_edition_title') ?>">&minus;</a>
                                        <?= $NewEdition ?>
                                    </td>
                                </tr>
                                <?
                            }
                            if (TorrentSlot::get_slot_resolution($Resolution) != TorrentSlot::get_slot_resolution($LastResolution)) {
                                $MissSlots = $MissingSlots[TorrentSlot::get_slot_resolution($Resolution)];
                                $MissSlotNames = [];
                                foreach ($MissSlots as $MissingSlot) {
                                    if ($MissingSlot == TorrentSlot::TorrentSlotTypeNone) {
                                        continue;
                                    }
                                    $SlotTooltip = TorrentSlot::empty_slot_tooltip($MissingSlot);
                                    $MissSlotNames[] = "<span data-tooltip='$SlotTooltip'><i>" . t('server.torrents.' . TorrentSlot::slot_option_lang($MissingSlot)) . "</i></span>";
                                }
                                if (count($MissSlotNames) > 0) {
                                ?>
                                    <tr class="TableTorrent-rowEmptySlotNote Table-row releases_<?= $ReleaseType ?>" group-id="<?= $GroupID ?>" edition-id="<?= $EditionID ?>">
                                        <td class="Table-cell TableTorrent-cellEmptySlotNote" colspan="3"><i><?= t('server.torrents.' . TorrentSlot::empty_slot_title(TorrentSlot::get_slot_resolution($Resolution))) ?></i><?= implode(' / ', $MissSlotNames) ?></td>
                                    </tr>
                                <?
                                }
                            }
                            if (!$Missing) {
                                ?>

                                <tr class="TableTorrent-rowTitle Table-row releases_<?= $ReleaseType ?>" group-id="<?= $GroupID ?>" edition-id="<?= $EditionID ?>" <?= !$Missing ? "id='torrent$TorrentID'" : '' ?> data-slot="<?= TorrentSlot::slot_filter_name($Slot) ?>">
                                    <td class="TableTorrent-cellName Table-cell">
                                        &nbsp;
                                        <?
                                        if (($Dupe || empty($Slot)) && check_perms("torrents_slot_edit")) {
                                            $TorrentInfo = "<strong style='display:inline' class='u-colorWarning'>$TorrentInfo</strong>";
                                        }
                                        ?>
                                        <a data-tooltip="<?= t('server.torrents.' . TorrentSlot::slot_option_lang($Slot)) ?>" href="#" onclick="globalapp.toggleTorrentDetail(event, '#torrent_slot_<?= $TorrentID ?>'); return false;"><?= $TorrentInfo ?></a>

                                    </td>
                                    <td class="TableTorrent-cellSize Table-cell TableTorrent-cellStat">
                                        <?= Format::get_size($Size) ?>
                                    </td>
                                    <?
                                    if (check_perms("torrents_slot_edit")) {
                                    ?>
                                        <td class="TableTorrent-cellSlotName Table-cellRight Table-cell">
                                            <input type="hidden" name="torrents[]" value="<?= $TorrentID ?>" />
                                            <select class="Input" name="slots[]">
                                                <?
                                                foreach (TorrentSlot::get_resolution_slots($Resolution) as $RSlot) {
                                                ?>
                                                    <?= TorrentSlot::slot_option($RSlot, false, $Slot, $IsExtraSlot) ?>
                                                <?
                                                }
                                                $extraName = t('server.torrents.additional_slots');
                                                ?>
                                                <optgroup class="Select-group" label="<?= $extraName ?>">
                                                    <?
                                                    foreach (TorrentSlot::get_resolution_slots($Resolution) as $RSlot) {
                                                        if ($RSlot == TorrentSlot::TorrentSlotTypeNone) {
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
                                            $SlotName = t("server.torrents.$SlotName");
                                        }
                                        if ($IsExtraSlot) {
                                            $SlotName .= '*';
                                        }
                                    ?>
                                        <td class="TableTorrent-cellSlotName Table-cellRight Table-cell"><?= $SlotName ?></td>
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


                    </table>
                </div>
            </form>
            <?
            if ($HasRequest) {
                $i = 0;
            ?>
                <div class="TableContainer">
                    <table class="TableTorrent TableRequest Table u-tabItem u-tabItemRequest <?= $TableTorrentClass ?>" style="<?= $View == 'request' ? "" : "display:none" ?>" id="torrent_details">
                        <tr class="Table-rowHeader">
                            <td class="TableRequest-cellName Table-cell" colspan="1">
                                <span>
                                    <a href="#" onclick="globalapp.toggleTab(event, '.u-tabItemTorrent')"><?= t('server.common.torrents') ?></a>
                                </span>
                                <span> | </span>
                                <span>
                                    <a href='#' onclick='globalapp.toggleTab(event, ".u-tabItemSlot")'><?= t('server.torrents.slot_table') ?></a>
                                </span>
                                <span> | </span>
                                <?= t('server.common.requests') ?>
                            </td>
                            <td class="Table-cell">
                                <?= t('server.requests.request_type') ?>
                            </td>
                            <td class="TableRequest-cellVotes Table-cell TableRequest-cellValue"><?= t('server.torrents.votes') ?></td>
                            <td class="TableRequest-cellBounty Table-cell TableRequest-cellValue"><?= t('server.torrents.bounty') ?></td>
                            <td class="Table-cell TableRequest-cellValue">
                                <?= t('server.requests.created') ?>
                            </td>
                        </tr>
                        <? foreach ($Requests as $Request) {
                            $RequestVotes = Requests::get_votes_array($Request['ID']);
                            $RequestType = $Request['RequestType'];

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
            <?
            }
            // Matched Votes
            include(CONFIG['SERVER_ROOT'] . '/sections/torrents/voter_picks.php');
            $Pages = Format::get_pages($Page, $NumComments, CONFIG['TORRENT_COMMENTS_PER_PAGE'], 9, '#comments');
            ?>
            <div class="Group">
                <div class="Group-header">
                    <div class="Group-headerTitle">
                        <?= t('server.collages.comments') ?>
                    </div>
                </div>
                <div class="Group-body" id="torrent_comments">
                    <? View::pages($Pages) ?>
                    <?
                    CommentsView::render_comments($Thread, $LastRead, "torrents.php?id=$GroupID");
                    ?>
                    <? View::pages($Pages) ?>
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
</div>
<?
View::show_footer([], 'torrents/index.js');
