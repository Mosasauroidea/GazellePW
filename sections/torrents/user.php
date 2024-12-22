<?php
include(CONFIG['SERVER_ROOT'] . '/classes/torrenttable.class.php');
$Orders = array('Time' => t('server.torrents.add_time'), 'Name' => t('server.torrents.name'), 'Seeders' => t('server.torrents.seeders'), 'Leechers' => t('server.torrents.leechers'), 'Snatched' => t('server.torrents.snatched'), 'Size' => t('server.torrents.size'));
$Ways = array('ASC' => t('server.torrents.asc'), 'DESC' => t('server.torrents.desc'));
$UserVotes = Votes::get_user_votes($LoggedUser['ID']);

// The "order by x" links on columns headers
function header_link($SortKey, $DefaultWay = 'DESC') {
    global $Order, $Way;
    if ($SortKey == $Order) {
        if ($Way == 'DESC') {
            $NewWay = 'ASC';
        } else {
            $NewWay = 'DESC';
        }
    } else {
        $NewWay = $DefaultWay;
    }

    return "torrents.php?way=$NewWay&amp;order=$SortKey&amp;" . Format::get_url(array('way', 'order'));
}

if (!isset($_GET['userid'])) {
    header("Location: torrents.php?type={$_GET['type']}&userid={$LoggedUser['ID']}");
}

$UserID = $_GET['userid'];
if (!is_number($UserID)) {
    error(0);
}

if (!empty($_GET['page']) && is_number($_GET['page']) && $_GET['page'] > 0) {
    $Page = $_GET['page'];
    $Limit = ($Page - 1) * CONFIG['TORRENTS_PER_PAGE'] . ', ' . CONFIG['TORRENTS_PER_PAGE'];
} else {
    $Page = 1;
    $Limit = CONFIG['TORRENTS_PER_PAGE'];
}

if (!empty($_GET['order']) && array_key_exists($_GET['order'], $Orders)) {
    $Order = $_GET['order'];
} else {
    $Order = 'Time';
}

if (!empty($_GET['way']) && array_key_exists($_GET['way'], $Ways)) {
    $Way = $_GET['way'];
} else {
    $Way = 'DESC';
}

$SearchWhere = array();
if (!empty($_GET['source'])) {
    if (in_array($_GET['source'], $Sources)) {
        $SearchWhere[] = "t.Source = '" . db_string($_GET['source']) . "'";
    }
}

if (!empty($_GET['codec']) && in_array($_GET['codec'], $Codecs)) {
    $SearchWhere[] = "t.Codec = '" . db_string($_GET['codec']) . "'";
}

if (!empty($_GET['container']) && in_array($_GET['container'], $Containers)) {
    $SearchWhere[] = "t.Container = '" . db_string($_GET['container']) . "'";
}

if (!empty($_GET['resolution']) && in_array($_GET['resolution'], $Resolutions)) {
    $SearchWhere[] = "t.Resolution = '" . db_string($_GET['resolution']) . "'";
}

if (!empty($_GET['processing']) && in_array($_GET['processing'], $Processings)) {
    $SearchWhere[] = "t.Processing = '" . db_string($_GET['processing']) . "'";
}

if (!empty($_GET['releasetype']) && in_array($_GET['releasetype'], $ReleaseTypes)) {
    $SearchWhere[] = "tg.ReleaseType = '" . db_string($_GET['releasetype']) . "'";
}

if (!empty($_GET['categories'])) {
    $Cats = array();
    foreach (array_keys($_GET['categories']) as $Cat) {
        if (!is_number($Cat)) {
            error(0);
        }
        $Cats[] = "tg.CategoryID = '" . db_string($Cat) . "'";
    }
    $SearchWhere[] = '(' . implode(' OR ', $Cats) . ')';
}

if (!isset($_GET['tags_type'])) {
    $_GET['tags_type'] = '1';
}

if (!empty($_GET['tags'])) {
    $Tags = explode(',', $_GET['tags']);
    $TagList = array();
    foreach ($Tags as $Tag) {
        $Tag = trim(str_replace('.', '_', $Tag));
        if (empty($Tag)) {
            continue;
        }
        if ($Tag[0] == '!') {
            $Tag = ltrim(substr($Tag, 1));
            if (empty($Tag)) {
                continue;
            }
            $TagList[] = "CONCAT(' ', tg.TagList, ' ') NOT LIKE '% " . db_string($Tag) . " %'";
        } else {
            $TagList[] = "CONCAT(' ', tg.TagList, ' ') LIKE '% " . db_string($Tag) . " %'";
        }
    }
    if (!empty($TagList)) {
        if (isset($_GET['tags_type']) && $_GET['tags_type'] !== '1') {
            $_GET['tags_type'] = '0';
            $SearchWhere[] = '(' . implode(' OR ', $TagList) . ')';
        } else {
            $_GET['tags_type'] = '1';
            $SearchWhere[] = '(' . implode(' AND ', $TagList) . ')';
        }
    }
}

