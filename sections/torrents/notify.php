<?php
if (!check_perms('site_torrents_notify')) {
    error(403);
}
include(CONFIG['SERVER_ROOT'] . '/classes/torrenttable.class.php');

define('NOTIFICATIONS_PER_PAGE', 50);
define('NOTIFICATIONS_MAX_SLOWSORT', 10000);

$OrderBys = array(
    'time'     => array('unt' => 'unt.TorrentID'),
    'size'     => array('t'   => 't.Size'),
    'snatches' => array('t'   => 't.Snatched'),
    'seeders'  => array('t'   => 't.Seeders'),
    'leechers' => array('t'   => 't.Leechers'),
    'year'     => array('tg'  => 'tnt.Year')
);

if (empty($_GET['order_by']) || !isset($OrderBys[$_GET['order_by']])) {
    $_GET['order_by'] = 'time';
}
list($OrderTbl, $OrderCol) = each($OrderBys[$_GET['order_by']]);

if (!empty($_GET['order_way']) && $_GET['order_way'] == 'asc') {
    $OrderWay = 'ASC';
} else {
    $OrderWay = 'DESC';
}

if (!empty($_GET['filterid']) && is_number($_GET['filterid'])) {
    $FilterID = $_GET['filterid'];
} else {
    $FilterID = false;
}

list($Page, $Limit) = Format::page_limit(NOTIFICATIONS_PER_PAGE);

// The "order by x" links on columns headers
function header_link($SortKey, $DefaultWay = 'desc') {
    global $OrderWay;
    if ($SortKey == $_GET['order_by']) {
        if ($OrderWay == 'DESC') {
            $NewWay = 'asc';
        } else {
            $NewWay = 'desc';
        }
    } else {
        $NewWay = $DefaultWay;
    }
    return "?action=notify&amp;order_way=$NewWay&amp;order_by=$SortKey&amp;" . Format::get_url(array('page', 'order_way', 'order_by'));
}
//Perhaps this should be a feature at some point
if (check_perms('users_mod') && !empty($_GET['userid']) && is_number($_GET['userid']) && $_GET['userid'] != $LoggedUser['ID']) {
    $UserID = $_GET['userid'];
    $Sneaky = true;
} else {
    $Sneaky = false;
    $UserID = $LoggedUser['ID'];
}

