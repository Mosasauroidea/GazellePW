<?
/*
 * This page is to outline all of the views built into reports v2.
 * It's used as the main page as it also lists the current reports by type
 * and the current in-progress reports by staff member.
 * All the different views are self explanatory by their names.
 */

if (!check_perms('admin_reports')) {
    error(403);
}

View::show_header(t('server.reportsv2.reports_v2'), 'reportsv2', 'PageReportV2Views');


//Grab owner's ID, just for examples
$DB->query("
	SELECT ID, Username
	FROM users_main
	ORDER BY ID ASC
	LIMIT 1");
list($OwnerID, $Owner) = $DB->next_record();
$Owner = display_str($Owner);

?>
<div class="LayoutBody">
    <div class="BodyHeader">
        <h2 class="BodyHeader-nav"><?= t('server.reportsv2.reports_v2_information') ?></h2>
        <? include('header.php'); ?>
    </div>
    <div class="Permissions">
        <div class="permission_container">
            <?
            $DB->query("
	SELECT
		um.ID,
		um.Username,
		COUNT(r.ID) AS Reports
	FROM reportsv2 AS r
		JOIN users_main AS um ON um.ID = r.ResolverID
	WHERE r.LastChangeTime > NOW() - INTERVAL 24 HOUR
	GROUP BY r.ResolverID
	ORDER BY Reports DESC");
            $Results = $DB->to_array();
            ?>
            <div class="Group">
                <div class="Group-header">
                    <div class="Group-headerTitle">
                        <?= t('server.reportsv2.time_d') ?>
                    </div>
                </div>
                <div class="Group-body">
                    <table class="Table">
                        <tr class="Table-rowHeader">
                            <td class="Table-cell"><?= t('server.reportsv2.username') ?></td>
                            <td class="Table-cell Table-cellRight"><?= t('server.reportsv2.reports') ?></td>
                        </tr>
                        <?
                        foreach ($Results as $Result) {
                            list($UserID, $Username, $Reports) = $Result;
                            if ($Username == $LoggedUser['Username']) {
                                $RowClass = ' class="rowa"';
                            } else {
                                $RowClass = '';
                            }
                        ?>
                            <tr class="Table-row">
                                <td class="Table-cell"><a href="reportsv2.php?view=resolver&amp;id=<?= $UserID ?>"><?= $Username ?></a></td>
                                <td class="Table-cell Table-cellRight"><?= number_format($Reports) ?></td>
                            </tr>
                        <?
                        }
                        ?>
                    </table>
                </div>
            </div>
            <?
            $DB->query("
	SELECT
		um.ID,
		um.Username,
		COUNT(r.ID) AS Reports
	FROM reportsv2 AS r
		JOIN users_main AS um ON um.ID = r.ResolverID
	WHERE r.LastChangeTime > NOW() - INTERVAL 1 WEEK
	GROUP BY r.ResolverID
	ORDER BY Reports DESC");
            $Results = $DB->to_array();
            ?>
            <div class="Group">
                <div class="Group-header">
                    <div class="Group-headerTitle">
                        <?= t('server.reportsv2.time_w') ?>
                    </div>
                </div>
                <div class="Group-body">
                    <table class="Table">
                        <tr class="Table-rowHeader">
                            <td class="Table-cell"><?= t('server.reportsv2.username') ?></td>
                            <td class="Table-cell Table-cellRight"><?= t('server.reportsv2.reports') ?></td>
                        </tr>
                        <?
                        foreach ($Results as $Result) {
                            list($UserID, $Username, $Reports) = $Result;
                            if ($Username == $LoggedUser['Username']) {
                                $RowClass = ' class="rowa"';
                            } else {
                                $RowClass = '';
                            }
                        ?>
                            <tr class="Table-row">
                                <td class="Table-cell"><a href="reportsv2.php?view=resolver&amp;id=<?= $UserID ?>"><?= $Username ?></a></td>
                                <td class="Table-cell Table-cellRight"><?= number_format($Reports) ?></td>
                            </tr>
                        <?
                        }
                        ?>
                    </table>
                </div>
            </div>

        </div>
        <div class="permission_container">
            <?
            $DB->query("
	SELECT
		um.ID,
		um.Username,
		COUNT(r.ID) AS Reports
	FROM reportsv2 AS r
		JOIN users_main AS um ON um.ID = r.ResolverID
	WHERE r.LastChangeTime > NOW() - INTERVAL 1 MONTH
	GROUP BY r.ResolverID
	ORDER BY Reports DESC");
            $Results = $DB->to_array();
            ?>
            <div class="Group">
                <div class="Group-header">
                    <div class="Group-headerTitle">
                        <?= t('server.reportsv2.time_m') ?>
                    </div>
                </div>
                <div class="Group-body">
                    <table class="Table">
                        <tr class="Table-rowHeader">
                            <td class="Table-cell"><?= t('server.reportsv2.username') ?></td>
                            <td class="Table-cell Table-cellRight"><?= t('server.reportsv2.reports') ?></td>
                        </tr>
                        <?
                        foreach ($Results as $Result) {
                            list($UserID, $Username, $Reports) = $Result;
                            if ($Username == $LoggedUser['Username']) {
                                $RowClass = ' class="rowa"';
                            } else {
                                $RowClass = '';
                            }
                        ?>
                            <tr class="Table-row">
                                <td class="Table-cell"><a href="reportsv2.php?view=resolver&amp;id=<?= $UserID ?>"><?= $Username ?></a></td>
                                <td class="Table-cell Table-cellRight"><?= number_format($Reports) ?></td>
                            </tr>
                        <?
                        }
                        ?>
                    </table>
                </div>
            </div>
            <?

            $DB->query("
	SELECT
		um.ID,
		um.Username,
		COUNT(r.ID) AS Reports
	FROM reportsv2 AS r
		JOIN users_main AS um ON um.ID = r.ResolverID
	GROUP BY r.ResolverID
	ORDER BY Reports DESC");
            $Results = $DB->to_array();
            ?>
            <div class="Group">
                <div class="Group-header">
                    <div class="Group-headerTitle">
                        <?= t('server.reportsv2.time_a') ?>
                    </div>
                </div>
                <div class="Group-body">
                    <table class="Table">
                        <tr class="Table-rowHeader">
                            <td class="Table-cell"><?= t('server.reportsv2.username') ?></td>
                            <td class="Table-cell Table-cellRight"><?= t('server.reportsv2.reports') ?></td>
                        </tr>
                        <?
                        foreach ($Results as $Result) {
                            list($UserID, $Username, $Reports) = $Result;
                            if ($Username == $LoggedUser['Username']) {
                                $RowClass = ' class="rowa"';
                            } else {
                                $RowClass = '';
                            }
                        ?>
                            <tr class="Table-row">
                                <td class="Table-cell"><a href="reportsv2.php?view=resolver&amp;id=<?= $UserID ?>"><?= $Username ?></a></td>
                                <td class="Table-cell Table-cellRight"><?= number_format($Reports) ?></td>
                            </tr>
                        <?
                        }
                        ?>
                    </table>
                </div>
            </div>
        </div>
        <div class="permission_container">
            <?
            $DB->query("
		SELECT
			r.ResolverID,
			um.Username,
			COUNT(r.ID) AS Count
		FROM reportsv2 AS r
			LEFT JOIN users_main AS um ON r.ResolverID = um.ID
		WHERE r.Status = 'InProgress'
		GROUP BY r.ResolverID");

            $Staff = $DB->to_array();
            ?>
            <div class="Group">
                <div class="Group-header">
                    <div class="Group-headerTitle">
                        <?= t('server.reportsv2.currently_assigned_reports_by_staff_member') ?>
                    </div>
                </div>
                <div class="Group-body">
                    <table class="Table">
                        <tr class="Table-rowHeader">
                            <td class="Table-cell"><?= t('server.reportsv2.staff_member') ?></td>
                            <td class="Table-cell Table-cellRight"><?= t('server.reportsv2.current_count') ?></td>
                        </tr>
                        <?
                        foreach ($Staff as $Array) {
                            if ($Array['Username'] == $LoggedUser['Username']) {
                                $RowClass = ' class="rowa"';
                            } else {
                                $RowClass = '';
                            }
                        ?>
                            <tr class="Table-row">
                                <td class="Table-cell">
                                    <a href="reportsv2.php?view=staff&amp;id=<?= $Array['ResolverID'] ?>"><?= display_str($Array['Username']) ?><?= t('server.reportsv2.s_reports') ?></a>
                                </td>
                                <td class="Table-cell Table-cellRight"><?= number_format($Array['Count']) ?></td>
                            </tr>
                        <?  } ?>
                    </table>
                </div>
            </div>
            <div class="Group">
                <div class="Group-header">
                    <div class="Group-headerTitle">
                        <?= t('server.reportsv2.different_view_modes_by_report_type') ?>
                    </div>
                </div>
                <div class="Group-body">
                    <?
                    $DB->query("
		SELECT
			Type,
			COUNT(ID) AS Count
		FROM reportsv2
		WHERE Status = 'New'
		GROUP BY Type");
                    $Current = $DB->to_array();
                    if (!empty($Current)) {
                    ?>
                        <table class="Table">
                            <tr class="Table-rowHeader">
                                <td class="Table-cell"><?= t('server.reportsv2.type') ?></td>
                                <td class="Table-cell Table-cellRight"><?= t('server.reportsv2.current_count') ?></td>
                            </tr>
                            <?
                            foreach ($Current as $Array) {
                                //Ugliness
                                foreach ($Types as $Category) {
                                    if (!empty($Category[$Array['Type']])) {
                                        $Title = $Category[$Array['Type']]['title'];
                                        break;
                                    }
                                }
                            ?>
                                <tr class="Table-row" <?= $Title === 'Urgent' ? ' style="font-weight: bold;"' : '' ?>>
                                    <td class="Table-cell">
                                        <a href="reportsv2.php?view=type&amp;id=<?= display_str($Array['Type']) ?>"><?= display_str($Title) ?></a>
                                    </td>
                                    <td class="Table-cell Table-cellRight">
                                        <?= number_format($Array['Count']) ?>
                                    </td>
                                </tr>
                        <?
                            }
                        }
                        ?>
                        </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?
View::show_footer();
?>