$SearchWhere = implode(' AND ', $SearchWhere);
if (!empty($SearchWhere)) {
    $SearchWhere = " AND $SearchWhere";
}

$User = Users::user_info($UserID);
$Perms = Permissions::get_permissions($User['PermissionID']);
$UserClass = $Perms['Class'];

switch ($_GET['type']) {
    case 'snatched':
        if (!check_paranoia('snatched', $User['Paranoia'], $UserClass, $UserID)) {
            error(403);
        }
        $Time = 'xs.tstamp';
        $UserField = 'xs.uid';
        $ExtraWhere = '';
        $From = "
			xbt_snatched AS xs
				JOIN torrents AS t ON t.ID = xs.fid";
        break;
    case 'seeding':
        if (!check_paranoia('seeding', $User['Paranoia'], $UserClass, $UserID)) {
            error(403);
        }
        $Time = '(xfu.mtime - xfu.timespent)';
        $UserField = 'xfu.uid';
        $ExtraWhere = '
			AND xfu.active = 1
			AND xfu.Remaining = 0';
        $From = "
			xbt_files_users AS xfu
				JOIN torrents AS t ON t.ID = xfu.fid";
        break;
    case 'leeching':
        if (!check_paranoia('leeching', $User['Paranoia'], $UserClass, $UserID)) {
            error(403);
        }
        $Time = '(xfu.mtime - xfu.timespent)';
        $UserField = 'xfu.uid';
        $ExtraWhere = '
			AND xfu.active = 1
			AND xfu.Remaining > 0';
        $From = "
			xbt_files_users AS xfu
				JOIN torrents AS t ON t.ID = xfu.fid";
        break;
    case 'uploaded':
        if ((empty($_GET['filter'])) && !check_paranoia('uploads', $User['Paranoia'], $UserClass, $UserID) && !check_perms("users_view_uploaded")) {
            error(403);
        }
        $Time = 'unix_timestamp(t.Time)';
        $UserField = 't.UserID';
        $ExtraWhere = '';
        $From = "torrents AS t";
        break;
    case 'downloaded':
        if (!check_perms('site_view_torrent_snatchlist')) {
            error(403);
        }
        $Time = 'unix_timestamp(ud.Time)';
        $UserField = 'ud.UserID';
        $ExtraWhere = '';
        $From = "
			users_downloads AS ud
				JOIN torrents AS t ON t.ID = ud.TorrentID";
        break;
    default:
        error(404);
}

if (!empty($_GET['filter'])) {
    if ($_GET['filter'] === 'uniquegroup') {
        if (!check_paranoia('uniquegroups', $User['Paranoia'], $UserClass, $UserID) && !check_perms("users_view_uploaded")) {
            error(403);
        }
        $GroupBy = 'tg.ID';
    } elseif ($_GET['filter'] === 'original') {
        if (!check_paranoia('original', $User['Paranoia'], $UserClass, $UserID) && !check_perms("users_view_uploaded")) {
            error(403);
        }
        $ExtraWhere .= " AND (t.Buy='1' or t.Diy='1')";
    } elseif ($_GET['filter'] === 'original_buy') {
        if (!check_paranoia('original', $User['Paranoia'], $UserClass, $UserID) && !check_perms("users_view_uploaded")) {
            error(403);
        }
        $ExtraWhere .= " AND t.Buy='1'";
    } elseif ($_GET['filter'] === 'original_diy') {
        if (!check_paranoia('original', $User['Paranoia'], $UserClass, $UserID) && !check_perms("users_view_uploaded")) {
            error(403);
        }
        $ExtraWhere .= " AND t.Diy='1'";
    }
}

if (empty($GroupBy)) {
    $GroupBy = 't.ID';
}