// Sorting by release year requires joining torrents_group, which is slow. Using a temporary table
// makes it speedy enough as long as there aren't too many records to create
if ($OrderTbl == 'tg') {
    $DB->query("
		SELECT COUNT(*)
		FROM users_notify_torrents AS unt
			JOIN torrents AS t ON t.ID=unt.TorrentID
		WHERE unt.UserID=$UserID" .
        ($FilterID
            ? " AND FilterID=$FilterID"
            : ''));
    list($TorrentCount) = $DB->next_record();
    if ($TorrentCount > NOTIFICATIONS_MAX_SLOWSORT) {
        error('Due to performance issues, torrent lists with more than ' . number_format(NOTIFICATIONS_MAX_SLOWSORT) . ' items cannot be ordered by release year.');
    }

    $DB->query("
		CREATE TEMPORARY TABLE temp_notify_torrents
			(TorrentID int, GroupID int, UnRead tinyint, FilterID int, Year smallint, PRIMARY KEY(GroupID, TorrentID), KEY(Year))
		ENGINE=MyISAM");
    $DB->query("
		INSERT IGNORE INTO temp_notify_torrents (TorrentID, GroupID, UnRead, FilterID)
		SELECT t.ID, t.GroupID, unt.UnRead, unt.FilterID
		FROM users_notify_torrents AS unt
			JOIN torrents AS t ON t.ID=unt.TorrentID
		WHERE unt.UserID=$UserID" .
        ($FilterID
            ? " AND unt.FilterID=$FilterID"
            : ''));
    $DB->query("
		UPDATE temp_notify_torrents AS tnt
			JOIN torrents_group AS tg ON tnt.GroupID = tg.ID
		SET tnt.Year = tg.Year");

    $DB->query("
		SELECT TorrentID, GroupID, UnRead, FilterID
		FROM temp_notify_torrents AS tnt
		ORDER BY $OrderCol $OrderWay, GroupID $OrderWay
		LIMIT $Limit");
    $Results = $DB->to_array(false, MYSQLI_ASSOC, false);
} else {
    $DB->query("
		SELECT
			SQL_CALC_FOUND_ROWS
			unt.TorrentID,
			unt.UnRead,
			unt.FilterID,
			t.GroupID
		FROM users_notify_torrents AS unt
			JOIN torrents AS t ON t.ID = unt.TorrentID
		WHERE unt.UserID = $UserID" .
        ($FilterID
            ? " AND unt.FilterID = $FilterID"
            : '') . "
		ORDER BY $OrderCol $OrderWay
		LIMIT $Limit");
    $Results = $DB->to_array(false, MYSQLI_ASSOC, false);
    $DB->query('SELECT FOUND_ROWS()');
    list($TorrentCount) = $DB->next_record();
}

$GroupIDs = $FilterIDs = $UnReadIDs = array();
foreach ($Results as $Torrent) {
    $GroupIDs[$Torrent['GroupID']] = 1;
    $FilterIDs[$Torrent['FilterID']] = 1;
    if ($Torrent['UnRead']) {
        $UnReadIDs[] = $Torrent['TorrentID'];
    }
}
$Pages = Format::get_pages($Page, $TorrentCount, NOTIFICATIONS_PER_PAGE, 9);

if (!empty($GroupIDs)) {
    $GroupIDs = array_keys($GroupIDs);
    $FilterIDs = array_keys($FilterIDs);
    $TorrentGroups = Torrents::get_groups($GroupIDs);

    // Get the relevant filter labels
    $DB->query('
		SELECT ID, Label, Artists
		FROM users_notify_filters
		WHERE ID IN (' . implode(',', $FilterIDs) . ')');
    $Filters = $DB->to_array('ID', MYSQLI_ASSOC, array('Artists'));
    foreach ($Filters as &$Filter) {
        $Filter['Artists'] = explode('|', trim($Filter['Artists'], '|'));
        foreach ($Filter['Artists'] as &$FilterArtist) {
            $FilterArtist = mb_strtolower($FilterArtist, 'UTF-8');
        }
        $Filter['Artists'] = array_flip($Filter['Artists']);
    }
    unset($Filter);

    if (!empty($UnReadIDs)) {
        //Clear before header but after query so as to not have the alert bar on this page load
        $DB->query("
			UPDATE users_notify_torrents
			SET UnRead = '0'
			WHERE UserID = " . $LoggedUser['ID'] . '
				AND TorrentID IN (' . implode(',', $UnReadIDs) . ')');
        $Cache->delete_value('notifications_new_' . $LoggedUser['ID']);
    }
}
if ($Sneaky) {
    $UserInfo = Users::user_info($UserID);
    View::show_header($UserInfo['Username'] . t('server.torrents.s_notifications'), 'notifications', 'PageTorrentNotify');
} else {
    View::show_header(t('server.torrents.my_notifications'), 'notifications', 'PageTorrentNotify');
}
?>
<div class=LayoutBody>
    <div class="BodyHeader">
        <div class="BodyHeader-nav TorrentViewWrapper"><?= t('server.user.notify_me_of_all_new_torrents_with') ?>
            <?
            renderTorrentViewButton(TorrentViewScene::Notify);
            ?>
        </div>
        <div class="BodyHeader-subNav"><?= t('server.torrents.latest_notifications') ?></div>
    </div>
    <div class="BodyNavLinks">
        <? if ($FilterID) { ?>
            <a href="torrents.php?action=notify<?= ($Sneaky ? "&amp;userid=$UserID" : '') ?>" class="brackets"><?= t('server.torrents.view_all') ?></a>
        <? } elseif (!$Sneaky) { ?>
            <a href="torrents.php?action=notify_clear&amp;auth=<?= $LoggedUser['AuthKey'] ?>" class="brackets"><?= t('server.torrents.clear_all_old') ?></a>
            <a class="brackets" href="#" onclick="globalapp.clearSelected(event)"><?= t('server.torrents.clear_selected') ?></a>
            <a href="torrents.php?action=notify_catchup&amp;auth=<?= $LoggedUser['AuthKey'] ?>" class="brackets"><?= t('server.torrents.catch_up') ?></a>
        <? } ?>
        <a href="user.php?action=notify" class="brackets"><?= t('server.user.new_torrent_notify_list') ?></a>
    </div>
    <? if ($TorrentCount > NOTIFICATIONS_PER_PAGE) { ?>
        <div class="BodyNavLinks">
            <?= $Pages ?>
        </div>
    <?
    }
    if (empty($Results)) {
    ?>
        <table class="layout border">
            <tr class="rowb">
                <td colspan="8" class="center">
                    <?= t('server.torrents.no_new_notifications_found') ?><a href="user.php?action=notify" class="brackets"> </a>
                </td>
            </tr>
        </table>
    <?
    } else {
        $FilterGroups = array();
        foreach ($Results as $Result) {
            if (!isset($FilterGroups[$Result['FilterID']])) {
                $FilterGroups[$Result['FilterID']] = array();
                $FilterGroups[$Result['FilterID']]['FilterLabel'] = isset($Filters[$Result['FilterID']])
                    ? $Filters[$Result['FilterID']]['Label']
                    : false;
            }
            $FilterGroups[$Result['FilterID']][] = $Result;
        }
    ?>
        <div class="BoxList">
            <?
            foreach ($FilterGroups as $FilterID => $FilterResults) {
            ?>
                <div class="Group">
                    <div class="Group-header">
                        <div class="Group-headerTitle">
                            <? if ($FilterResults['FilterLabel'] !== false) {
                                $NewFilterID = $FilterID . ($Sneaky ? "&amp;userid=$UserID" : '');
                                $LabelName = $FilterResults['FilterLabel'];
                            ?>
                                <?= t('server.torrents.matches_for', ['Values' => [
                                    "<a href='torrents.php?action=notify&amp;filterid=$NewFilterID'>$LabelName</a>"
                                ]]) ?>
                            <? } else {
                            ?>
                                <?= t('server.torrents.matches_for_unknown_filter', ['Values' => [
                                    "[${FilterID}]"
                                ]]) ?>
                            <? } ?>
                        </div>
                    </div>
                    <div class="Group-body">
                        <div class="BodyNavLinks notify_filter_links">
                            <? if (!$Sneaky) { ?>
                                <a class="brackets" href="#" onclick="globalapp.rssClearSelected(event, <?= $FilterID ?>)"><?= t('server.torrents.clear_selected_in_filter') ?></a>
                                <a href="torrents.php?action=notify_clear_filter&amp;filterid=<?= $FilterID ?>&amp;auth=<?= $LoggedUser['AuthKey'] ?>" class="brackets"><?= t('server.torrents.clear_all_old_in_filter') ?></a>
                                <a href="torrents.php?action=notify_catchup_filter&amp;filterid=<?= $FilterID ?>&amp;auth=<?= $LoggedUser['AuthKey'] ?>" class="brackets"><?= t('server.torrents.mark_all_in_filter_as_read') ?></a>
                            <? } ?>
                        </div>
                        <form class="torrent-table" name="torrents" id="notificationform_<?= $FilterID ?>" action="">
                            <?
                            unset($FilterResults['FilterLabel']);
                            $TorrentLists = [];
                            foreach ($FilterResults as $Result) {
                                $TorrentID = $Result['TorrentID'];
                                $TorrentLists[] = Torrents::convert_torrent($TorrentGroups[$Result['GroupID']], $TorrentID);
                            }
                            $tableRender = newUngroupTorrentView(TorrentViewScene::Notify, $TorrentLists);
                            $tableRender->with_filter_id($FilterID)->render();

                            ?>
                        </form>
                    </div>
                </div>
            <?
            }
            ?>
        </div>
    <?
    }

    if ($Pages) { ?>
        <div class="BodyNavLinks"><?= $Pages ?></div>
    <?  } ?>
</div>
<?
View::show_footer();
