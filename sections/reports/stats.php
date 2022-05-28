<?

if (!check_perms('admin_reports') && !check_perms('site_moderate_forums')) {
    error(403);
}
View::show_header(Lang::get('reports', 'other_reports_stats'), '', 'PageReportStat');

?>
<div class="BodyHeader">
    <h2 class="BodyHeader-nav"><?= Lang::get('reports', 'other_reports_stats') ?></h2>
    <div class="BodyNavLinks">
        <a href="reports.php"><?= Lang::get('reports', 'new') ?></a>
        <a href="reports.php?view=old"><?= Lang::get('reports', 'old') ?></a>
        <a href="reports.php?action=stats"><?= Lang::get('reports', 'stats') ?></a>
    </div>
</div>
<div class="thin float_clear" id="other_reports_stats_details">
    <div class="two_columns pad">
        <?
        if (check_perms('admin_reports')) {
            $DB->query("
	SELECT um.Username,
		COUNT(r.ID) AS Reports
	FROM reports AS r
		JOIN users_main AS um ON um.ID = r.ResolverID
	WHERE r.ReportedTime > '2009-08-21 22:39:41'
		AND r.ReportedTime > NOW() - INTERVAL 24 HOUR
	GROUP BY r.ResolverID
	ORDER BY Reports DESC");
            $Results = $DB->to_array();
        ?>
            <h3><strong><?= Lang::get('reports', 'reports_resolved_in_the_last_24_hours') ?></strong></h3>
            <table class="Table">
                <tr class="Table-row">
                    <td class="Table-cell"><?= Lang::get('reports', 'username') ?></td>
                    <td class="Table-cell Table-cellRight"><?= Lang::get('reports', 'reports') ?></td>
                </tr>
                <?
                foreach ($Results as $Result) {
                    list($Username, $Reports) = $Result;
                    if ($Username == $LoggedUser['Username']) {
                        $RowClass = ' class="rowa"';
                    } else {
                        $RowClass = '';
                    }
                ?>
                    <tr class="Table-row">
                        <td class="Table-cell"><?= $Username ?></td>
                        <td class="Table-cell Table-cellRight"><?= number_format($Reports) ?></td>
                    </tr>
                <?  } ?>
            </table>
            <?
            $DB->query("
	SELECT um.Username,
		COUNT(r.ID) AS Reports
	FROM reports AS r
		JOIN users_main AS um ON um.ID = r.ResolverID
	WHERE r.ReportedTime > '2009-08-21 22:39:41'
		AND r.ReportedTime > NOW() - INTERVAL 1 WEEK
	GROUP BY r.ResolverID
	ORDER BY Reports DESC");
            $Results = $DB->to_array();
            ?>
            <h3><strong><?= Lang::get('reports', 'reports_resolved_in_the_last_week') ?></strong></h3>
            <table class="Table">
                <tr class="Table-rowHeader">
                    <td class="Table-cell"><?= Lang::get('reports', 'username') ?></td>
                    <td class="Table-cell Table-cellRight"><?= Lang::get('reports', 'reports') ?></td>
                </tr>
                <?
                foreach ($Results as $Result) {
                    list($Username, $Reports) = $Result;
                    if ($Username == $LoggedUser['Username']) {
                        $RowClass = ' class="rowa"';
                    } else {
                        $RowClass = '';
                    }
                ?>
                    <tr class="Table-row">
                        <td class="Table-cell"><?= $Username ?></td>
                        <td class="Table-cell" number_column"><?= number_format($Reports) ?></td>
                    </tr>
                <?  } ?>
            </table>
            <?
            $DB->query("
	SELECT um.Username,
		COUNT(r.ID) AS Reports
	FROM reports AS r
		JOIN users_main AS um ON um.ID = r.ResolverID
	WHERE r.ReportedTime > '2009-08-21 22:39:41'
		AND r.ReportedTime > NOW() - INTERVAL 1 MONTH
	GROUP BY r.ResolverID
	ORDER BY Reports DESC");
            $Results = $DB->to_array();
            ?>
            <h3><strong><?= Lang::get('reports', 'reports_resolved_in_the_last_month') ?></strong></h3>
            <table class="Table">
                <tr class="Table-rowHeader">
                    <td class="Table-cell"><?= Lang::get('reports', 'username') ?></td>
                    <td class="Table-cell Table-cellRight"><?= Lang::get('reports', 'reports') ?></td>
                </tr>
                <?
                foreach ($Results as $Result) {
                    list($Username, $Reports) = $Result;
                    if ($Username == $LoggedUser['Username']) {
                        $RowClass = ' class="rowa"';
                    } else {
                        $RowClass = '';
                    }
                ?>
                    <tr class="Table-row">
                        <td class="Table-cell"><?= $Username ?></td>
                        <td class="Table-cell Table-cellRight"><?= number_format($Reports) ?></td>
                    </tr>
                <?  } ?>
            </table>
            <?
            $DB->query("
	SELECT um.Username,
		COUNT(r.ID) AS Reports
	FROM reports AS r
		JOIN users_main AS um ON um.ID = r.ResolverID
	GROUP BY r.ResolverID
	ORDER BY Reports DESC");
            $Results = $DB->to_array();
            ?>
            <h3><strong><?= Lang::get('reports', 'reports_resolved_since_other_reports') ?></strong></h3>
            <table class="Table">
                <tr class="Table-rowHeader">
                    <td class="Table-cell"><?= Lang::get('reports', 'username') ?></td>
                    <td class="Table-cell Table-cellRight"><?= Lang::get('reports', 'reports') ?></td>
                </tr>
                <?
                foreach ($Results as $Result) {
                    list($Username, $Reports) = $Result;
                    if ($Username == $LoggedUser['Username']) {
                        $RowClass = ' class="rowa"';
                    } else {
                        $RowClass = '';
                    }
                ?>
                    <tr class="Table-row">
                        <td class="Table-cell"><?= $Username ?></td>
                        <td class="Table-cell Table-cellRight"><?= number_format($Reports) ?></td>
                    </tr>
                <?  } ?>
            </table>
        <?
        } /* if (check_perms('admin_reports')) */ ?>
    </div>
    <div class="two_columns pad">
        <?

        $TrashForumIDs = '12';

        $DB->query("
		SELECT u.Username,
			COUNT(f.LastPostAuthorID) as Trashed
		FROM forums_topics AS f
			LEFT JOIN users_main AS u ON u.ID = f.LastPostAuthorID
		WHERE f.ForumID IN ($TrashForumIDs)
		GROUP BY f.LastPostAuthorID
		ORDER BY Trashed DESC
		LIMIT 30");
        $Results = $DB->to_array();
        ?>
        <h3><strong><?= Lang::get('reports', 'threads_trashed_since_the_beginning_of_time') ?></strong></h3>
        <table class="Table">
            <tr class="Table-rowHeader">
                <td class="Table-cell Table-cellRight"><?= Lang::get('reports', 'place') ?></td>
                <td class="Table-cell"><?= Lang::get('reports', 'username') ?></td>
                <td class="Table-cell Table-cellRight"><?= Lang::get('reports', 'trashed') ?></td>
            </tr>
            <?
            $i = 1;
            foreach ($Results as $Result) {
                list($Username, $Trashed) = $Result;
                if ($Username == $LoggedUser['Username']) {
                    $RowClass = ' class="rowa"';
                } else {
                    $RowClass = '';
                }
            ?>
                <tr class="Table-row">
                    <td class="Table-cell Table-cellRight"><?= $i ?></td>
                    <td class="Table-cell"><?= $Username ?></td>
                    <td class="Table-cell Table-cellRight"><?= number_format($Trashed) ?></td>
                </tr>
            <?
                $i++;
            }
            ?>
        </table>
    </div>
</div>
<?
View::show_footer();
?>