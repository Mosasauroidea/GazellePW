<?

if ($ConvID = (int)$_GET['id']) {
    // Get conversation info
    $DB->query("
		SELECT Subject, UserID, Level, AssignedToUser, Unread, Status
		FROM staff_pm_conversations
		WHERE ID = $ConvID");
    list($Subject, $UserID, $Level, $AssignedToUser, $Unread, $Status) = $DB->next_record();

    $LevelCap = 1000;


    $PMLevel = $Level;
    $Level = min($Level, $LevelCap);

    if (!(($UserID == $LoggedUser['ID'])
        || ($AssignedToUser == $LoggedUser['ID'])
        || (($Level > 0 && $Level <= $LoggedUser['EffectiveClass']) || ($Level == 0 && $IsFLS)))) {
        // User is trying to view someone else's conversation
        error(403);
    }
    // User is trying to view their own unread conversation, set it to read
    if ($UserID == $LoggedUser['ID'] && $Unread) {
        $DB->query("
			UPDATE staff_pm_conversations
			SET Unread = false
			WHERE ID = $ConvID");
        // Clear cache for user
        $Cache->delete_value("staff_pm_new_$LoggedUser[ID]");
    }

    View::show_header(t('server.staffpm.staff_pm'), 'staffpm,bbcode', 'PageStaffPMViewConv');

    $UserInfo = Users::user_info($UserID);
    $UserStr = Users::format_username($UserID, true, true, true, true);

    $OwnerID = $UserID;
    $OwnerName = $UserInfo['Username'];

?>
    <div class="LayoutBody">
        <div class="BodyHeader">
            <h2 class="BodyHeader-nav"><?= t('server.staffpm.staff_pm') ?> > <?= display_str($Subject) ?></h2>
            <div class="BodyNavLinks">

                <?
                if ($IsStaff) {
                ?>
                    <a href="staffpm.php" class="brackets"><?= t('server.staffpm.view_your_unanswered') ?></a>
                <?
                }
                if ($IsFLS) {
                ?>
                    <a href="staffpm.php?view=unanswered" class="brackets"><?= t('server.staffpm.view_all_unanswered') ?></a>
                    <a href="staffpm.php?view=open" class="brackets"><?= t('server.staffpm.view_unresolved') ?></a>
                    <a href="staffpm.php?view=resolved" class="brackets"><?= t('server.staffpm.view_resolved') ?></a>
                <?
                }
                if ($IsStaff) { ?>
                    <a href="staffpm.php?action=scoreboard" class="brackets"><?= t('server.staffpm.view_scoreboard') ?></a>
                <?
                }
                ?>
            </div>
        </div>
        <div class="BoxList" id="inbox">
            <?
            // Get messages
            $StaffPMs = $DB->query("
		SELECT UserID, SentDate, Message, ID
		FROM staff_pm_messages
		WHERE ConvID = $ConvID");

            while (list($UserID, $SentDate, $Message, $MessageID) = $DB->next_record()) {
                // Set user string
                if ($UserID == $OwnerID) {
                    // User, use prepared string
                    $UserString = $UserStr;
                    $Username = $OwnerName;
                } else {
                    // Staff/FLS
                    if (check_perms('show_staff_username')) {
                        $UserInfo = Users::user_info($UserID);
                        $UserString = Users::format_username($UserID, true, true, true, true);
                        $Username = $UserInfo['Username'];
                    } else {
                        $UserString = $Username = 'Staff';
                    }
                }
            ?>
                <div class="Box" id="post<?= $MessageID ?>">

                    <div class="Box-header">
                        <div class="Box-headerLeft">
                            <div class="Box-headerTitle">
                                <?              /* TODO: the inline style in the <a> tag is an ugly hack. get rid of it. */ ?>
                                <a class="postid" href="staffpm.php?action=viewconv&amp;id=<?= $ConvID ?>#post<?= $MessageID ?>" style="font-weight: normal;">#<?= $MessageID ?></a>
                                <?= $UserString ?>
                            </div>
                            - <?= time_diff($SentDate, 2, true) ?>
                        </div>
                        <div class="Box-headerActions">
                            <? if ($Status != 'Resolved') { ?>
                                <a href="#quickpost" onclick="Quote('<?= $MessageID ?>', '<?= $Username ?>');" class="brackets"><?= t('server.staffpm.quote') ?></a>
                            <?      } ?>
                        </div>
                    </div>
                    <div class="Box-body HtmlText">
                        <?= Text::full_format($Message) ?>
                    </div>
                </div>
                <div align="center" style="display: none;"></div>
            <?
                $DB->set_query_id($StaffPMs);
            }

            // Reply box and buttons
            ?>
            <div id="reply_box" class="BoxList">
                <form class="manage_form" name="staff_messages" action="staffpm.php" method="post" id="messageform">
                    <input type="hidden" name="action" value="takepost" />
                    <input type="hidden" name="convid" value="<?= $ConvID ?>" id="convid" />
                    <?
                    if ($Status != 'Resolved') {
                        $TextPrev = new TEXTAREA_PREVIEW('message', 'quickpost', '', 90, 10, true, true, false, array(), true);
                    }
                    ?>
                    <div class="Form-row">
                        <?
                        if ($Status != 'Resolved') { ?>
                            <input variant="primary" class="Button" type="submit" value="<?= t('server.inbox.send_message') ?>" />
                            <input class="Button" type="button" value="<?= t('server.common.resolve') ?>" onclick="location.href='staffpm.php?action=resolve&amp;id=<?= $ConvID ?>';" />

                            <?
                            // Assign to
                            if ($IsStaff) {
                                // Staff assign dropdown
                            ?>
                                | <select class="Input" id="assign_to" name="assign">
                                    <optgroup class="Select-group" label="User classes">
                                        <?      // FLS "class"
                                        $Selected = ((!$AssignedToUser && $PMLevel == 0) ? ' selected="selected"' : '');
                                        ?>
                                        <option class="Select-option" value="class_0" <?= $Selected ?>><?= t('server.staffpm.first_line_support') ?></option>
                                        <?      // Staff classes
                                        foreach ($ClassLevels as $Class) {
                                            // Create one <option class="Select-option"> for each staff user class
                                            if ($Class['Level'] >= 650) {
                                                $Selected = ((!$AssignedToUser && ($PMLevel == $Class['Level'])) ? ' selected="selected"' : '');
                                        ?>
                                                <option class="Select-option" value="class_<?= $Class['Level'] ?>" <?= $Selected ?>><?= $Class['Name'] ?></option>
                                        <?
                                            }
                                        }
                                        ?>
                                    </optgroup>
                                    <optgroup class="Select-group" label="Staff">
                                        <?      // Staff members
                                        $DB->query(
                                            "
			SELECT
				m.ID,
				m.Username
			FROM permissions AS p
				JOIN users_main AS m ON m.PermissionID = p.ID
			WHERE p.DisplayStaff = '1'
			ORDER BY p.Level DESC, m.Username ASC"
                                        );
                                        while (list($ID, $Name) = $DB->next_record()) {
                                            // Create one <option class="Select-option"> for each staff member
                                            $Selected = (($AssignedToUser == $ID) ? ' selected="selected"' : '');
                                        ?>
                                            <option class="Select-option" value="user_<?= $ID ?>" <?= $Selected ?>><?= $Name ?></option>
                                        <?      } ?>
                                    </optgroup>
                                    <optgroup class="Select-group" label="First Line Support">
                                        <?
                                        // FLS users
                                        $DB->query("
			SELECT
				m.ID,
				m.Username
			FROM users_info AS i
				JOIN users_main AS m ON m.ID = i.UserID
				JOIN permissions AS p ON p.ID = m.PermissionID
			WHERE p.DisplayStaff != '1'
				AND i.SupportFor != ''
			ORDER BY m.Username ASC
		");
                                        while (list($ID, $Name) = $DB->next_record()) {
                                            // Create one <option class="Select-option"> for each FLS user
                                            $Selected = (($AssignedToUser == $ID) ? ' selected="selected"' : '');
                                        ?>
                                            <option class="Select-option" value="user_<?= $ID ?>" <?= $Selected ?>><?= $Name ?></option>
                                        <?      } ?>
                                    </optgroup>
                                </select>
                                <input class="Button" type="button" onclick="Assign();" value="<?= t('server.staffpm.assign') ?>" />

                            <?  } elseif ($IsFLS) { /* FLS assign button */ ?>
                                <input class="Button" type="button" value="Assign to staff" onclick="location.href='staffpm.php?action=assign&amp;to=staff&amp;convid=<?= $ConvID ?>';" />
                                <input class="Button" type="button" value="Assign to forum staff" onclick="location.href='staffpm.php?action=assign&amp;to=forum&amp;convid=<?= $ConvID ?>';" />
                            <?
                            }


                            ?>
                        <?  } else { ?>
                            <input class="Button" type="button" value="<?= t('server.staffpm.unresolved') ?>" onclick="location.href='staffpm.php?action=unresolve&amp;id=<?= $ConvID ?>';" />
                        <?
                        }
                        ?>
                    </div>

                    <?

                    if ($IsStaff) {
                    ?>
                        <div id="ajax_message" class="Form-row hidden">
                        </div>
                    <?
                    }
                    ?>

                </form>
                <? if ($IsFLS  && $Status != 'Resolved') { /* Moved by request */ ?>
                    <div class="Form-rowList" variant="header">
                        <div class="Form-rowHeader">
                            <?= t('server.staffpm.common_answers') ?>
                            <div class="Form-actions">
                                <a href="#" class="brackets" onclick="location.href='staffpm.php?action=responses&amp;convid=<?= $ConvID ?>'"><?= t('server.staffpm.create_edit_message') ?></a>
                            </div>
                        </div>
                        <div class=" Form-row">
                            <div class="Form-label">
                                <?= t('server.staffpm.preview') ?>:
                            </div>
                            <div class="Form-items">
                                <div id="common_answers_body" class="Box-body"><i><?= t('server.staffpm.select_an_answer_from_the_drop_down_to_view_it') ?></i></div>
                            </div>
                        </div>
                        <div class="Form-row FormOneLine">
                            <select class="Input" id="common_answers_select" onchange="UpdateMessage();">
                                <option id="first_common_response"><?= t('server.staffpm.select_a_message') ?></option>
                                <?
                                // List common responses
                                $DB->query("
			SELECT ID, Name
			FROM staff_pm_responses
			ORDER BY Name ASC");
                                while (list($ID, $Name) = $DB->next_record()) {
                                ?>
                                    <option class="Select-option" value="<?= $ID ?>"><?= $Name ?></option>
                                <?      } ?>
                            </select>
                            <input class="Button" type="button" value="<?= t('server.staffpm.set_message') ?>" onclick="SetMessage();" />
                        </div>
                    </div>
                <?      } ?>

                <? if (check_perms('users_give_donor') && $Status != 'Resolved') { ?>
                    <div id="make_donor_form">
                        <form action="staffpm.php" method="post">
                            <input type="hidden" name="action" value="make_donor" />
                            <input type="hidden" name="auth" value="<?= $LoggedUser['AuthKey'] ?>" />
                            <input type="hidden" name="id" value="<?= $ConvID ?>" />
                            <div class="Form-rowList" variant="header">
                                <div class="Form-rowHeader">
                                    <?= t('server.staffpm.make_donate') ?>
                                </div>
                                <div class="Form-row">
                                    <div class="Form-label"><?= t('server.staffpm.amount') ?>: </div>
                                    <div class="Form-inputs"><input class="Input" type="text" name="donation_amount" onkeypress="return isNumberKey(event);" /></div>
                                </div>
                                <div class="Form-row">
                                    <div class="Form-label"><?= t('server.staffpm.reason') ?>: </div>
                                    <div class="Form-inputs"><input class="Input" type="text" name="donation_reason" /></div>
                                </div>
                                <div class="Form-row">
                                    <select class="Input" name="donation_currency">
                                        <option class="Select-option" value="CNY"><?= t('server.user.cny') ?></option>
                                    </select>
                                    <input class="Button" type="submit" value="<?= t('server.common.submit') ?>" />
                                </div>
                            </div>
                        </form>
                    </div>
                <?  } ?>
            </div>
        </div>
    </div>
<?

    View::show_footer();
} else {
    // No ID
    header('Location: staffpm.php');
}