if ((empty($_GET['search']) || trim($_GET['search']) === '') && $Order != 'Name') {
    $SQL = "
		SELECT
			SQL_CALC_FOUND_ROWS
			t.GroupID,
			t.ID AS TorrentID,
			$Time AS Time,
			tg.CategoryID
		FROM $From
			JOIN torrents_group AS tg ON tg.ID = t.GroupID
		WHERE $UserField = '$UserID'
			$ExtraWhere
			$SearchWhere
		GROUP BY $GroupBy
		ORDER BY $Order $Way
		LIMIT $Limit";
} else {
    $DB->query("
		CREATE TEMPORARY TABLE temp_sections_torrents_user (
			GroupID int(10) unsigned not null,
			TorrentID int(10) unsigned not null,
			Time int(12) unsigned not null,
			CategoryID int(3) unsigned,
			Seeders int(6) unsigned,
			Leechers int(6) unsigned,
			Snatched int(10) unsigned,
			Name mediumtext,
			Size bigint(12) unsigned,
		PRIMARY KEY (TorrentID)) CHARSET=utf8mb4");
    $DB->query("
		INSERT IGNORE INTO temp_sections_torrents_user
			SELECT
				t.GroupID,
				t.ID AS TorrentID,
				$Time AS Time,
				tg.CategoryID,
				t.Seeders,
				t.Leechers,
				t.Snatched,
				CONCAT_WS(' ', GROUP_CONCAT(aa.Name SEPARATOR ' '), ' ', tg.Name, ' ', tg.Year, ' ', tg.SubName, ' ', tg.IMDBID, ' ') AS Name,
				t.Size
			FROM $From
				JOIN torrents_group AS tg ON tg.ID = t.GroupID
				LEFT JOIN torrents_artists AS ta ON ta.GroupID = tg.ID
				LEFT JOIN artists_alias AS aa ON aa.ArtistID = ta.ArtistID
			WHERE $UserField = '$UserID'
				$ExtraWhere
				$SearchWhere
			GROUP BY TorrentID, Time");

    if (!empty($_GET['search']) && trim($_GET['search']) !== '') {
        $Words = array_unique(explode(' ', db_string($_GET['search'])));
    }

    $SQL = "
		SELECT
			SQL_CALC_FOUND_ROWS
			GroupID,
			TorrentID,
			Time,
			CategoryID
		FROM temp_sections_torrents_user";
    if (!empty($Words)) {
        $SQL .= "
		WHERE Name LIKE '%" . implode("%' AND Name LIKE '%", $Words) . "%'";
    }
    $SQL .= "
		ORDER BY $Order $Way
		LIMIT $Limit";
}

$DB->query($SQL);
$GroupIDs = $DB->collect('GroupID');
$TorrentsInfo = $DB->to_array('TorrentID', MYSQLI_ASSOC);

$DB->query('SELECT FOUND_ROWS()');
list($TorrentCount) = $DB->next_record();

$Results = Torrents::get_groups($GroupIDs);

$Action = display_str($_GET['type']);
$User = Users::user_info($UserID);

View::show_header($User['Username'] . t('server.torrents.user_s') . t('server.torrents.action_' . $Action) . t('server.torrents.action_torrents'), 'voting', 'PageTorrentUser');

$Pages = Format::get_pages($Page, $TorrentCount, CONFIG['TORRENTS_PER_PAGE']);


?>
<div class="LayoutBody">
    <div class="BodyHeader">
        <div class="BodyHeader-nav TorrentViewWrapper"><a href="user.php?id=<?= $UserID ?>"><?= $User['Username'] ?></a><?= t('server.torrents.user_s') . t('server.torrents.action_' . $Action) . t('server.torrents.action_torrents') ?>
            <?
            renderTorrentViewButton(TorrentViewScene::User);
            ?>
        </div>
    </div>
    <div class="BodyContent">
        <form class="Form SearchPage Box SearchUserTorrent" name="torrents" action="" method="get">
            <div class="SearchPageBody">
                <table class="Form-rowList">
                    <tr class="Form-row">
                        <td class="Form-label"><strong><?= t('server.torrents.search_for') ?>:</strong></td>
                        <td class="Form-inputs">
                            <input type="hidden" name="type" value="<?= $_GET['type'] ?>" />
                            <input type="hidden" name="userid" value="<?= $UserID ?>" />
                            <input class="Input" type="text" name="search" size="60" value="<? Format::form('search') ?>" />
                        </td>
                    </tr>
                    <tr class="Form-row">
                        <td class="Form-label"><strong><?= t('server.torrents.ft_ripspecifics') ?>:</strong></td>
                        <td class="Form-inputs" colspan="3">
                            <select class="Input" id="source" name="source" class="ft_source fti_advanced">
                                <option class="Select-option" value=""><?= t('server.torrents.source') ?></option>
                                <? foreach ($Sources as $SourceName) { ?>
                                    <option class="Select-option" value="<?= display_str($SourceName); ?>" <? Format::selected('source', $SourceName) ?>><?= display_str($SourceName); ?></option>
                                <?  } ?>
                            </select>

                            <select class="Input" name="codec" class="ft_codec fti_advanced">
                                <option class="Select-option" value=""><?= t('server.torrents.codec') ?></option>
                                <? foreach ($Codecs as $CodecName) { ?>
                                    <option class="Select-option" value="<?= display_str($CodecName); ?>" <? Format::selected('codec', $CodecName) ?>><?= display_str($CodecName); ?></option>
                                <?  } ?>
                            </select>
                            <select class="Input" name="container" class="ft_container fti_advanced">
                                <option class="Select-option" value=""><?= t('server.torrents.container') ?></option>
                                <? foreach ($Containers as $ContainerName) { ?>
                                    <option class="Select-option" value="<?= display_str($ContainerName); ?>" <? Format::selected('container', $ContainerName) ?>><?= display_str($ContainerName); ?></option>
                                <?  } ?>
                            </select>
                            <select class="Input" name="resolution" class="ft_resolution fti_advanced">
                                <option class="Select-option" value=""><?= t('server.torrents.resolution') ?></option>
                                <? foreach ($Resolutions as $ResolutionName) { ?>
                                    <option class="Select-option" value="<?= display_str($ResolutionName); ?>" <? Format::selected('resolution', $ResolutionName) ?>><?= display_str($ResolutionName); ?></option>
                                <?  } ?>
                            </select>
                            <select class="Input" name="processing" class="ft_container fti_advanced">
                                <option class="Select-option" value=""><?= t('server.torrents.processing') ?></option>
                                <? foreach ($Processings as $ProcessingName) { ?>
                                    <option class="Select-option" value="<?= display_str($ProcessingName); ?>" <? Format::selected('processing', $ProcessingName) ?>><?= display_str($ProcessingName); ?></option>
                                <?  } ?>
                            </select>
                            <select class="Input" name="releasetype" class="ft_releasetype fti_advanced">
                                <option class="Select-option" value=""><?= t('server.torrents.ft_releasetype') ?></option>
                                <? foreach ($ReleaseTypes as $ID) { ?>
                                    <option class="Select-option" value="<?= display_str($ID); ?>" <? Format::selected('releasetype', $ID) ?>><?= display_str(t('server.torrents.release_types')[$ID]); ?></option>
                                <?  } ?>
                            </select>
                        </td>
                    </tr>
                    <tr class="Form-row">
                        <td class="Form-label"><strong><?= t('server.torrents.misc') ?>:</strong></td>
                        <td class="Form-inputs" colspan="3">
                            <select class="Input" name="scene" class="ft_scene">
                                <option class="Select-option" value=""><?= t('server.torrents.scene') ?></option>
                                <option class="Select-option" value="1" <? Format::selected('scene', 1) ?>><?= t('server.torrents.yes') ?></option>
                                <option class="Select-option" value="0" <? Format::selected('scene', 0) ?>><?= t('server.torrents.no') ?></option>
                            </select>
                        </td>
                    </tr>


                    <tr class="Form-row">
                        <td class="Form-label"><strong><?= t('server.torrents.ft_order') ?>:</strong></td>
                        <td class="Form-inputs">
                            <select class="Input" name="order" class="ft_order_by">
                                <? foreach ($Orders as $OrderKey => $OrderText) { ?>
                                    <option class="Select-option" value="<?= $OrderKey ?>" <? Format::selected('order', $OrderKey) ?>><?= $OrderText ?></option>
                                <?    } ?>
                            </select>
                            <select class="Input" name="way" class="ft_order_way">
                                <? foreach ($Ways as $WayKey => $WayText) { ?>
                                    <option class="Select-option" value="<?= $WayKey ?>" <? Format::selected('way', $WayKey) ?>><?= $WayText ?></option>
                                <?    } ?>
                            </select>
                        </td>
                    </tr>
                </table>
            </div>
            <div class="SearchPageFooter">
                <div class="SearchPageFooter-actions">
                    <button class="Button" type="submit" value="Search torrents"><?= t('server.common.search') ?></button>
                </div>
            </div>
        </form>
    </div>
    <? if (count($GroupIDs) === 0) { ?>
        <div class="center"><?= t('server.torrents.nothing_found') ?></div>
    <?    } else { ?>
        <div class="BodyNavLinks"><?= $Pages ?></div>
    <?
        $TorrentLists = [];
        foreach ($TorrentsInfo as $TorrentID => $Info) {
            list($GroupID,, $Time) = array_values($Info);
            $TorrentLists[] = Torrents::convert_torrent($Results[$GroupID], $TorrentID);
        }
        $tableRender = newUngroupTorrentView(TorrentViewScene::User, $TorrentLists);
        $tableRender->render();
    } ?>
    <div class="BodyNavLinks"><?= $Pages ?></div>
</div>
<? View::show_footer(); ?>