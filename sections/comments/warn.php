<?php
if (!check_perms('users_warn')) {
    error(404);
}
Misc::assert_isset_request($_POST, array('postid'));

$PostID = (int)$_POST['postid'];
$DB->query("
	SELECT Body, AuthorID
	FROM comments
	WHERE ID = $PostID");
if (!$DB->has_results()) {
    error(404);
}
list($PostBody, $AuthorID) = $DB->next_record();
$UserInfo = Users::user_info($AuthorID);

View::show_header(t('server.forums.comments'), '', 'PageCommentWarn');
?>

<div class="LayoutBody">
    <div class="BodyHeader">
        <h2 class="BodyHeader-nav"><?= t('server.forums.comments') ?></h2>
    </div>

    <form class="Form create_form" name="warning" action="" onsubmit="quickpostform.submit_button.disabled=true;" method="post">
        <input type="hidden" name="postid" value="<?= $PostID ?>" />
        <input type="hidden" name="action" value="take_warn" />
        <table class="Form-rowList" variant="header">
            <tr class="Form-rowHeader">
                <td>
                    <?= t('server.forums.warn') ?> <a href="user.php?id=<?= $UserID ?>"><?= $UserInfo['Username'] ?></a>
                </td>
            </tr>
            <tr class="Form-row">
                <td class="Form-label"><?= t('server.common.reason') ?>:</td>
                <td class="Form-inputs">
                    <input class="Input" type="text" name="reason" size="60" />
                </td>
            </tr>
            <tr class="Form-row">
                <td class="Form-label"><?= t('server.common.time_length') ?>:</td>
                <td class="Form-inputs">
                    <select class="Input" name="length">
                        <option class="Select-option" value="verbal"><?= t('server.forums.verbal') ?></option>
                        <option class="Select-option" value="1"><?= t('server.user.1_week') ?></option>
                        <option class="Select-option" value="2"><?= t('server.user.2_week') ?></option>
                        <option class="Select-option" value="4"><?= t('server.user.4_week') ?></option>
                        <? if (check_perms('users_mod')) { ?>
                            <option class="Select-option" value="8"><?= t('server.user.8_week') ?></option>
                        <?                  } ?>
                    </select>
                </td>
            </tr>
            <tr class="Form-row">
                <td class="Form-label"><?= t('server.user.pm') ?></td>
                <td class="Form-items">
                    <? new TEXTAREA_PREVIEW("privatemessage", "message") ?>
                </td>
            </tr>
            <tr class="Form-row">
                <td class="Form-label"><?= t('server.forums.edit_post') ?>:</td>
                <td class="Form-items">
                    <? new TEXTAREA_PREVIEW("body", "body", $PostBody) ?>
                </td>
            </tr>
            <tr class="Form-row">
                <td>
                    <button class="Button" type="submit" id="submit_button" value="Warn user" tabindex="1"><?= t('server.common.submit') ?></button>
                </td>
            </tr>
        </table>
    </form>
</div>
<? View::show_footer();
