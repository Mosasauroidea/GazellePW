<?

if (empty($Return)) {
    $ToID = $_GET['to'];
    if ($ToID == $LoggedUser['ID']) {
        error(t('server.inbox.you_cannot_start_a_conversation_with_yourself'));
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
View::show_header(t('server.inbox.compose'), 'inbox,bbcode,jquery.validate,form_validate', 'PageInboxCompose');
?>
<div class="LayoutBody">
    <div class="BodyHeader">
        <h2 class="BodyHeader-nav">
            <?= t('server.inbox.send_a_message_to_user', ['Values' => [
                "<a href='user.php?id=$ToID'>$Username</a>"
            ]]) ?>
        </h2>
    </div>
    <form class="Form send_form" name="message" action="inbox.php" method="post" id="messageform">
        <input type="hidden" name="action" value="takecompose" />
        <input type="hidden" name="toid" value="<?= $ToID ?>" />
        <input type="hidden" name="auth" value="<?= $LoggedUser['AuthKey'] ?>" />
        <div class="Form-rowList" id="quickpost">
            <div class="Form-row">
                <div class="Form-label"><?= t('server.inbox.subject') ?></div>
                <div class="Form-inputs">
                    <input class="Input required" type="text" name="subject" size="95" value="<?= (!empty($Subject) ? $Subject : '') ?>" />
                </div>
            </div>
            <div class="Form-row">
                <div class="Form-label"><?= t('server.inbox.body') ?></div>
                <div class="Form-items">
                    <? new TEXTAREA_PREVIEW('body', 'body', $Body, 60, 8); ?>
                </div>
            </div>
            <div class="Form-row">
                <div id="buttons">
                    <input class="Button" type="submit" value="<?= t('server.inbox.send_message') ?>" />
                </div>
            </div>
        </div>
    </form>
</div>
<?
View::show_footer();
?>