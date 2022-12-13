<?php

$Page = !empty($_GET['page']) ? intval($_GET['page']) : 1;
$Page = max(1, $Page);
$Limit = CONFIG['TORRENTS_PER_PAGE'];
$Offset = CONFIG['TORRENTS_PER_PAGE'] * ($Page - 1);

if (!empty($_GET['order_way']) && $_GET['order_way'] == 'desc') {
    $OrderWay = 'desc';
} else {
    $OrderWay = 'asc';
}

$OrderBys = array("size", "seeders", "seedtime", "hourlypoints");
$OrderBy = '';
if (!empty($_GET['order_by']) && in_array($_GET['order_by'], $OrderBys)) {
    $OrderBy = " order by " . $_GET['order_by'] . " $OrderWay ";
}

if (!empty($_GET['userid'])) {
    if (check_perms('users_mod')) {
        $UserID = intval($_GET['userid']);
        $User = array_merge(Users::user_stats($_GET['userid']), Users::user_info($_GET['userid']), Users::user_heavy_info($_GET['userid']));
        if (empty($User)) {
            error(404);
        }
    } else {
        error(404);
    }
} else {
    $UserID = $LoggedUser['ID'];
    $User = $LoggedUser;
}

$Title = ($UserID === $LoggedUser['ID']) ? t('server.bonus.your_bonus_point_rate') : "{$User['Username']}" . t('server.bonus.s_bonus_point_rate');
View::show_header($Title, '', 'PageBonusBPRates');

