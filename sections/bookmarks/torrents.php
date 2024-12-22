<?php
include(CONFIG['SERVER_ROOT'] . '/classes/torrenttable.class.php');
ini_set('max_execution_time', 600);
set_time_limit(0);

//~~~~~~~~~~~ Main bookmarks page ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~//

function compare($X, $Y) {
    return ($Y['count'] - $X['count']);
}

if (!empty($_GET['userid'])) {
    if (!check_perms('users_override_paranoia')) {
        error(403);
    }
    $UserID = $_GET['userid'];
    if (!is_number($UserID)) {
        error(404);
    }
    $DB->query("
		SELECT Username
		FROM users_main
		WHERE ID = '$UserID'");
    list($Username) = $DB->next_record();
} else {
    $UserID = $LoggedUser['ID'];
}

$Sneaky = $UserID !== $LoggedUser['ID'];
$Title = $Sneaky ? "$Username" . t('server.bookmarks.s_bookmarked_torrent_groups') : t('server.bookmarks.your_bookmarked_torrent_groups');

$NumGroups = 0;
$ArtistCount = array();

list($GroupIDs, $CollageDataList, $TorrentList) = Users::get_bookmarks($UserID);
foreach ($GroupIDs as $Idx => $GroupID) {
    if (!isset($TorrentList[$GroupID])) {
        unset($GroupIDs[$Idx]);
        continue;
    }
    // Handle stats and stuff
    $NumGroups++;
    extract(Torrents::array_group($TorrentList[$GroupID]));
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
    new Tags(Torrents::tags($TorrentList[$GroupID]));
}

$GroupIDs = array_values($GroupIDs);

$CollageCovers = isset($LoggedUser['CollageCovers']) ? (int)$LoggedUser['CollageCovers'] : 25;

View::show_header($Title, 'browse,collage', 'PageBookmarkTorrent');
?>
<div class="LayoutBody">
    <div class="BodyHeader">
        <h2 class="BodyHeader-nav TorrentViewWrapper">
            <div class="RssTitle">
                <? if (!$Sneaky) {
                ?><a target="_blank" data-tooltip="<?= t('server.user.rss_address') ?>" href="feeds.php?feed=torrents_bookmarks_t_<?= $LoggedUser['torrent_pass'] ?>&amp;user=<?= $LoggedUser['ID'] ?>&amp;auth=<?= $LoggedUser['RSS_Auth'] ?>&amp;passkey=<?= $LoggedUser['torrent_pass'] ?>&amp;authkey=<?= $LoggedUser['AuthKey'] ?>&amp;name=<?= urlencode(CONFIG['SITE_NAME'] . ': Bookmarked Torrents') ?>">
                        <?= icon('rss') ?></a>
                    <?
                } ?><?= $Title ?>
            </div>
            <?
            renderTorrentViewButton(TorrentViewScene::Bookmark);
            ?>
        </h2>
        <div class="BodyNavLinks">
            <a href="bookmarks.php?type=torrents" class="brackets"><?= t('server.index.moviegroups') ?></a>
            <a href="bookmarks.php?type=artists" class="brackets"><?= t('server.common.artists') ?></a>
            <?
            if (CONFIG['ENABLE_COLLAGES']) {
            ?>
                <a href="bookmarks.php?type=collages" class="brackets"><?= t('server.bookmarks.collages') ?></a>
            <?
            }
            ?>
            <a href="bookmarks.php?type=requests" class="brackets"><?= t('server.common.requests') ?></a>
            <? if (count($TorrentList) > 0) { ?>
        </div>
        <div class="BodyNavLinks">
            <a href="bookmarks.php?action=remove_snatched&amp;auth=<?= $LoggedUser['AuthKey'] ?>" class="brackets" onclick="return confirm('<?= t('server.bookmarks.remove_snatched_confirm') ?>');"><?= t('server.bookmarks.remove_snatched') ?></a>
            <a href="bookmarks.php?action=edit&amp;type=torrents" class="brackets"><?= t('server.bookmarks.manage_torrents') ?></a>
        <? } ?>
        </div>
    </div>
    <? if (count($TorrentList) === 0) { ?>
        <div>
            <div class="center">
                <div><?= t('server.bookmarks.no_bookmarked_torrents') ?></div>
            </div>
        </div>
</div>
<!--content-->
<?
        View::show_footer();
        die();
    } ?>
<div class="LayoutMainSidebar">
    <div class="Sidebar LayoutMainSidebar-sidebar">
        <div class="SidebarItemStats SidebarItem Box">
            <div class="SidebarItem-header Box-header">
                <strong><?= t('server.bookmarks.stats') ?></strong>
            </div>
            <ul class="SidebarList SidebarItem-body Box-body">
                <li class="SidebarList-item"><?= t('server.bookmarks.torrent_groups') ?>: <?= $NumGroups ?></li>
                <li class="SidebarList-item"><?= t('server.common.artists') ?>: <?= count($ArtistCount) ?></li>
            </ul>
        </div>
        <div class="SidebarItemTags SidebarItem Box">
            <div class="SidebarItem-header Box-header"><strong><?= t('server.bookmarks.top_tags') ?></strong></div>
            <div class="SidebarList SidebarItem-body Box-body">
                <? Tags::format_top(5, 'torrents.php?action=advanced&taglist=', '', 'SidebarList-item') ?>
            </div>
        </div>
        <div class="SidebarItemArtists SidebarItem Box">
            <div class="SidebarItem-header Box-header">
                <strong><?= t('server.bookmarks.top_artists') ?></strong>
            </div>
            <div class="SidebarList SidebarItem-body Box-body">
                <?
                $Indent = "\t\t\t\t";
                if (!empty($TopArtists)) {
                    uasort($TopArtists, 'compare');
                    $i = 0;
                    foreach ($TopArtists as $ID => $Artist) {
                        $i++;
                        if ($i > 10) {
                            break;
                        }
                ?>
                        <li class="SidebarList-item">
                            <div><a href="artist.php?id=<?= $ID ?>"><?= Artists::display_artist($Artist['data']) ?></a> (<?= number_format($Artist['count']) ?>)</div>
                        </li>
                <?
                    }
                    echo "$Indent</ol>\n";
                } else {
                    echo "$Indent<ul class=\"nobullet\">\n";
                    echo "$Indent\t<li>There are no artists to display.</li>\n";
                    echo "$Indent</ul>\n";
                }
                ?>
            </div>
        </div>
    </div>

    <?php
    $Groups = [];
    foreach ($GroupIDs as $GroupID) {
        $Groups[] = $TorrentList[$GroupID];
    }
    $tableRender = newGroupTorrentView(TorrentViewScene::Bookmark, $Groups);
    $tableRender->render();
    ?>
</div>

<?php
View::show_footer();
