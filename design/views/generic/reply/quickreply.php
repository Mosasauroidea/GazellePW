<?php

/**
 * This version of #quickpostform is used in all subsections
 * Instead of modifying multiple places, just modify this one.
 *
 * To include it in a section use this example.

        View::parse('generic/reply/quickreply.php', array(
            'InputTitle' => 'Post reply',
            'InputName' => 'thread',
            'InputID' => $ThreadID,
            'ForumID' => $ForumID,
            'TextareaCols' => 90
        ));

 * Note that InputName and InputID are the only required variables
 * They're used to construct the $_POST.
 *
 * Eg
 * <input name="thread" value="123" />
 * <input name="groupid" value="321" />
 *
 * Globals are required as this template is included within a
 * function scope.
 *
 * To add a "Subscribe" box for non-forum pages (like artist/collage/...
 * comments), add a key 'SubscribeBox' to the array passed to View::parse.
 * Example:

        View::parse('generic/reply/quickreply.php', array(
            'InputTitle' => 'Post comment',
            'InputName' => 'groupid',
            'InputID' => $GroupID,
            'TextareaCols' => 65,
            'SubscribeBox' => true
        ));
 */
global $HeavyInfo, $UserSubscriptions, $ThreadInfo, $Document;

if (G::$LoggedUser['DisablePosting']) {
    return;
}
if (!isset($TextareaCols)) {
    $TextareaCols = 70;
}
if (!isset($TextareaRows)) {
    $TextareaRows = 8;
}
if (!isset($InputAction)) {
    $InputAction = 'reply';
}
if (!isset($InputTitle)) {
    $InputTitle = t('server.forums.post_comment');
}
if (!isset($Action)) {
    $Action = '';
}
// TODO: Remove inline styles

// Old to do?
// TODO: Preview, come up with a standard, make it look like post or just a
// block of formatted BBcode, but decide and write some proper XHTML


$ReplyText = new TEXTAREA_PREVIEW(
    'body',
    'quickpost',
    '',
    $TextareaCols,
    $TextareaRows,
    true,
    true,
    true,
    array(
        'tabindex="1"',
        'onkeyup="resize(\'quickpost\')"'
    )
);
?>

<div id="reply_box">
    <div>
        <form class="send_form center" name="reply" id="quickpostform" action="<?= $Action ?>" method="post" <? if (!check_perms('users_mod')) { ?> onsubmit="quickpostform.submit_button.disabled = true;" <? } ?>>
            <input type="hidden" name="action" value="<?= $InputAction ?>" />
            <input type="hidden" name="auth" value="<?= G::$LoggedUser['AuthKey'] ?>" />
            <input type="hidden" name="<?= $InputName ?>" value="<?= $InputID ?>" />
            <div id="quickreplytext">
                <?
                echo $ReplyText->getBuffer();
                ?>
            </div>
            <div class="Form-row FormOneLine">
                <?
                if (isset($SubscribeBox) && !isset($ForumID) && Subscriptions::has_subscribed_comments($Document, $InputID) === false) {
                ?>
                    <input id="subscribebox" type="checkbox" name="subscribe" <?= !empty($HeavyInfo['AutoSubscribe']) ? ' checked="checked"' : '' ?> tabindex="2" />
                    <label for="subscribebox"><?= t('server.forums.checkbox_subscribe') ?></label>
                    <?
                }
                // Forum thread logic
                // This might use some more abstraction
                if (isset($ForumID)) {
                    if (!Subscriptions::has_subscribed($InputID)) {
                    ?>
                        <input id="subscribebox" type="checkbox" name="subscribe" <?= !empty($HeavyInfo['AutoSubscribe']) ? ' checked="checked"' : '' ?> tabindex="2" />
                        <label for="subscribebox"><?= t('server.forums.checkbox_subscribe') ?></label>
                    <?
                    }

                    if ($ThreadInfo['LastPostAuthorID'] == G::$LoggedUser['ID']) {
                        // Test to see if the post was made an hour ago, if so, auto-check merge box
                        /** @noinspection PhpUnhandledExceptionInspection */
                        $PostDate = date_create($ThreadInfo['LastPostTime'])->add(new DateInterval("PT1H"));
                        $TestDate = date_create();
                        $Checked = ($PostDate >= $TestDate) ? "checked" : "";
                    ?>
                        <input id="mergebox" type="checkbox" name="merge" tabindex="2">
                        <label for="mergebox"><?= t('server.forums.checkbox_merge') ?></label>
                    <?
                    }
                    if (!G::$LoggedUser['DisableAutoSave']) {
                    ?>
                        <script type="application/javascript">
                            var storedTempTextarea = new StoreText('quickpost', 'quickpostform', <?= $InputID ?>);
                        </script>
                <?
                    }
                }
                ?>
                <input class="Button" variant="primary" type="submit" value="<?= t('server.forums.post_reply') ?>" id="submit_button" tabindex="1" />
            </div>
        </form>
    </div>
</div>