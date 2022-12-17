<?php
if (!check_perms('site_view_flow')) {
    error(403);
}

define('DAYS_PER_PAGE', 100);
list($Page, $Limit) = Format::page_limit(DAYS_PER_PAGE);

$RS = $DB->query("
		SELECT
			SQL_CALC_FOUND_ROWS
			j.Date,
			DATE_FORMAT(j.Date, '%Y-%m') AS Month,
			CASE ISNULL(j.Flow)
				WHEN 0 THEN j.Flow
				ELSE '0'
			END AS Joined,
			CASE ISNULL(m.Flow)
				WHEN 0 THEN m.Flow
				ELSE '0'
			END AS Manual,
			CASE ISNULL(r.Flow)
				WHEN 0 THEN r.Flow
				ELSE '0'
			END AS Ratio,
			CASE ISNULL(i.Flow)
				WHEN 0 THEN i.Flow
				ELSE '0'
			END AS Inactivity
		FROM (
				SELECT
					DATE_FORMAT(JoinDate, '%Y-%m-%d') AS Date,
					COUNT(UserID) AS Flow
				FROM users_info
				WHERE JoinDate != '0000-00-00 00:00:00'
				GROUP BY Date
			) AS j
			LEFT JOIN (
				SELECT
					DATE_FORMAT(BanDate, '%Y-%m-%d') AS Date,
					COUNT(UserID) AS Flow
				FROM users_info
				WHERE BanDate != '0000-00-00 00:00:00'
					AND BanReason = '1'
				GROUP BY Date
			) AS m ON j.Date = m.Date
			LEFT JOIN (
				SELECT
					DATE_FORMAT(BanDate, '%Y-%m-%d') AS Date,
					COUNT(UserID) AS Flow
				FROM users_info
				WHERE BanDate != '0000-00-00 00:00:00'
					AND BanReason = '2'
				GROUP BY Date
			) AS r ON j.Date = r.Date
			LEFT JOIN (
				SELECT
					DATE_FORMAT(BanDate, '%Y-%m-%d') AS Date,
					COUNT(UserID) AS Flow
				FROM users_info
				WHERE BanDate != '0000-00-00 00:00:00'
					AND BanReason = '3'
				GROUP BY Date
			) AS i ON j.Date = i.Date
		ORDER BY j.Date DESC
		LIMIT $Limit");
$DB->query('SELECT FOUND_ROWS()');
list($Results) = $DB->next_record();

$Pages = Format::get_pages($Page, $Results, DAYS_PER_PAGE, 11);
View::show_header(t('server.tools.h2_user_flow'), '', 'PageToolUserFlow');
$DB->set_query_id($RS);
?>
<div class="LayoutBody">
    <div class="BodyHeader">
        <h2 class="BodyHeader-nav"><?= t('server.tools.user_flow') ?></h2>
    </div>
    <? if (!isset($_GET['page'])) { ?>
        <div class="ChartRoot">
            <div id="chart_user_timeline">
            </div>
        </div>
    <?  } ?>
    <? View::pages($Pages) ?>
    <table class="TableUserFlow Table">
        <tr class="Table-rowHeader">
            <td class="Table-cell"><?= t('server.tools.date') ?></td>
            <td class="Table-cell">(+) <?= t('server.tools.joined') ?></td>
            <td class="Table-cell">(-) <?= t('server.tools.manual') ?></td>
            <td class="Table-cell">(-) <?= t('server.tools.ratio') ?></td>
            <td class="Table-cell">(-) <?= t('server.tools.inactivity') ?></td>
            <td class="Table-cell">(-) <?= t('server.tools.total') ?></td>
            <td class="Table-cell"><?= t('server.tools.net_growth') ?></td>
        </tr>
        <?
        while (list($Date, $Month, $Joined, $Manual, $Ratio, $Inactivity) = $DB->next_record()) {
            $TotalOut = $Ratio + $Inactivity + $Manual;
            $TotalGrowth = $Joined - $TotalOut;
        ?>
            <tr class="Table-row">
                <td class="Table-cell"><?= $Date ?></td>
                <td class="Table-cell"><?= number_format($Joined) ?></td>
                <td class="Table-cell"><?= number_format($Manual) ?></td>
                <td class="Table-cell"><?= number_format((float)$Ratio) ?></td>
                <td class="Table-cell"><?= number_format($Inactivity) ?></td>
                <td class="Table-cell"><?= number_format($TotalOut) ?></td>
                <td class="Table-cell"><?= number_format($TotalGrowth) ?></td>
            </tr>
        <?  } ?>
    </table>
    <? View::pages($Pages) ?>
</div>
<?
Stats::userTimeLine();
View::show_footer([], 'stats/index'); ?>