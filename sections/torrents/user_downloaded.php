<?php

// todo by qwerty temp code
if (!CONFIG['ENABLE_HNR']) {
    die();
}

use Gazelle\Util\Time;

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
$View = $_GET['view'];
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

if (!check_perms('site_view_torrent_snatchlist')) {
    error(403);
}
$Time = 'unix_timestamp(ud.Time)';


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

$Time = '(xfu.mtime - xfu.timespent)';
$UserField = 'xfu.uid';
$ExtraWhere = '
    AND xfu.active = 1
    AND xfu.Remaining = 0';
$From = "
    xbt_files_users AS xfu
        JOIN torrents AS t ON t.ID = xfu.fid";

$HNR_INTERVAL = HNR_INTERVAL;
$HNR_MIN_MIN_RATIO = HNR_MIN_MIN_RATIO;
$HNR_MIN_SIZE_PERCENT = HNR_MIN_SIZE_PERCENT;
$HNR_MIN_SEEEDING_TIME = HNR_MIN_SEEEDING_TIME;
if ($View == 1) {
    $SearchWhere .= " AND ut.real_downloaded > t.Size * $HNR_MIN_SIZE_PERCENT
    AND (ut.seedtime < $HNR_MIN_SEEEDING_TIME or ut.real_uploaded <= 0 or ut.real_uploaded / ut.real_downloaded < $HNR_MIN_MIN_RATIO)
    AND unix_timestamp(now()) - unix_timestamp(ud.Time) > $HNR_INTERVAL
    AND th.torrent_id is null";
}
if ((empty($_GET['search']) || trim($_GET['search']) === '') && $Order != 'Name') {
    $SQL = "
		SELECT
			SQL_CALC_FOUND_ROWS
			t.GroupID,
			t.ID AS TorrentID,
			UNIX_TIMESTAMP(ud.Time) AS Time,
			tg.CategoryID,
            ut.seedtime,
            t.Seeders,
			t.Leechers,
            ut.real_downloaded,
            ut.real_uploaded,
            ut.snatched,
            xfu.active,
            xfu.remaining,
            th.torrent_id as eliminate
		FROM users_downloads AS ud 
            LEFT JOIN users_torrents AS ut on ut.uid = ud.UserID and ut.fid = ud.TorrentID
            LEFT JOIN xbt_files_users AS xfu on xfu.uid = ud.UserID and xfu.fid = ud.TorrentID
            JOIN torrents AS t ON t.ID = ud.TorrentID
			JOIN torrents_group AS tg ON tg.ID = t.GroupID
            LEFT JOIN torrents_hnr as th ON th.user_id= ud.UserID and th.torrent_id = ud.TorrentID
		WHERE ud.UserID = '$UserID'
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
			Name mediumtext,
			Size bigint(12) unsigned,
            seedtime bigint(20) unsigned,
            real_downloaded bigint(20) unsigned,
            real_uploaded bigint(20) unsigned,
            snatched bigint(20) unsigned,
            active tinyint(1),
            remaining bigint(20) unsigned,
		PRIMARY KEY (TorrentID)) CHARSET=utf8mb4");
    $DB->query("
		INSERT IGNORE INTO temp_sections_torrents_user
			SELECT
				t.GroupID,
				t.ID AS TorrentID,
				ud.Time AS Time,
				tg.CategoryID,
				t.Seeders,
				t.Leechers,
				CONCAT_WS(' ', GROUP_CONCAT(aa.Name SEPARATOR ' '), ' ', tg.Name, ' ', tg.Year, ' ', tg.SubName, ' ', tg.IMDBID, ' ') AS Name,
				t.Size,
                ut.seedtime,
                ut.real_downloaded,
                ut.real_uploaded,
                ut.snatched,
                xfu.active,
                xfu.remaining,
                th.torrent_id as eliminate
			FROM users_downloads AS ud 
                JOIN torrents AS t ON t.ID = ud.TorrentID
                LEFT JOIN users_torrents AS ut on ut.uid = ud.UserID and ut.fid = ud.TorrentID
                LEFT JOIN xbt_files_users AS xfu on xfu.uid = ud.UserID and xfu.fid = ud.TorrentID
				JOIN torrents_group AS tg ON tg.ID = t.GroupID
				LEFT JOIN torrents_artists AS ta ON ta.GroupID = tg.ID
				LEFT JOIN artists_alias AS aa ON aa.ArtistID = ta.ArtistID
                LEFT JOIN torrents_hnr as th ON th.user_id= ud.UserID and th.torrent_id = ud.TorrentID
			WHERE ud.UserID = '$UserID'
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
			UNIX_TIMESTAMP(Time),
			CategoryID,
            Seeders,
			Leechers,
            seedtime,
            real_downloaded,
            real_uploaded,
            snatched,
            active,
            remaining,
            eliminate      
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

View::show_header($User['Username'] . t('server.torrents.user_s') . t('server.torrents.action_' . $Action) . t('server.torrents.action_torrents'), 'voting', 'PageTorrentuserDownloaded');

$Pages = Format::get_pages($Page, $TorrentCount, CONFIG['TORRENTS_PER_PAGE']);


?>
<div class="LayoutBody">
    <div class="BodyHeader">
        <h2 class="BodyHeader-nav"><a href="user.php?id=<?= $UserID ?>"><?= $User['Username'] ?></a><?= t('server.torrents.user_s') . t('server.torrents.action_' . $Action) . t('server.torrents.action_torrents') ?></h2>
    </div>
    <div>
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
                            <div class="Checkbox">
                                <input class="Input" type="checkbox" name="view" <?= $View == 1 ? "checked" : "" ?> id="only_hnr" value="1">
                                <label class="Checkbox-label"><?= t('server.torrents.only_hnr') ?></label>
                            </div>
                            <select class="Input" name="scene" class="ft_scene">
                                <option class="Select-option" value=""><?= t('server.torrents.scene') ?></option>
                                <option class="Select-option" value="1" <? Format::selected('scene', 1) ?>><?= t('server.torrents.yes') ?></option>
                                <option class="Select-option" value="0" <? Format::selected('scene', 0) ?>><?= t('server.torrents.no') ?></option>
                            </select>
                        </td>
                    </tr>
                    <tr class="Form-row">
                        <td class="Form-label"><strong><?= t('server.torrents.tags') ?>:</strong></td>
                        <td class="Form-inputs">
                            <input class="Input" type="text" name="tags" size="60" data-tooltip="Use !tag to exclude tag" value="<? Format::form('tags') ?>" />
                            <div class="Radio">
                                <input class="Input" type="radio" name="tags_type" id="tags_type0" value="0" <? Format::selected('tags_type', 0, 'checked') ?> />
                                <label class="Radio-label" for="tags_type0"><?= t('server.torrents.any') ?></label>
                            </div>
                            <div class="Radio">
                                <input class="Input" type="radio" name="tags_type" id="tags_type1" value="1" <? Format::selected('tags_type', 1, 'checked') ?> />
                                <label class="Radio-label" for="tags_type1"><?= t('server.torrents.all') ?></label>
                            </div>
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

                <table class="layout cat_list">
                    <?
                    $x = 0;
                    reset($Categories);
                    foreach ($Categories as $CatKey => $CatName) {
                        if ($x % 7 === 0) {
                            if ($x > 0) {
                    ?>
                                </tr>
                            <?        } ?>
                            <tr class="Form-row">
                            <?
                        }
                        $x++;
                            ?>
                            <td class="Form-inputs">
                                <div class="Checkbox">
                                    <input class="Input" type="checkbox" name="categories[<?= ($CatKey + 1) ?>]" id="cat_<?= ($CatKey + 1) ?>" value="1" <? if (isset($_GET['categories'][$CatKey + 1])) { ?> checked="checked" <? } ?> />
                                    <label class="Checkbox-label" for="cat_<?= ($CatKey + 1) ?>"><?= $CatName ?></label>
                                </div>
                            </td>
                        <?
                    }
                        ?>
                            </tr>
                </table>
            </div>
            <div class="SearchPageFooter">
                <div class="SearchPageFooter-actions">
                    <input class="Button" type="submit" value="Search torrents" />
                </div>
            </div>
        </form>
    </div>
    <? if (count($GroupIDs) === 0) { ?>
        <div class="center"><?= t('server.torrents.nothing_found') ?></div>
    <?    } else { ?>
        <div class="BodyNavLinks"><?= $Pages ?></div>
        <table class="TableTorrent Table">
            <tr class="Table-rowHeader">
                <td class="Table-cell"><a href="<?= header_link('Name', 'ASC') ?>"><?= t('server.torrents.torrent') ?></a></td>
                <td class="Table-cell">
                    <i data-tooltip="<?= t('server.torrents.size') ?>">
                        <?= icon("torrent-size") ?>
                    </i>
                </td>
                <td class="Table-cell TableTorrent-cellStat">
                    <i data-tooltip="<?= t('server.common.uploaded') ?>">
                        <?= icon("uploaded") ?>
                    </i>
                </td>
                <td class="Table-cell TableTorrent-cellStat">
                    <i data-tooltip="<?= t('server.common.downloaded') ?>">
                        <?= icon("downloaded") ?>
                    </i>
                </td>
                <td class="Table-cell TableTorrent-cellStat">
                    <i data-tooltip="<?= t('server.common.ratio') ?>">
                        <?= icon("ratio") ?>
                    </i>
                </td>
                <td class="Table-cell TableTorrent-cellStat">
                    <a href="<?= header_link('Seeders') ?>">
                        <i aria-hidden="true" alt="Seeders" data-tooltip="<?= t('server.torrents.seeders') ?>">
                            <?= icon("torrent-seeders") ?>
                        </i>
                    </a>
                </td>
                <td class="Table-cell TableTorrent-cellStat">
                    <a href="<?= header_link('Leechers') ?>">
                        <i aria-hidden="true" alt="Leechers" data-tooltip="<?= t('server.torrents.leechers') ?>">
                            <?= icon("torrent-leechers") ?>
                        </i>
                    </a>
                </td>
                <td class="center">
                    <i data-tooltip="<?= t('server.torrents.seeding_status') ?>">
                        <?= icon("seed-status") ?>
                    </i>
                </td>
                <td class="center">
                    <i data-tooltip="<?= t('server.torrents.seeding_time') ?>">
                        <?= icon("torrent-time") ?>
                    </i>
                </td>
                <? if (CONFIG['ENABLE_HNR']) { ?>
                    <td class="center">
                        <i data-tooltip="<?= t('server.torrents.hit_and_run') ?>">
                            <?= icon("User/hnr") ?>
                        </i>
                    </td>
                <? } ?>
            </tr>
            <?
            $PageSize = 0;
            foreach ($TorrentsInfo as $TorrentID => $Info) {
                list($GroupID,, $Time, $CategoryID, $Seeders, $Leechers, $SeedTime, $RealDownloaded, $RealUploaded, $Snatched, $Active, $Remaining, $Eliminate) = array_values($Info);

                $Seeding = $Remaining == 0 && $Active;

                extract(Torrents::array_group($Results[$GroupID]));
                $Torrent = $Torrents[$TorrentID];
                $Torrent['Group'] = $Results[$GroupID];
                $Size = $Torrent['Size'];
                $TorrentName = Torrents::torrent_simple_view($Torrent['Group'], $Torrent);
                $RealUploaded = $RealUploaded ? $RealUploaded : 0;
                $RealDownloaded = $RealDownloaded ? $RealDownloaded : 0;
                $HNR = false;
                if ($RealDownloaded > $Size * HNR_MIN_SIZE_PERCENT  &&  time() - intval($Time) > HNR_INTERVAL && ($SeedTime < HNR_MIN_SEEEDING_TIME || $RealUploaded == 0 || $RealDownloaded / $RealUploaded < HNR_MIN_MIN_RATIO)) {
                    $HNR = true;
                }
                // 被消除
                if ($Eliminate && $HNR = true) {
                    $HNR = false;
                }

            ?>
                <tr class="Table-row">
                    <td class="Table-cell">
                        <?= $TorrentName ?>
                    </td>
                    <td class="Table-cell">
                        <?= Format::get_size($Torrent['Size']) ?>
                    </td>
                    <td class="Table-cell TableTorrent-cellStat">
                        <?= $RealUploaded ? Format::get_size($RealUploaded) : '--' ?>
                    </td>
                    <td class="Table-cell TableTorrent-cellStat">
                        <?= $RealDownloaded ? Format::get_size($RealDownloaded) : '--' ?>
                    </td>
                    <td class="Table-cell TableTorrent-cellStat">
                        <?= (!$RealDownloaded && !$RealUploaded) ? '--' : round($RealUploaded / $RealDownloaded, 2) ?>
                    </td>
                    <td class="Table-cell TableTorrent-cellStat"> <?= (($Torrent['Seeders'] == 0) ? ' u-colorRatio00' : '') ?>"><?= number_format($Torrent['Seeders']) ?></td>
                    <td class="Table-cell TableTorrent-cellStat"> <?= number_format($Torrent['Leechers']) ?></td>
                    <td class="Table-cell Table-cellCenter">
                        <span class="u-colorSuccess"><?= $Seeding ? t('server.torrents.yes') : t('server.torrents.no') ?></span>
                    </td>
                    <td class="Table-cell Table-cellCenter">
                        <?= Time::convertMinutes($SeedTime / 60) ?>
                    </td>
                    <? if (CONFIG['ENABLE_HNR']) { ?>
                        <td class="Table-cell Table-cellCenter">
                            <span class="u-colorWarning"><?= $HNR ? t('server.torrents.yes') : t('server.torrents.no') ?></span>
                        </td>
                    <? } ?>
                </tr>
            <?        } ?>
        </table>
    <?    } ?>
    <div class="BodyNavLinks"><?= $Pages ?></div>
</div>
<? View::show_footer(); ?>