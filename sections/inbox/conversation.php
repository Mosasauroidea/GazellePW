<?
$ConvID = $_GET['id'];
if (!$ConvID || !is_number($ConvID)) {
    error(404);
}


$UserID = $LoggedUser['ID'];
$DB->query("
	SELECT InInbox, InSentbox
	FROM pm_conversations_users
	WHERE UserID = '$UserID'
		AND ConvID = '$ConvID'");
if (!$DB->has_results()) {
    error(403);
}
list($InInbox, $InSentbox) = $DB->next_record();



if (!$InInbox && !$InSentbox) {

    error(404);
}

// Get information on the conversation
$DB->query("
	SELECT
		c.Subject,
		cu.Sticky,
		cu.UnRead,
		cu.ForwardedTo
	FROM pm_conversations AS c
		JOIN pm_conversations_users AS cu ON c.ID = cu.ConvID
	WHERE c.ID = '$ConvID'
		AND UserID = '$UserID'");
list($Subject, $Sticky, $UnRead, $ForwardedID) = $DB->next_record();


$DB->query("
	SELECT um.ID, Username
	FROM pm_messages AS pm
		JOIN users_main AS um ON um.ID = pm.SenderID
	WHERE pm.ConvID = '$ConvID'");

$ConverstionParticipants = $DB->to_array();

foreach ($ConverstionParticipants as $Participant) {
    $PMUserID = (int)$Participant['ID'];
    $Users[$PMUserID]['UserStr'] = Users::format_username($PMUserID, true, true, true, true);
    $Users[$PMUserID]['Username'] = $Participant['Username'];
}

$Users[0]['UserStr'] = 'System'; // in case it's a message from the system
$Users[0]['Username'] = 'System';


if ($UnRead == '1') {

    $DB->query("
		UPDATE pm_conversations_users
		SET UnRead = '0'
		WHERE ConvID = '$ConvID'
			AND UserID = '$UserID'");
    // Clear the caches of the inbox and sentbox
    $Cache->decrement("inbox_new_$UserID");
}

View::show_header(t('server.inbox.view_conversation_space') . "$Subject", 'comments,inbox,bbcode,jquery.validate,form_validate', 'PageInboxConversation');

// Get messages
$DB->query("
	SELECT SentDate, SenderID, Body, ID
	FROM pm_messages
	WHERE ConvID = '$ConvID'
	ORDER BY ID");
?>
<div class="LayoutBody">
    <div class="BodyHeader">
        <div class="BodyHeader-nav"><?= $Subject . ($ForwardedID > 0 ? " (Forwarded to $ForwardedName)" : '') ?></div>
        <div class="BodyNavLinks">
            <a href="<?= Inbox::get_inbox_link(); ?>" class="brackets"><?= t('server.inbox.back_to_inbox') ?></a>
        </div>
    </div>
    <?

    while (list($SentDate, $SenderID, $Body, $MessageID) = $DB->next_record()) { ?>
        <div class="Box">
            <div class="Box-header" style="overflow: hidden;">
                <div class="Box-headerLeft">
                    <div class="Box-headerTitle">
                        <?= $Users[(int)$SenderID]['UserStr'] ?>
                    </div>
                    - <?= time_diff($SentDate) ?>
                </div>
                <div class="Box-headerActions">
                    <? if ($SenderID > 0) { ?>
                        <a href="#quickpost" onclick="Quote('<?= $MessageID ?>','<?= $Users[(int)$SenderID]['Username'] ?>');" class="brackets"><?= t('server.inbox.quote') ?></a>
                    <?  } ?>
                </div>
            </div>
            <div class="Box-body HtmlText PostArticle" id="message<?= $MessageID ?>">
                <?= Text::full_format($Body) ?>
            </div>
        </div>
    <?
    }
    $DB->query("
	SELECT UserID
	FROM pm_conversations_users
	WHERE UserID != '$LoggedUser[ID]'
		AND ConvID = '$ConvID'
		AND (ForwardedTo = 0 OR ForwardedTo = UserID)");
    $ReceiverIDs = $DB->collect('UserID');



    if (!empty($ReceiverIDs) && (empty($LoggedUser['DisablePM']) || array_intersect($ReceiverIDs, array_keys($StaffIDs)))) {
    ?>
        <form class="send_form" name="reply" action="inbox.php" method="post" id="messageform">
            <input type="hidden" name="action" value="takecompose" />
            <input type="hidden" name="auth" value="<?= $LoggedUser['AuthKey'] ?>" />
            <input type="hidden" name="toid" value="<?= implode(',', $ReceiverIDs) ?>" />
            <input type="hidden" name="convid" value="<?= $ConvID ?>" />
            <div id="preview" class="box vertical_space body hidden"></div>
            <? new TEXTAREA_PREVIEW('body', 'quickpost', '', 60, 8, true, true, false); ?>
            <div id="buttons" class="Form-row FormOneLine">
                <input variant="primary" class="Button" type="submit" value="<?= t('server.inbox.send_message') ?>" />
            </div>
        </form>
    <?
    }
    ?>
    <form class="manage_form Form-row" name="messages" action="inbox.php" method="post">
        <input type="hidden" name="action" value="takeedit" />
        <input type="hidden" name="convid" value="<?= $ConvID ?>" />
        <input type="hidden" name="auth" value="<?= $LoggedUser['AuthKey'] ?>" />

        <div class="label">
            <input type="checkbox" id="sticky" name="sticky" <? if ($Sticky) {
                                                                    echo ' checked="checked"';
                                                                } ?> />
            <label for="sticky"><?= t('server.inbox.sticky') ?></label>
        </div>
        <div class="label">
            <input type="checkbox" id="mark_unread" name="mark_unread" />
            <label for="mark_unread"><?= t('server.inbox.mark_as_unread') ?></label>
        </div>
        <div class="label">
            <input type="checkbox" id="delete" name="delete" />
            <label for="delete"><?= t('server.inbox.delete_conversation') ?></label>
        </div>
        <div class="center" colspan="6"><button class="Button" type="submit" value="Manage conversation"><?= t('server.inbox.manage_conversation') ?></button></div>
    </form>

    <?
    $DB->query("
	SELECT SupportFor
	FROM users_info
	WHERE UserID = " . $LoggedUser['ID']);
    list($FLS) = $DB->next_record();
    if ((check_perms('users_mod') || $FLS != '') && (!$ForwardedID || $ForwardedID == $LoggedUser['ID'])) {
    ?>
        <form class="send_form Form-row" name="forward" action="inbox.php" method="post">
            <input type="hidden" name="action" value="forward" />
            <input type="hidden" name="convid" value="<?= $ConvID ?>" />
            <input type="hidden" name="auth" value="<?= $LoggedUser['AuthKey'] ?>" />
            <label for="receiverid"><?= t('server.inbox.forward_to') ?></label>
            <select class="Input" id="receiverid" name="receiverid">
                <?
                foreach ($StaffIDs as $StaffID => $StaffName) {
                    if ($StaffID == $LoggedUser['ID'] || in_array($StaffID, $ReceiverIDs)) {
                        continue;
                    }
                ?>
                    <option class="Select-option" value="<?= $StaffID ?>"><?= $StaffName ?></option>
                <?
                }
                ?>
            </select>
            <button class="Button" type="submit" value="Forward"><?= t('server.inbox.forward_conversation') ?></button>
        </form>
    <?
    }

    //And we're done!
    ?>
</div>
<?
View::show_footer();
?>