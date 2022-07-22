<?

if (empty($Return)) {
    $ToID = $_GET['to'];
    if ($ToID == $LoggedUser['ID']) {
        error(Lang::get('inbox.you_cannot_start_a_conversation_with_yourself'));
        header('Location: ' . Inbox::get_inbox_link());
    }
}

if (!$ToID || !is_number($ToID)) {
    error(404);
}

if (!empty($LoggedUser['DisablePM']) && !isset($StaffIDs[$ToID])) {
    error(403);
}

$DB->query("
	SELECT Username
	FROM users_main
	WHERE ID='$ToID'");
list($Username) = $DB->next_record();
if (!$Username) {
    error(404);
}
View::show_header(Lang::get('inbox.compose'), 'inbox,bbcode,jquery.validate,form_validate', 'PageInboxCompose');
?>
<div class="LayoutBody">
    <div class="BodyHeader">
        <h2 class="BodyHeader-nav"><?= Lang::get('inbox.send_a_message_to_user_before') ?><a href="user.php?id=<?= $ToID ?>"><?= $Username ?></a><?= Lang::get('inbox.send_a_message_to_user_after') ?></h2>
    </div>
    <form class="Box send_form" name="message" action="inbox.php" method="post" id="messageform">
        <div class="Box-body">
            <input type="hidden" name="action" value="takecompose" />
            <input type="hidden" name="toid" value="<?= $ToID ?>" />
            <input type="hidden" name="auth" value="<?= $LoggedUser['AuthKey'] ?>" />
            <div class="Box" id="quickpost">
                <div class="Box-body">
                    <h3><?= Lang::get('inbox.subject') ?></h3>
                    <input class="Input required" type="text" name="subject" size="95" value="<?= (!empty($Subject) ? $Subject : '') ?>" />
                    <h3><?= Lang::get('inbox.body') ?></h3>
                    <? new TEXTAREA_PREVIEW('body', 'body', $Body, 60, 8); ?>
                    <div id="buttons" class="Post-bodyActions">
                        <input class="Button" type="submit" value="<?= Lang::get('inbox.send_message') ?>" />
                    </div>
                </div>

            </div>
        </div>
    </form>
</div>
<?
View::show_footer();
?>