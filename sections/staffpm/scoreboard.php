<?php
require(__DIR__ . '/../staff/functions.php');
$View   = ($_REQUEST['view'] ?? 'staff');
$Action = ($_REQUEST['action'] ?? 'stats');

if (!$IsStaff && !$IsFLS) {
    error(403);
}
View::show_header('Staff Inbox', 'PageStaffPMScoreboard');
?>
<div class="LayoutBody">
    <div class="BodyHeader">
        <h2 class="BodyHeader-nav"><?= $ViewString ?><?= t('server.staffpm.scoreboard') ?></h2>
        <div class="BodyNavLinks">
            <? if ($IsStaff) { ?>
                <a href="staffpm.php" class="brackets"><?= t('server.staffpm.view_your_unanswered') ?></a>
            <?  } ?>
            <a href="staffpm.php?view=unanswered" class="brackets"><?= t('server.staffpm.view_all_unanswered') ?></a>
            <a href="staffpm.php?view=open" class="brackets"><?= t('server.staffpm.view_unresolved') ?></a>
            <a href="staffpm.php?view=resolved" class="brackets"><?= t('server.staffpm.view_resolved') ?></a>
            <? if ($IsStaff) { ?>
                <a href="staffpm.php?action=scoreboard" class="brackets"><?= t('server.staffpm.view_scoreboard') ?></a>
            <?  }

            if ($IsFLS && !$IsStaff) { ?>
                <span data-tooltip="This is the inbox where replies to Staff PMs you have sent are."><a href="staffpm.php?action=userinbox" class="brackets"><?= t('server.staffpm.personal_staff_inbox') ?></a></span>
            <?  } ?>
        </div>
    </div>
    <div class="BodyContent">
        <?php

        $SupportStaff = get_support();
        [$FrontLineSupport, $Staff] = $SupportStaff;
        $SupportStaff = array_merge($FrontLineSupport, ...array_values($Staff));
        $SupportStaff = array_column($SupportStaff, 'ID');

        if ($View != 'staff') {
            $IN    = "NOT IN";
            $COL   = "PMs";
            $EXTRA = "(SELECT count(*)
                FROM staff_pm_conversations AS spc
                WHERE spc.UserID=um.ID
                AND spc.Date > now() - INTERVAL ? DAY)";
        } else {
            $IN    = "IN";
            $COL   = t('server.staffpm.resolved_num');
            $EXTRA = "(SELECT count(*)
                FROM staff_pm_conversations AS spc
                WHERE spc.ResolverID=um.ID
                AND spc.Status = 'Resolved'
                AND spc.Date > now() - INTERVAL ? DAY)";
        }

        $BaseSQL = sprintf(
            "
    SELECT um.ID,
        um.Username,
        count(*) AS Num,
        %s AS Extra
    FROM staff_pm_messages AS spm
    INNER JOIN users_main AS um ON um.ID=spm.UserID
    INNER JOIN permissions p ON p.ID = um.PermissionID
    WHERE spm.SentDate > now() - INTERVAL ? DAY AND p.Level <= ? AND um.ID %s (%s)
    GROUP BY spm.UserID
    ORDER BY Num DESC
    LIMIT 50
    ",
            $EXTRA,
            $IN,
            placeholders($SupportStaff)
        );

        $DB->prepared_query($BaseSQL, 1, 1, $LoggedUser['Class'], ...$SupportStaff);
        $Results = $DB->to_array();
        ?>
        <div class="Permissions">
            <div class="permission_container">
                <div class="Group">
                    <div class="Group-header">
                        <div class="Group-headerTitle"><?= t('server.staffpm.inbox_action_last_24_hours') ?></div>
                    </div>
                    <div class="Group-body">
                        <table class="Table">
                            <tr class="Table-rowHeader">
                                <td class="Table-cell"><?= t('server.staff.username') ?></td>
                                <td class="Table-cell"><?= t('server.staffpm.replies') ?></td>
                                <td class="Table-cell"><?= $COL ?></td>
                            </tr>
                            <?php
                            foreach ($Results as $Result) {
                                [$UserID, $Username, $Num, $Extra] = $Result;
                            ?>
                                <tr class="Table-row">
                                    <td class="Table-cell"><a href="/reportsv2.php?view=resolver&amp;id=<?= $UserID ?>"><?= $Username ?></a></td>
                                    <td class="Table-cell"><?= $Num ?></td>
                                    <td class="Table-cell"><?= $Extra ?></td>
                                </tr>
                            <?php } ?>
                        </table>
                    </div>
                </div>
                <?php
                $DB->prepared_query($BaseSQL, 365000, 365000, $LoggedUser['Class'], ...$SupportStaff);
                $Results = $DB->to_array();
                ?>
                <div class="Group">
                    <div class="Group-header">
                        <div class="Group-headerTitle"><?= t('server.staffpm.inbox_actions_total') ?></div>
                    </div>
                    <div class="Group-body">
                        <table class="Table">
                            <tr class="Table-rowHeader">
                                <td class="Table-cell"><?= t('server.staff.username') ?></td>
                                <td class="Table-cell"><?= t('server.staffpm.replies') ?></td>
                                <td class="Table-cell"><?= $COL ?></td>
                            </tr>
                            <?php
                            foreach ($Results as $Result) {
                                [$UserID, $Username, $Num, $Extra] = $Result;
                            ?>
                                <tr class="Table-row">
                                    <td class="Table-cell"><a href="/reportsv2.php?view=resolver&amp;id=<?= $UserID ?>"><?= $Username ?></a></td>
                                    <td class="Table-cell"><?= $Num ?></td>
                                    <td class="Table-cell"><?= $Extra ?></td>
                                </tr>
                            <?php } ?>
                        </table>
                    </div>
                </div>
            </div>
            <?php
            $DB->prepared_query($BaseSQL, 7, 7, $LoggedUser['Class'], ...$SupportStaff);
            $Results = $DB->to_array();
            ?>
            <div class="permission_container">
                <div class="Group">
                    <div class="Group-header">
                        <div class="Group-headerTitle"><?= t('server.staffpm.inbox_action_last_week') ?></div>
                    </div>
                    <div class="Group-body">
                        <table class="Table">
                            <tr class="Table-rowHeader">
                                <td class="Table-cell"><?= t('server.staff.username') ?></td>
                                <td class="Table-cell"><?= t('server.staffpm.replies') ?></td>
                                <td class="Table-cell"><?= $COL ?></td>
                            </tr>
                            <?php
                            foreach ($Results as $Result) {
                                [$UserID, $Username, $Num, $Extra] = $Result;
                            ?>
                                <tr class="Table-row">
                                    <td class="Table-cell"><a href="/reportsv2.php?view=resolver&amp;id=<?= $UserID ?>"><?= $Username ?></a></td>
                                    <td class="Table-cell"><?= $Num ?></td>
                                    <td class="Table-cell"><?= $Extra ?></td>
                                </tr>
                            <?php } ?>
                        </table>
                    </div>
                </div>
            </div>
            <?php
            $DB->prepared_query($BaseSQL, 30, 30, $LoggedUser['Class'], ...$SupportStaff);
            $Results = $DB->to_array();
            ?>
            <div class="permission_container">
                <div class="Group">
                    <div class="Group-header">
                        <div class="Group-headerTitle"><?= t('server.staffpm.inbox_action_last_month') ?></div>
                    </div>
                    <div class="Group-body">
                        <table class="Table">
                            <tr class="Table-rowHeader">
                                <td class="Table-cell"><?= t('server.staff.username') ?></td>
                                <td class="Table-cell"><?= t('server.staffpm.replies') ?></td>
                                <td class="Table-cell"><?= $COL ?></td>
                            </tr>
                            <?php
                            foreach ($Results as $Result) {
                                [$UserID, $Username, $Num, $Extra] = $Result;
                            ?>
                                <tr class="Table-row">
                                    <td class="Table-cell"><a href="/reportsv2.php?view=resolver&amp;id=<?= $UserID ?>"><?= $Username ?></a></td>
                                    <td class="Table-cell"><?= $Num ?></td>
                                    <td class="Table-cell"><?= $Extra ?></td>
                                </tr>
                            <?php } ?>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<?php
View::show_footer();
