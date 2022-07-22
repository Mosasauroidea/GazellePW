<?php

include(CONFIG['SERVER_ROOT'] . '/classes/torrenttable.class.php');
function compare($X, $Y) {
    return ($Y['count'] - $X['count']);
}

// Build the data for the collage and the torrent list
// TODO: Cache this
$DB->query("
	SELECT
		ct.GroupID,
		ct.UserID
	FROM collages_torrents AS ct
		JOIN torrents_group AS tg ON tg.ID = ct.GroupID
	WHERE ct.CollageID = '$CollageID'
	ORDER BY ct.Sort");

$GroupIDs = $DB->collect('GroupID');
$Contributors = $DB->to_pair('GroupID', 'UserID', false);
if (count($GroupIDs) > 0) {
    $TorrentList = Torrents::get_groups($GroupIDs);
    $UserVotes = Votes::get_user_votes($LoggedUser['ID']);
} else {
    $TorrentList = array();
}

$NumGroups = count($TorrentList);
$NumGroupsByUser = 0;
$TopArtists = array();
$UserAdditions = array();
$Number = 0;

list($NumComments, $Page, $Thread, $LastRead) = Comments::load('collages', $CollageID);

// We loop through all groups building some basic statistics for them
// for the header of the collage page, and then we have to build the
// HTML inline instead of doing it all up here. Yeah, it's more complicated
// but the memory savings are a lot
foreach ($GroupIDs as $Idx => $GroupID) {
    if (!isset($TorrentList[$GroupID])) {
        unset($GroupIDs[$Idx]);
        continue;
    }
    $Group = $TorrentList[$GroupID];
    extract(Torrents::array_group($Group));
    $UserID = $Contributors[$GroupID];
    new Tags($TagList);

    // Handle stats and stuff
    $Number++;
    if ($UserID == $LoggedUser['ID']) {
        $NumGroupsByUser++;
    }

    foreach ($Artists as $Importance => $ImportanceArtists) {
        foreach ($ImportanceArtists as $Artist) {
            if (!isset($TopArtists[$Artist['id']])) {
                $TopArtists[$Artist['id']] = array('data' => $Artist, 'count' => 1);
            } else {
                $TopArtists[$Artist['id']]['count']++;
            }
        }
    }

    if (!isset($UserAdditions[$UserID])) {
        $UserAdditions[$UserID] = 0;
    }
    $UserAdditions[$UserID]++;
}

// Re-index the array so we can abuse that later to slice parts out of it
$GroupIDs = array_values($GroupIDs);

if ($CollageCategoryID === '0' && !check_perms('site_collages_delete')) {
    if (!check_perms('site_collages_personal') || $CreatorID !== $LoggedUser['ID']) {
        $PreventAdditions = true;
    }
}

if (
    !check_perms('site_collages_delete')
    && ($Locked
        || ($MaxGroups > 0 && $NumGroups >= $MaxGroups)
        || ($MaxGroupsPerUser > 0 && $NumGroupsByUser >= $MaxGroupsPerUser))
) {
    $PreventAdditions = true;
}

// Silly hack for people who are on the old setting
$CollageCovers = isset($LoggedUser['CollageCovers']) ? $LoggedUser['CollageCovers'] : 25 * (abs($LoggedUser['HideCollage'] - 1));

View::show_header($Name, 'browse,collage,bbcode,voting,recommend', 'PageCollageTorrent');
?>
<div class="LayoutBody">
    <div class="BodyHeader">
        <h2 class="BodyHeader-nav"><?= $Name ?></h2>
        <div class="BodyNavLinks">
            <a href="collages.php" class="brackets">
                <?= Lang::get('collages.collages_list') ?></a>
            <? if (check_perms('site_collages_create')) { ?>
                <a href="collages.php?action=new" class="brackets">
                    <?= Lang::get('collages.create_collages') ?></a>
            <?  } ?>
        </div>
        <div class="BodyNavLinks">
            <? if (check_perms('site_collages_subscribe')) { ?>
                <a href="#" id="subscribelink<?= $CollageID ?>" class="brackets" onclick="CollageSubscribe(<?= $CollageID ?>); return false;"><?= (in_array($CollageID, $CollageSubscriptions) ? Lang::get('collages.unsubscribe') : Lang::get('collages.subscribe')) ?></a>
            <?
            }
            if (check_perms('site_collages_delete') || (check_perms('site_edit_wiki') && !$Locked)) {
            ?>
                <a href="collages.php?action=edit&amp;collageid=<?= $CollageID ?>" class="brackets">
                    <?= Lang::get('collages.edit_collage') ?></a>
            <?  } else { ?>
                <span class="brackets"><?= Lang::get('collages.locked') ?></span>
            <?
            }
            if (Bookmarks::has_bookmarked('collage', $CollageID)) {
            ?>
                <a href="#" id="bookmarklink_collage_<?= $CollageID ?>" class="brackets" onclick="Unbookmark('collage', <?= $CollageID ?>, '<?= Lang::get('global.add_bookmark') ?>'); return false;">
                    <?= Lang::get('global.remove_bookmark') ?></a>
            <?  } else { ?>
                <a href="#" id="bookmarklink_collage_<?= $CollageID ?>" class="brackets" onclick="Bookmark('collage', <?= $CollageID ?>, '<?= Lang::get('global.remove_bookmark') ?>'); return false;">
                    <?= Lang::get('global.add_bookmark') ?></a>
            <?  } ?>
            <!-- <a href="#" id="recommend" class="brackets">Recommend</a> -->
            <?
            if (check_perms('site_collages_manage') && !$Locked) {
            ?>
                <a href="collages.php?action=manage&amp;collageid=<?= $CollageID ?>" class="brackets">
                    <?= Lang::get('collages.manage_torrents') ?></a>
            <?  } ?>
            <a href="reports.php?action=report&amp;type=collage&amp;id=<?= $CollageID ?>" class="brackets">
                <?= Lang::get('collages.report_collage') ?></a>
            <? if (check_perms('site_collages_delete') || $CreatorID == $LoggedUser['ID']) { ?>
                <a href="collages.php?action=delete&amp;collageid=<?= $CollageID ?>&amp;auth=<?= $LoggedUser['AuthKey'] ?>" class="brackets" onclick="return confirm('<?= Lang::get('collages.delete_confirm') ?>');">
                    <?= Lang::get('global.delete') ?></a>
            <?  } ?>
        </div>
    </div>
    <? /* Misc::display_recommend($CollageID, "collage"); */ ?>
    <div class="LayoutMainSidebar">
        <div class="Sidebar LayoutMainSidebar-sidebar">
            <div class="SidebarItemCollageCategory SidebarItem Box">
                <div class="SidebarItem-header Box-header">
                    <strong><?= Lang::get('collages.category') ?></strong>
                </div>
                <div class="SidebarItem-body Box-body">
                    <a href="collages.php?action=search&amp;cats[<?= (int)$CollageCategoryID ?>]=1"><?= Lang::get('collages.collagecats')[(int)$CollageCategoryID] ?></a>
                </div>
            </div>
            <div class="SidebarItemCollageDescription SidebarItem Box">
                <div class="SidebarItem-header Box-header">
                    <strong><?= Lang::get('collages.description') ?></strong>
                </div>
                <div class="SidebarItem-body Box-body HtmlText">
                    <?= Text::full_format($Description) ?>
                </div>
            </div>
            <div class="SidebarItemStats SidebarItem Box">
                <div class="SidebarItem-header Box-header">
                    <strong><?= Lang::get('collages.statistics') ?></strong>
                </div>
                <ul class="SidebarList SidebarItem-body Box-body">
                    <li class="SidebarList-item">
                        <?= Lang::get('global.torrents') ?>: <?= number_format($NumGroups) ?></li>
                    <? if (!empty($TopArtists)) { ?>
                        <li class="SidebarList-item">
                            <?= Lang::get('global.artists') ?>: <?= number_format(count($TopArtists)) ?></li>
                    <? } ?>
                    <li class="SidebarList-item">
                        <?= Lang::get('collages.subscribers') ?>: <?= number_format((int)$Subscribers) ?></li>
                    <li class="SidebarList-item">
                        <?= Lang::get('collages.built_by') ?> <?= number_format(count($UserAdditions)) ?>
                        <?= Lang::get('collages.user') ?><?= (count($UserAdditions) > 1 ? Lang::get('collages.users') : '') ?></li>
                    <li class="SidebarList-item">
                        <?= Lang::get('collages.last_updated') ?>: <?= time_diff($Updated) ?></li>
                </ul>
            </div>
            <div class="SidebarItemTags SidebarItem Box">
                <div class="SidebarItem-header Box-header">
                    <strong><?= Lang::get('collages.top_tags') ?></strong>
                </div>
                <ul class="SidebarItem-body Box-body SidebarList">
                    <?
                    Tags::format_top(5, 'collages.php?action=search&amp;tags=', '', 'SidebarList-item');
                    ?>
                </ul>
            </div>
            <? if (!empty($TopArtists)) { ?>
                <div class="SidebarItemArtists SidebarItem Box">
                    <div class="SidebarItem-header Box-header">
                        <strong><?= Lang::get('collages.top_artists') ?></strong>
                    </div>
                    <ul class="SidebarList SidebarItem-body Box-body">
                        <?
                        uasort($TopArtists, 'compare');
                        $i = 0;
                        foreach ($TopArtists as $ID => $Artist) {
                            $i++;
                            if ($i > 10) {
                                break;
                            }
                        ?>
                            <li class="Sidebar-item">
                                <a href="artist.php?id=<?= $ID ?>"><?= Artists::display_artist($Artist['data']) ?></a> (<?= number_format($Artist['count']) ?>)
                            </li>
                        <?
                        }
                        ?>
                    </ul>
                </div>
            <?  } ?>
            <div class="SidebarItemCollageContributors SidebarItem Box">
                <div class="SidebarItem-header Box-header">
                    <strong><?= Lang::get('collages.top_contributors') ?></strong>
                </div>
                <ul class="SidebarList SidebarItem-body Box-body">
                    <?
                    arsort($UserAdditions);
                    $i = 0;
                    foreach ($UserAdditions as $UserID => $Additions) {
                        $i++;
                        if ($i > 5) {
                            break;
                        }
                    ?>
                        <li class="SidebarList-item"><?= Users::format_username($UserID, false, false, false) ?> (<?= number_format($Additions) ?>)</li>
                    <?
                    }
                    ?>
                </ul>
            </div>
            <? if (check_perms('site_collages_manage') && !isset($PreventAdditions)) {
                $BatchAdd = Lang::get('collages.batch_add');
                $IndividualAdd = Lang::get('collages.individual_add');
            ?>
                <div class="SidebarItemCollageTorrentAdd SidebarItem Box">
                    <div class="SidebarItem-header Box-header">
                        <strong><?= Lang::get('collages.add_torrent_group') ?></strong>
                        <span class="floatright">
                            <a href="#" onclick="$('.add_torrent_container').toggle_class('hidden'); this.innerHTML = (this.innerHTML == '<?= $BatchAdd ?>' ? '<?= $IndividualAdd ?>' : '<?= $BatchAdd ?>'); return false;" class="brackets"><?= Lang::get('collages.batch_add') ?></a></span>
                    </div>
                    <div class="pad add_torrent_container">
                        <form class="FormOneLine FormCollageAddTorrentGroup" name="torrent" action="collages.php" method="post">
                            <input type="hidden" name="action" value="add_torrent" />
                            <input type="hidden" name="auth" value="<?= $LoggedUser['AuthKey'] ?>" />
                            <input type="hidden" name="collageid" value="<?= $CollageID ?>" />
                            <div class="field_div">
                                <input class="Input" type="text" size="20" name="url" />
                            </div>
                            <div class="submit_div">
                                <input class="Button" type="submit" value="<?= Lang::get('global.add') ?>" />
                            </div>
                        </form>
                        <span style="font-style: italic;">
                            <?= Lang::get('collages.add_torrent_group_note1') ?></span>

                    </div>
                    <div class="pad hidden add_torrent_container">
                        <form class="FormOneLine FormCollageAdd" name="torrents" action="collages.php" method="post">
                            <input type="hidden" name="action" value="add_torrent_batch" />
                            <input type="hidden" name="auth" value="<?= $LoggedUser['AuthKey'] ?>" />
                            <input type="hidden" name="collageid" value="<?= $CollageID ?>" />
                            <div class="field_div">
                                <textarea class="Input" name="urls" rows="5" cols="25" style="white-space: pre; word-wrap: normal; overflow: auto;"></textarea>
                            </div>
                            <div class="submit_div">
                                <input class="Button" type="submit" value="<?= Lang::get('global.add') ?>" />
                            </div>
                        </form>
                        <span style="font-style: italic;">
                            <?= Lang::get('collages.add_torrent_group_note2') ?></span>

                    </div>
                </div>
            <? } ?>
        </div>
        <div class="LayoutMainSidebar-main">
            <div class="Box">
                <div class="Box-body">
                    <?
                    $Groups = [];
                    foreach ($GroupIDs as $GroupID) {
                        $Groups[] = $TorrentList[$GroupID];
                    }
                    $tableRender = new TorrentGroupCoverTableView($Groups);
                    $tableRender->render();
                    $Pages = Format::get_pages($Page, $NumComments, CONFIG['TORRENT_COMMENTS_PER_PAGE'], 9, '#comments');
                    ?>
                </div>
            </div>
            <div class="u-vstack" id="torrent_comments">
                <div class="BodyNavLinks"><a name="comments"></a>
                    <?= $Pages ?>
                </div>
                <?
                CommentsView::render_comments($Thread, $LastRead, "collages.php?id=$CollageID");
                ?>
                <div class="BodyNavLinks">
                    <?= $Pages ?>
                </div>
                <?
                View::parse('generic/reply/quickreply.php', array(
                    'InputName' => 'pageid',
                    'InputID' => $CollageID,
                    'Action' => 'comments.php?page=collages',
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
