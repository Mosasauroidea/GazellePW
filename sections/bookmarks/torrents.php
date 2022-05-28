<?php
include(SERVER_ROOT . '/classes/torrenttable.class.php');
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
$Title = $Sneaky ? "$Username" . Lang::get('bookmarks', 's_bookmarked_torrent_groups') : Lang::get('bookmarks', 'your_bookmarked_torrent_groups');

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
    foreach ($Artists as $Importance => $ImportanceArtists) {
        foreach ($ImportanceArtists as $Artist) {
            if (!isset($TopArtists[$Artist['id']])) {
                $TopArtists[$Artist['id']] = array('data' => $Artist, 'count' => 1);
            } else {
                $TopArtists[$Artist['id']]['count']++;
            }
        }
    }
    new Tags($TagList);
}

$GroupIDs = array_values($GroupIDs);

$CollageCovers = isset($LoggedUser['CollageCovers']) ? (int)$LoggedUser['CollageCovers'] : 25;

View::show_header($Title, 'browse,collage', 'PageBookmarkTorrent');
?>
<div class="LayoutBody">
    <div class="BodyHeader">
        <h2 class="BodyHeader-nav">
            <? if (!$Sneaky) { ?><a href="feeds.php?feed=torrents_bookmarks_t_<?= $LoggedUser['torrent_pass'] ?>&amp;user=<?= $LoggedUser['ID'] ?>&amp;auth=<?= $LoggedUser['RSS_Auth'] ?>&amp;passkey=<?= $LoggedUser['torrent_pass'] ?>&amp;authkey=<?= $LoggedUser['AuthKey'] ?>&amp;name=<?= urlencode(SITE_NAME . ': Bookmarked Torrents') ?>"><img src="<?= STATIC_SERVER ?>/common/symbols/rss.png" alt="RSS feed" /></a>&nbsp;
                <? } ?><?= $Title ?></h2>
        <div class="BodyNavLinks">
            <a href="bookmarks.php?type=torrents" class="brackets"><?= Lang::get('global', 'torrents') ?></a>
            <a href="bookmarks.php?type=artists" class="brackets"><?= Lang::get('global', 'artists') ?></a>
            <?
            if (ENABLE_COLLAGES) {
            ?>
                <a href="bookmarks.php?type=collages" class="brackets"><?= Lang::get('bookmarks', 'collages') ?></a>
            <?
            }
            ?>
            <a href="bookmarks.php?type=requests" class="brackets"><?= Lang::get('global', 'requests') ?></a>
            <? if (count($TorrentList) > 0) { ?>
        </div>
        <div class="BodyNavLinks">
            <a href="bookmarks.php?action=remove_snatched&amp;auth=<?= $LoggedUser['AuthKey'] ?>" class="brackets" onclick="return confirm('<?= Lang::get('bookmarks', 'remove_snatched_confirm') ?>');"><?= Lang::get('bookmarks', 'remove_snatched') ?></a>
            <a href="bookmarks.php?action=edit&amp;type=torrents" class="brackets"><?= Lang::get('bookmarks', 'manage_torrents') ?></a>
        <? } ?>
        </div>
    </div>
    <? if (count($TorrentList) === 0) { ?>
        <div class="Box">
            <div class="Box-body" align="center">
                <h2><?= Lang::get('bookmarks', 'no_bookmarked_torrents') ?></h2>
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
                <strong><?= Lang::get('bookmarks', 'stats') ?></strong>
            </div>
            <ul class="SidebarList SidebarItem-body Box-body">
                <li class="SidebarList-item"><?= Lang::get('bookmarks', 'torrent_groups') ?>: <?= $NumGroups ?></li>
                <li class="SidebarList-item"><?= Lang::get('global', 'artists') ?>: <?= count($ArtistCount) ?></li>
            </ul>
        </div>
        <div class="SidebarItemTags SidebarItem Box">
            <div class="SidebarItem-header Box-header"><strong><?= Lang::get('bookmarks', 'top_tags') ?></strong></div>
            <div class="SidebarItem-body Box-body">
                <ul class="SidebarList">
                    <? Tags::format_top(5, 'torrents.php?taglist=', '', 'SidebarList-item') ?>
                </ul>
            </div>
        </div>
        <div class="SidebarItemArtists SidebarItem Box">
            <div class="SidebarItem-header Box-header">
                <strong><?= Lang::get('bookmarks', 'top_artists') ?></strong>
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
                            <a href="artist.php?id=<?= $ID ?>"><?= Artists::display_artist($Artist['data']) ?></a> (<?= number_format($Artist['count']) ?>)
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

    <div class="Box">
        <div class="Box-body">
            <?php
            $Groups = [];
            foreach ($GroupIDs as $GroupID) {
                $Groups[] = $TorrentList[$GroupID];
            }
            $tableRender = new TorrentGroupCoverTableView($Groups);
            $tableRender->render();
            ?>
        </div>
    </div>
</div>

<?php
View::show_footer();
