<?

if (!check_perms('admin_reports') && !check_perms('site_moderate_forums')) {
    error(403);
}
View::show_header(t('server.reports.other_reports_stats'), '', 'PageReportStat');

?>
<div class="LayoutBody">
    <div class="BodyHeader">
        <h2 class="BodyHeader-nav"><?= t('server.reports.other_reports_stats') ?></h2>
        <div class="BodyNavLinks">
            <a href="reports.php"><?= t('server.reports.new') ?></a>
            <a href="reports.php?view=old"><?= t('server.reports.old') ?></a>
            <a href="reports.php?action=stats"><?= t('server.reports.stats') ?></a>
        </div>
    </div>
    <div class="Permissions" id="other_reports_stats_details">
        <div class="permission_container">
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
                <div class="Group">
                    <div class="Group-header">
                        <div class="Group-headerTitle">
                            <?= t('server.reports.reports_resolved_in_the_last_24_hours') ?>
                        </div>
                    </div>
                    <div class="Group-body">
                        <table class="Table">
                            <tr class="Table-rowHeader">
                                <td class="Table-cell"><?= t('server.reports.username') ?></td>
                                <td class="Table-cell Table-cellRight"><?= t('server.reports.reports') ?></td>
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
                    </div>
                </div>


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
                <div class="Group">
                    <div class="Group-header">
                        <div class="Group-headerTitle">
                            <?= t('server.reports.reports_resolved_since_other_reports') ?>
                        </div>
                    </div>
                    <div class="Group-body">
                        <table class="Table">
                            <tr class="Table-rowHeader">
                                <td class="Table-cell"><?= t('server.reports.username') ?></td>
                                <td class="Table-cell Table-cellRight"><?= t('server.reports.reports') ?></td>
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
                    </div>
                </div>
            <?
            } /* if (check_perms('admin_reports')) */ ?>
        </div>
        <div class="permission_container">
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
            <div class="Group">
                <div class="Group-header">
                    <div class="Group-headerTitle">
                        <?= t('server.reports.reports_resolved_in_the_last_week') ?>
                    </div>
                </div>
                <div class="Group-body">
                    <table class="Table">
                        <tr class="Table-rowHeader">
                            <td class="Table-cell"><?= t('server.reports.username') ?></td>
                            <td class="Table-cell Table-cellRight"><?= t('server.reports.reports') ?></td>
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
                </div>
            </div>
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
            <div class="Group">
                <div class="Group-header">
                    <div class="Group-headerTitle">
                        <?= t('server.reports.threads_trashed_since_the_beginning_of_time') ?>
                    </div>
                </div>
                <div class="Group-body">
                    <table class="Table">
                        <tr class="Table-rowHeader">
                            <td class="Table-cell Table-cellRight"><?= t('server.reports.place') ?></td>
                            <td class="Table-cell"><?= t('server.reports.username') ?></td>
                            <td class="Table-cell Table-cellRight"><?= t('server.reports.trashed') ?></td>
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
        </div>
        <div class="permission_container">
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
            <div class="Group">
                <div class="Group-header">
                    <div class="Group-headerTitle">
                        <?= t('server.reports.reports_resolved_in_the_last_month') ?>
                    </div>
                </div>
                <div class="Group-body">
                    <table class="Table">
                        <tr class="Table-rowHeader">
                            <td class="Table-cell"><?= t('server.reports.username') ?></td>
                            <td class="Table-cell Table-cellRight"><?= t('server.reports.reports') ?></td>
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
                </div>
            </div>
        </div>
    </div>
</div>
<?

View::show_footer();
?>