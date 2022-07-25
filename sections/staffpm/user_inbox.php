<?

View::show_header(t('server.staffpm.staff_pms'), 'staffpm', 'PageStaffPMUserInbox');

// Get messages
$StaffPMs = $DB->query(
    "
	SELECT
		ID,
		Subject,
		UserID,
		Status,
		Level,
		AssignedToUser,
		Date,
		Unread
	FROM staff_pm_conversations
	WHERE UserID = " . $LoggedUser['ID'] . "
	ORDER BY Status, Date DESC"
);

// Start page
?>
<div class="LayoutBody">
    <div class="BodyHeader">
        <h2 class="BodyHeader-nav"><?= t('server.staffpm.staff_pms') ?></h2>
        <div class="BodyNavLinks">
            <a href="#" onclick="$('#compose').gtoggle();" class="brackets"><?= t('server.staffpm.compose_new') ?></a>
        </div>
    </div>
    <br />
    <br />
    <? View::parse('generic/reply/staffpm.php', array('Hidden' => true)); ?>
    <div class="BoxBody" id="inbox">
        <?

        if (!$DB->has_results()) {
            // No messages
        ?>
            <h2><?= t('server.staffpm.no_messages') ?></h2>
        <?
        } else {
            // Messages, draw table
        ?>
            <form class="manage_form" name="staff_messages" method="post" action="staffpm.php" id="messageform">
                <input type="hidden" name="action" value="multiresolve" />
                <h3><?= t('server.staffpm.open_messages') ?></h3>
                <div class="TableContainer">
                    <table class="TableUserInbox">
                        <tr class="Table-rowHeader">
                            <td class="Table-cell" width="10"><input type="checkbox" onclick="toggleChecks('messageform', this);" /></td>
                            <td class="Table-cell" width="50%"><?= t('server.staffpm.subject') ?></td>
                            <td class="Table-cell"><?= t('server.staffpm.date') ?></td>
                            <td class="Table-cell"><?= t('server.staffpm.assigned_to') ?></td>
                        </tr>
                        <?
                        // List messages
                        $Row = 'a';
                        $ShowBox = 1;
                        while (list($ID, $Subject, $UserID, $Status, $Level, $AssignedToUser, $Date, $Unread) = $DB->next_record()) {
                            if ($Unread === '1') {
                                $RowClass = 'unreadpm';
                            } else {
                                $Row = $Row === 'a' ? 'b' : 'a';
                                $RowClass = "row$Row";
                            }

                            if ($Status == 'Resolved') {
                                $ShowBox++;
                            }
                            if ($ShowBox == 2) {
                                // First resolved PM
                        ?>
                    </table>
                </div>

                <h3><?= t('server.staffpm.resolved_messages') ?></h3>
                <div class="TableContainer">
                    <table class="TableUserInbox Table">
                        <tr class="Table-rowHeader">
                            <td class="Table-cell" width="10"><input type="checkbox" onclick="toggleChecks('messageform',this)" /></td>
                            <td class="Table-cell" width="50%"><?= t('server.staffpm.subject') ?></td>
                            <td class="Table-cell"><?= t('server.staffpm.date') ?></td>
                            <td class="Table-cell"><?= t('server.staffpm.assigned_to') ?></td>
                        </tr>
                    <?
                            }

                            // Get assigned
                            $Assigned = ($Level == 0) ? 'First Line Support' : $ClassLevels[$Level]['Name'];
                            // No + on Sysops
                            if ($Assigned != 'Sysop') {
                                $Assigned .= '+';
                            }

                            // Table row
                    ?>
                    <tr class="Table-row">
                        <td class="Table-cell Table-cellCenter"><input type="checkbox" name="id[]" value="<?= $ID ?>" /></td>
                        <td class="Table-cell"><a href="staffpm.php?action=viewconv&amp;id=<?= $ID ?>"><?= display_str($Subject) ?></a></td>
                        <td class="Table-cell"><?= time_diff($Date, 2, true) ?></td>
                        <td class="Table-cell"><?= $Assigned ?></td>
                    </tr>
                <?
                            $DB->set_query_id($StaffPMs);
                        }

                        // Close table and multiresolve form
                ?>
                    </table>
                </div>
                <div class="submit_div">
                    <input class="Button" type="submit" value="Resolve selected" />
                </div>
            </form>
        <?
        }
        ?>
    </div>
</div>
<? View::show_footer(); ?>