<?

enforce_login();
if (!check_perms('site_moderate_forums')) {
    error(403);
}


$ForumID = $_GET['forumid'];
if (!is_number($ForumID)) {
    error(404);
}


if (!empty($_POST['add']) || (!empty($_POST['del']))) {
    if (!empty($_POST['add'])) {
        if (is_number($_POST['new_thread'])) {
            $DB->query("
				INSERT INTO forums_specific_rules (ForumID, ThreadID)
				VALUES ($ForumID, " . $_POST['new_thread'] . ')');
        }
    }
    if (!empty($_POST['del'])) {
        if (is_number($_POST['threadid'])) {
            $DB->query("
				DELETE FROM forums_specific_rules
				WHERE ForumID = $ForumID
					AND ThreadID = " . $_POST['threadid']);
        }
    }
    $Cache->delete_value('forums_list');
    header("Location: forums.php?action=edit_rules&forumid=$ForumID");
}


$DB->query("
	SELECT ThreadID
	FROM forums_specific_rules
	WHERE ForumID = $ForumID");
$ThreadIDs = $DB->collect('ThreadID');

View::show_header('Edit Forum Rule', '', 'PageForumEditRule');
?>
<div class="LayoutBody">
    <div class="BodyHeader">
        <h2 class="BodyHeader-nav">
            <a href="forums.php"><?= t('server.forums.forums') ?></a>
            &gt;
            <a href="forums.php?action=viewforum&amp;forumid=<?= $ForumID ?>"><?= $Forums[$ForumID]['Name'] ?></a>
            &gt;
            <?= t('server.forums.edit_forum_specific_rules') ?>
        </h2>
    </div>
    <form class="Form add_form" name="forum_rules" action="" method="post">
        <input type="hidden" name="auth" value="<?= $LoggedUser['AuthKey'] ?>" />
        <table class="Form-rowList" variant="header">
            <tr class="Form-rowHeader">
                <td>
                    <?= t('server.forums.new_forum_specific_rule') ?>
                </td>
            </tr>
            <tr class="Form-row">
                <td class="Form-label"><?= t('server.forums.thread_id') ?></td>
                <td class="Form-inputs">
                    <input class="Input is-small" type="text" name="new_thread" size="8" />
                </td>
            </tr>
            <tr class="Form-row">
                <td>
                    <button class="Button" type="submit" name="add" value="Add thread"><?= t('server.common.add') ?></button>
                </td>
            </tr>
        </table>
    </form>
    <table class="Table TableEditRule">
        <tr class="Table-rowHeader">
            <td class="Table-cell">
                <?= t('server.forums.thread_id') ?>
            </td>
            <td class="Table-cell">
                <?= t('server.common.actions') ?>
            </td>
        </tr>
        <? foreach ($ThreadIDs as $ThreadID) { ?>
            <tr class="Table-row">
                <td class="Table-cell"><?= $ThreadID ?></td>
                <td class="Table-cell">
                    <form class=" delete_form" name="forum_rules" action="" method="post">
                        <input type="hidden" name="auth" value="<?= $LoggedUser['AuthKey'] ?>" />
                        <input type="hidden" name="threadid" value="<?= $ThreadID ?>" />
                        <button class="Button" type="submit" name="del" value="Delete link"><?= t('server.common.delete') ?></button>
                    </form>
                </td>
            </tr>
        <?  } ?>
    </table>
</div>
<?
View::show_footer();
?>