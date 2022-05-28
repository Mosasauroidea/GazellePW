<?
/*
User collage subscription page
*/

include(SERVER_ROOT . '/classes/torrenttable.class.php');
if (!check_perms('site_collages_subscribe')) {
    error(403);
}

View::show_header(Lang::get('userhistory', 'subscribed_collages'), 'browse', 'PageUserHistorySubscribedCollage');

$ShowAll = !empty($_GET['showall']);

if (!$ShowAll) {
    $sql = "
		SELECT
			c.ID,
			c.Name,
			c.NumTorrents,
			s.LastVisit
		FROM collages AS c
			JOIN users_collage_subs AS s ON s.CollageID = c.ID
			JOIN collages_torrents AS ct ON ct.CollageID = c.ID
		WHERE s.UserID = $LoggedUser[ID] AND c.Deleted = '0'
			AND ct.AddedOn > s.LastVisit
		GROUP BY c.ID";
} else {
    $sql = "
		SELECT
			c.ID,
			c.Name,
			c.NumTorrents,
			s.LastVisit
		FROM collages AS c
			JOIN users_collage_subs AS s ON s.CollageID = c.ID
			LEFT JOIN collages_torrents AS ct ON ct.CollageID = c.ID
		WHERE s.UserID = $LoggedUser[ID] AND c.Deleted = '0'
		GROUP BY c.ID";
}

$DB->query($sql);
$NumResults = $DB->record_count();
$CollageSubs = $DB->to_array();
?>
<div class="LayoutBody">
    <div class="BodyHeader">
        <h2 class="BodyHeader-nav"><?= Lang::get('userhistory', 'subscribed_collages') ?><?= ($ShowAll ? '' : Lang::get('userhistory', 'with_new_additions')) ?></h2>

        <div class="BodyNavLinks">
            <?
            if ($ShowAll) {
            ?>
                <br /><br />
                <a href="userhistory.php?action=subscribed_collages&amp;showall=0" class="brackets"><?= Lang::get('userhistory', 'only_display_collages_with_new_additions') ?></a>
            <?
            } else {
            ?>
                <br /><br />
                <a href="userhistory.php?action=subscribed_collages&amp;showall=1" class="brackets"><?= Lang::get('userhistory', 'show_all_subscribed_collages') ?></a>
            <?
            }
            ?>
            <a href="userhistory.php?action=catchup_collages&amp;auth=<?= $LoggedUser['AuthKey'] ?>" class="brackets"><?= Lang::get('userhistory', 'catch_up') ?></a>
        </div>
    </div>
    <?
    if (!$NumResults) {
    ?>
        <div class="center">
            <?= Lang::get('userhistory', 'no_subscribed_collages') ?><?= ($ShowAll ? '' : Lang::get('userhistory', 'with_new_additions')) ?>
        </div>
        <?
    } else {
        $HideGroup = '';
        $ActionTitle = 'Hide';
        $ActionURL = 'hide';
        $ShowGroups = 0;

        foreach ($CollageSubs as $Collage) {
            unset($TorrentTable);

            list($CollageID, $CollageName, $CollageSize, $LastVisit) = $Collage;
            $RS = $DB->query("
			SELECT GroupID
			FROM collages_torrents
			WHERE CollageID = $CollageID
				AND AddedOn > '" . db_string($LastVisit) . "'
			ORDER BY AddedOn");
            $NewTorrentCount = $DB->record_count();

            $GroupIDs = $DB->collect('GroupID', false);
            if (count($GroupIDs) > 0) {
                $TorrentList = Torrents::get_groups($GroupIDs);
            } else {
                $TorrentList = array();
            }
        ?>
            <table style="margin-top: 8px;" class="subscribed_collages_table">
                <tr class="colhead_dark">
                    <td>
                        <span style="float: left;">
                            <strong><a href="collage.php?id=<?= $CollageID ?>"><?= $CollageName ?></a></strong> (<?= $NewTorrentCount ?> <?= Lang::get('userhistory', 'new_torrent') ?><?= ($NewTorrentCount == 1 ? '' : Lang::get('userhistory', 's')) ?>)
                        </span>&nbsp;
                        <span style="float: right;">
                            <a href="#" onclick="$('#collage_table_<?= $CollageID ?>').gtoggle(); this.innerHTML = (this.innerHTML == '<?= Lang::get('global', 'hide') ?>' ? '<?= Lang::get('global', 'show') ?>' : '<?= Lang::get('global', 'hide') ?>'); return false;" class="brackets"><?= ($ShowAll ? Lang::get('userhistory', 'show') : Lang::get('userhistory', 'hide')) ?></a>&nbsp;&nbsp;&nbsp;<a href="userhistory.php?action=catchup_collages&amp;auth=<?= $LoggedUser['AuthKey'] ?>&amp;collageid=<?= $CollageID ?>" class="brackets"><?= Lang::get('userhistory', 'catch_up') ?></a>&nbsp;&nbsp;&nbsp;<a href="#" onclick="CollageSubscribe(<?= $CollageID ?>); return false;" id="subscribelink<?= $CollageID ?>" class="brackets"><?= Lang::get('global', 'unsubscribe') ?></a>
                        </span>
                    </td>
                </tr>
            </table>
            <!--</div>-->
            <div class="BoxBody" id="collage_table_<?= $CollageID ?>">
                <?
                $Groups = [];
                foreach ($GroupIDs as $GroupID) {
                    $Groups[] = $TorrentList[$GroupID];
                }
                $tableRender = new TorrentGroupCoverTableView($Groups);
                $tableRender->render();
                ?>
            </div>
    <?
        }
    }
    ?>
</div>
<?

View::show_footer();

?>