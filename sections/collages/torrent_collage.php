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
    $Artists = $Group['Artists'];
    $UserID = $Contributors[$GroupID];
    new Tags(Torrents::tags($Group));

    // Handle stats and stuff
    $Number++;
    if ($UserID == G::$LoggedUser['ID']) {
        $NumGroupsByUser++;
    }
    $ArtistSet = [];

    foreach ($Artists as $Importance => $ImportanceArtists) {
        foreach ($ImportanceArtists as $Artist) {
            if (isset($ArtistSet[$Artist['ArtistID']])) {
                continue;
            }
            if (!isset($TopArtists[$Artist['ArtistID']])) {
                $TopArtists[$Artist['ArtistID']] = array('data' => $Artist, 'count' => 1);
            } else {
                $TopArtists[$Artist['ArtistID']]['count']++;
            }
            $ArtistSet[$Artist['ArtistID']] = true;
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

View::show_header($Name, 'browse,collage,comments,bbcode,voting,recommend', 'PageCollageTorrent');
?>
<div class="LayoutBody">
    <div class="BodyHeader">
        <h2 class="BodyHeader-nav"><?= $Name ?></h2>
        <div class="BodyNavLinks">
            <a href="collages.php" class="brackets">
                <?= t('server.collages.collages_list') ?></a>
            <? if (check_perms('site_collages_create')) { ?>
                <a href="collages.php?action=new" class="brackets">
                    <?= t('server.collages.create_collages') ?></a>
            <?  } ?>
        </div>
        <div class="BodyNavLinks">
            <? if (check_perms('site_collages_subscribe')) { ?>
                <a href="#" id="subscribelink<?= $CollageID ?>" class="brackets" onclick="CollageSubscribe(<?= $CollageID ?>, '<?= (in_array($CollageID, $CollageSubscriptions) ? t('server.collages.subscribe') : t('server.collages.unsubscribe')) ?>'); return false;"><?= (in_array($CollageID, $CollageSubscriptions) ? t('server.collages.unsubscribe') : t('server.collages.subscribe')) ?></a>
            <?
            }
            if (check_perms('site_collages_delete') || (check_perms('site_edit_wiki') && !$Locked)) {
            ?>
                <a href="collages.php?action=edit&amp;collageid=<?= $CollageID ?>" class="brackets">
                    <?= t('server.collages.edit_collage') ?></a>
            <?  } else { ?>
                <span class="brackets"><?= t('server.collages.locked') ?></span>
            <?
            }
            if (Bookmarks::has_bookmarked('collage', $CollageID)) {
            ?>
                <a href="#" id="bookmarklink_collage_<?= $CollageID ?>" class="brackets" onclick="Unbookmark('collage', <?= $CollageID ?>, '<?= t('server.common.add_bookmark') ?>'); return false;">
                    <?= t('server.common.remove_bookmark') ?></a>
            <?  } else { ?>
                <a href="#" id="bookmarklink_collage_<?= $CollageID ?>" class="brackets" onclick="Bookmark('collage', <?= $CollageID ?>, '<?= t('server.common.remove_bookmark') ?>'); return false;">
                    <?= t('server.common.add_bookmark') ?></a>
            <?  } ?>
            <!-- <a href="#" id="recommend" class="brackets">Recommend</a> -->
            <?
            if (check_perms('site_collages_manage') && !$Locked) {
            ?>
                <a href="collages.php?action=manage&amp;collageid=<?= $CollageID ?>" class="brackets">
                    <?= t('server.collages.manage_collage') ?></a>
            <?  } ?>
            <a href="reports.php?action=report&amp;type=collage&amp;id=<?= $CollageID ?>" class="brackets">
                <?= t('server.collages.report_collage') ?></a>
            <? if (check_perms('site_collages_delete') || $CreatorID == $LoggedUser['ID']) { ?>
                <a href="collages.php?action=delete&amp;collageid=<?= $CollageID ?>&amp;auth=<?= $LoggedUser['AuthKey'] ?>" class="brackets">
                    <?= t('server.common.delete') ?></a>
            <?  } ?>
        </div>
    </div>
    <? /* Misc::display_recommend($CollageID, "collage"); */ ?>
    <div class="LayoutMainSidebar">
        <div class="Sidebar LayoutMainSidebar-sidebar">
            <div class="SidebarItemCollageCategory SidebarItem Box">
                <div class="SidebarItem-header Box-header">
                    <strong><?= t('server.collages.category') ?></strong>
                </div>
                <div class="SidebarItem-body Box-body">
                    <a href="collages.php?action=search&amp;cats[<?= (int)$CollageCategoryID ?>]=1"><?= t('server.collages.collagecats')[(int)$CollageCategoryID] ?></a>
                </div>
            </div>

            <div class="SidebarItemStats SidebarItem Box">
                <div class="SidebarItem-header Box-header">
                    <strong><?= t('server.collages.statistics') ?></strong>
                </div>
                <ul class="SidebarList SidebarItem-body Box-body">
                    <li class="SidebarList-item">
                        <?= t('server.common.torrents') ?>: <?= number_format($NumGroups) ?></li>
                    <? if (!empty($TopArtists)) { ?>
                        <li class="SidebarList-item">
                            <?= t('server.common.artists') ?>: <?= number_format(count($TopArtists)) ?></li>
                    <? } ?>
                    <li class="SidebarList-item">
                        <?= t('server.collages.subscribers') ?>: <?= number_format((int)$Subscribers) ?></li>
                    <li class="SidebarList-item">
                        <?= t('server.collages.built_by_n_users', ['Values' => [
                            number_format(count($UserAdditions)),
                            t('server.collages.user', ['Count' => count($UserAdditions)])
                        ]]) ?>
                    </li>
                    <li class="SidebarList-item">
                        <?= t('server.collages.last_updated') ?>: <?= time_diff($Updated) ?></li>
                </ul>
            </div>
            <div class="SidebarItemTags SidebarItem Box">
                <div class="SidebarItem-header Box-header">
                    <strong><?= t('server.collages.top_tags') ?></strong>
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
                        <strong><?= t('server.collages.top_artists') ?></strong>
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
                    <strong><?= t('server.collages.top_contributors') ?></strong>
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
                $BatchAdd = t('server.collages.batch_add');
                $IndividualAdd = t('server.collages.individual_add');
            ?>
                <div class="SidebarItemCollageTorrentAdd SidebarItem Box">
                    <div class="SidebarItem-header Box-header">
                        <strong><?= t('server.collages.add_torrent_group') ?></strong>
                        <div class="SidebarItem-headerActions"> <a href="#" onclick="$('.add_torrent_container').toggle_class('hidden'); this.innerHTML = (this.innerHTML == '<?= $BatchAdd ?>' ? '<?= $IndividualAdd ?>' : '<?= $BatchAdd ?>'); return false;" class="brackets"><?= t('server.collages.batch_add') ?></a>
                        </div>
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
                                <input class="Button" type="submit" value="<?= t('server.common.add') ?>" />
                            </div>
                        </form>
                        <span style="font-style: italic;">
                            <?= t('server.collages.add_torrent_group_note1') ?></span>

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
                                <input class="Button" type="submit" value="<?= t('server.common.add') ?>" />
                            </div>
                        </form>
                        <span style="font-style: italic;">
                            <?= t('server.collages.add_torrent_group_note2') ?></span>

                    </div>
                </div>
            <? } ?>
        </div>
        <div class="LayoutMainSidebar-main">
            <div class="Group">
                <div class="Group-header">
                    <div class="Group-headerTitle">
                        <strong><?= t('server.collages.description') ?></strong>
                    </div>
                </div>
                <div class="Group-body HtmlText">
                    <?= Text::full_format($Description) ?>
                </div>
            </div>
            <div class="Group">
                <div class="Group-header">
                    <div class="Group-headerTitle">
                        <?= t('server.index.moviegroups') ?>
                    </div>
                    <div class="Group-headerActions">
                        <? renderTorrentViewButton(TorrentViewScene::Collage);
                        ?>
                    </div>
                </div>
                <div class="Group-body">
                    <?
                    $Groups = [];
                    foreach ($GroupIDs as $GroupID) {
                        $Groups[] = $TorrentList[$GroupID];
                    }
                    $tableRender = newGroupTorrentView(TorrentViewScene::Collage, $Groups);
                    $tableRender->render();
                    $Pages = Format::get_pages($Page, $NumComments, CONFIG['TORRENT_COMMENTS_PER_PAGE'], 9, '#comments');
                    ?>
                </div>
            </div>
            <div class="Group">
                <div class="Group-header">
                    <div class="Group-headerTitle">
                        <?= t('server.collages.comments') ?>
                    </div>
                </div>
                <div class="Group-body" id="torrent_comments">
                    <? View::pages($Pages) ?>
                    <?
                    CommentsView::render_comments($Thread, $LastRead, "collages.php?id=$CollageID");
                    ?>
                    <? View::pages($Pages) ?>
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
</div>
<?
View::show_footer();