$DB->prepared_query("
SELECT
	COUNT(xfu.uid) as TotalTorrents,
	SUM(t.Size) as TotalSize,
	SUM(IFNULL(t.Size / (1024 * 1024 * 1024) * 1 * (
		0.025 + (
			(0.06 * LN(1 + (xfh.seedtime / (24)))) / (POW(GREATEST(t.Seeders, 1), 0.6))
		)
	), 0)) AS TotalHourlyPoints
FROM
	(SELECT DISTINCT uid,fid FROM xbt_files_users WHERE active=1 AND remaining=0 AND mtime > unix_timestamp(NOW() - INTERVAL 1 HOUR) AND uid = ?) AS xfu
	JOIN xbt_files_history AS xfh ON xfh.uid = xfu.uid AND xfh.fid = xfu.fid
	JOIN torrents AS t ON t.ID = xfu.fid
WHERE
	xfu.uid = ?", $UserID, $UserID);


list($TotalTorrents, $TotalSize, $TotalHourlyPoints) = $DB->next_record();
$TotalTorrents = intval($TotalTorrents);
$TotalSize = floatval($TotalSize);
$TotalHourlyPoints = floatval($TotalHourlyPoints);
$TotalDailyPoints = $TotalHourlyPoints * 24;
$TotalWeeklyPoints = $TotalDailyPoints * 7;
// The mean number of days in a month in the Gregorian calendar,
// and then multiple that by 12
$TotalMonthlyPoints = $TotalDailyPoints * 30.436875;
$TotalYearlyPoints = $TotalDailyPoints * 365.2425;

$Pages = Format::get_pages($Page, $TotalTorrents, CONFIG['TORRENTS_PER_PAGE']);
if ($TotalTorrents > 0) {
    $DB->prepared_query("
	SELECT
		t.ID,
		t.GroupID,
		t.Size,
		t.Size / (1024 * 1024 * 1024) as CorrectSize,
		t.Codec,
		t.Source,
		t.Processing,
        t.Container,
        t.Resolution,
		t.Scene,
		t.RemasterYear,
		t.RemasterTitle,
        t.Slot,
		GREATEST(t.Seeders, 1) AS Seeders,
		xfh.seedtime AS Seedtime,
		(t.Size / (1024 * 1024 * 1024) * 1 *(
			0.025 + (
				(0.06 * LN(1 + (xfh.seedtime / (24)))) / (POW(GREATEST(t.Seeders, 1), 0.6))
			)
		)) AS HourlyPoints
	FROM
		(SELECT DISTINCT uid,fid FROM xbt_files_users WHERE active=1 AND remaining=0 AND mtime > unix_timestamp(NOW() - INTERVAL 1 HOUR) AND uid = ?) AS xfu
		JOIN xbt_files_history AS xfh ON xfh.uid = xfu.uid AND xfh.fid = xfu.fid
		JOIN torrents AS t ON t.ID = xfu.fid
	WHERE
		xfu.uid = ?
	$OrderBy
	LIMIT ?
	OFFSET ?", $UserID, $UserID, $Limit, $Offset);

    $GroupIDs = $DB->collect('GroupID');
    $Groups = Torrents::get_groups($GroupIDs, true, true, false);
}
?>
<div class=LayoutBody>
    <div class="BodyHeader">
        <h2 class="BodyHeader-nav"><?= $Title ?></h2>
        <h3>
            <span><?= t('server.bonus.total_points') ?>: </span>
            <span class="is-bp" id="bp-value" data-value="<?= $User['BonusPoints'] ?>">
                <?= number_format($User['BonusPoints']) ?>
            </span>
        </h3>
    </div>
    <div class="BodyNavLinks">
        <a class="brackets" href="/rules.php?p=bonus"><?= t('server.bonus.about_bonus_points') ?></a>
        <a class="brackets" href="/bonus.php"><?= t('server.bonus.bonus_points_shop') ?></a>
        <a class="brackets" href="/bonus.php?action=history"><?= t('server.bonus.history') ?></a>
        <a class="brackets" href="/top10.php?type=users&limit=10&details=bonus_points"><?= t('server.top10.top') . ' 10 ' . t('server.user.bonus_points') ?></a>
    </div>
    <?
    if (!empty($Pages)) {
    ?>
        <div class="BodyNavLinks">
            <?= $Pages ?>
        </div>
    <?
    }
    ?>
    <div class="TableContainer">
        <table class="TableBonusRate Table">
            <thead>
                <tr class="Table-rowHeader">
                    <td class="Table-cell"><?= t('server.bonus.total_torrents') ?></td>
                    <td class="Table-cell"><?= t('server.common.size') ?></td>
                    <td class="Table-cell"><?= t('server.bonus.bp_hour') ?></td>
                    <td class="Table-cell"><?= t('server.bonus.bp_day') ?></td>
                    <td class="Table-cell"><?= t('server.bonus.bp_week') ?></td>
                    <td class="Table-cell"><?= t('server.bonus.bp_month') ?></td>
                    <td class="Table-cell"><?= t('server.bonus.bp_year') ?></td>
                </tr>
            </thead>
            <tbody>
                <tr class="Table-row">
                    <td class="Table-cell is-seedingCount" id="seeding-count-value" data-value="<?= $TotalTorrents ?>">
                        <?= $TotalTorrents ?>
                    </td>
                    <td class="Table-cell is-seedingSize" id="seeding-size-value" data-value="<?= $TotalSize ?>">
                        <?= Format::get_size($TotalSize) ?>
                    </td>
                    <td class="Table-cell is-bpPerHour" id="bp-per-hour-value" data-value="<?= $TotalHourlyPoints ?>">
                        <?= number_format($TotalHourlyPoints) ?>
                    </td>
                    <td class="Table-cell is-bpPerDay" id="bp-per-day-value" data-value="<?= $TotalDailyPoints ?>">
                        <?= number_format($TotalDailyPoints) ?>
                    </td>
                    <td class="Table-cell is-bpPerWeek" id="bp-per-week-value" data-value="<?= $TotalWeeklyPoints ?>">
                        <?= number_format($TotalWeeklyPoints) ?>
                    </td>
                    <td class="Table-cell is-bpPerMonth" id="bp-per-month-value" data-value="<?= $TotalMonthlyPoints ?>">
                        <?= number_format($TotalMonthlyPoints) ?>
                    </td>
                    <td class="Table-cell is-bpPerYear" id="bp-per-year-value" data-value="<?= $TotalyearlyPoints ?>">
                        <?= number_format($TotalYearlyPoints) ?>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    <?
    $LinkTail = "&order_way=" . ($OrderWay == "asc" ? "desc" : "asc") . ($Page != 1 ? "&page=$Page" : "");
    ?>
    <?
    if ($TotalTorrents > 0) {
    ?>
        <div class="TableContainer">
            <table class="TableBonusRateDetail Table">
                <thead>
                    <tr class="Table-rowHeader">
                        <td class="Table-cell"><?= t('server.common.torrent') ?></td>
                        <td class="Table-cell"><a href="bonus.php?action=bprates&order_by=size<?= $LinkTail ?><?= $UserID == $LoggedUser['ID'] ? "" : "&userid=$UserID" ?>"><?= t('server.common.size') ?></a></td>
                        <td class="Table-cell"><a href="bonus.php?action=bprates&order_by=seeders<?= $LinkTail ?><?= $UserID == $LoggedUser['ID'] ? "" : "&userid=$UserID" ?>"><?= t('server.common.seeders') ?></a></td>
                        <td class="Table-cell"><a href="bonus.php?action=bprates&order_by=seedtime<?= $LinkTail ?><?= $UserID == $LoggedUser['ID'] ? "" : "&userid=$UserID" ?>"><?= t('server.bonus.seedtime') ?></a></td>
                        <td class="Table-cell"><a href="bonus.php?action=bprates&order_by=hourlypoints<?= $LinkTail ?><?= $UserID == $LoggedUser['ID'] ? "" : "&userid=$UserID" ?>"><?= t('server.bonus.bp_hour') ?></a></td>
                        <td class="Table-cell"><?= t('server.bonus.bp_day') ?></td>
                        <td class="Table-cell"><?= t('server.bonus.bp_week') ?></td>
                        <td class="Table-cell"><?= t('server.bonus.bp_month') ?></td>
                        <td class="Table-cell"><?= t('server.bonus.bp_year') ?></td>
                    </tr>
                </thead>
                <tbody>
                    <?
                    while ($Torrent = $DB->next_record(MYSQLI_ASSOC)) {
                        $Size = intval($Torrent['Size']);
                        $CorrectSize = $Torrent['CorrectSize'];
                        $Seeders = intval($Torrent['Seeders']);
                        $HourlyPoints = floatval($Torrent['HourlyPoints']);
                        $DailyPoints = $HourlyPoints * 24;
                        $WeeklyPoints = $DailyPoints * 7;
                        $MonthlyPoints = $DailyPoints * 30.436875;
                        $YearlyPoints = $DailyPoints * 365.2425;
                        $Torrent['Group'] = $Groups[$Torrent['GroupID']];
                        $Name = Torrents::torrent_simple_view($Torrent['Group'], $Torrent, true, [
                            'SettingTorrentTitle' => G::$LoggedUser['SettingTorrentTitle'],
                        ]);
                        $DisplayName = '<a href="torrents.php?id=' . $GroupID . '&amp;torrentid=' . $Torrent['ID'] . '"  data-tooltip="' . t('server.common.view_torrent') . '" dir="ltr">' . $Name . '</a>';
                    ?>
                        <tr class="Table-row">
                            <td class="Table-cell"><?= $DisplayName ?></td>
                            <td class="Table-cell"><?= Format::get_size($Torrent['Size']) ?></td>
                            <td class="Table-cell"><?= number_format($Seeders) ?></td>
                            <td class="Table-cell"><?= convert_hours($Torrent['Seedtime'], 2) ?></td>
                            <td class="Table-cell"><?= number_format($HourlyPoints) ?></td>
                            <td class="Table-cell"><?= number_format($DailyPoints) ?></td>
                            <td class="Table-cell"><?= number_format($WeeklyPoints) ?></td>
                            <td class="Table-cell"><?= number_format($MonthlyPoints) ?></td>
                            <td class="Table-cell"><?= number_format($YearlyPoints) ?></td>
                        </tr>
                    <? } ?>
                </tbody>
            </table>
        </div>
    <? } ?>
</div>
<?
View::show_footer();
?>