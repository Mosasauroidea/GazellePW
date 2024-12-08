<?
if (!check_perms('users_mod')) {
    error(403);
}
include(CONFIG['SERVER_ROOT'] . '/sections/user/linkedfunctions.php');


if (!check_perms('users_mod')) {
    error(403);
}

$UserID = (int)$_GET['id'];

if (!is_number($UserID)) {
    error(403);
}
$DB->query("
		SELECT d.ID, d.Comments, SHA1(d.Comments) AS CommentHash
		FROM dupe_groups AS d
			JOIN users_dupes AS u ON u.GroupID = d.ID
		WHERE u.UserID = $UserID");
if (list($GroupID, $Comments, $CommentHash) = $DB->next_record()) {
    $DB->query("
			SELECT m.ID
			FROM users_main AS m
				JOIN users_dupes AS d ON m.ID = d.UserID
			WHERE d.GroupID = $GroupID
			ORDER BY m.ID ASC");
    $DupeCount = $DB->record_count();
    $Dupes = $DB->to_array();
} else {
    $DupeCount = 0;
    $Dupes = array();
}
View::show_header(t('server.user.linked_account'), "jquery, jquery.wookmark,user,bbcode,comments,info_paster,tiles", "PageUserShow");
?>
<div class="LayoutBody">
    <div class="BodyHeader">
        <h2 class="BodyHeader-nav"><?= Users::format_username($UserID, true, true, true, false, true, false, true) ?></h2>
    </div>
    <div class="LayoutMainSidebar">
        <div class="Sidebar LayoutMainSidebar-sidebar">
            <div class="SidebarItemLinkedAccount SidebarItem Box" id="lniked_account">
                <div class="SidebarItem-header Box-header">
                    <strong> <?= t('server.user.linked_account') ?> (<?= max($DupeCount - 1, 0) ?>)</strong>
                </div>
                <ul class="SidebarList SidebarItem-body Box-body">
                    <?
                    $i = 0;
                    foreach ($Dupes as $Dupe) {
                        $i++;
                        list($DupeID) = $Dupe;
                        $DupeInfo = Users::user_info($DupeID);
                        if ($DupeID == $UserID) {
                            continue;
                        }
                    ?>
                        <li class="SidebarList-item">
                            <?= Users::format_username($DupeID, true, true, true, true) ?>
                            <a class="SidebarList-actions" href="user.php?action=dupes&amp;dupeaction=remove&amp;auth=<?= $LoggedUser['AuthKey'] ?>&amp;userid=<?= $UserID ?>&amp;removeid=<?= $DupeID ?>" onclick="return confirm('Are you sure you wish to remove <?= $DupeInfo['Username'] ?> from this group?');" class="brackets" data-tooltip="Remove linked account">X</a>
                        </li>
                    <?
                    }
                    ?>
                </ul>
            </div>
        </div>
        <form class="manage_form" name="user" method="post" id="linkedform" action="">
            <input type="hidden" name="action" value="dupes" />
            <input type="hidden" name="dupeaction" value="update" />
            <input type="hidden" name="userid" value="<?= $UserID ?>" />
            <input type="hidden" id="auth" name="auth" value="<?= $LoggedUser['AuthKey'] ?>" />
            <input type="hidden" id="form_comment_hash" name="form_comment_hash" value="<?= $CommentHash ?>" />
            <div class="Form-rowList" variant="header" id="l_a_box">
                <div class="Form-rowHeader">
                    <?= t('server.common.add') ?>
                </div>
                <div class="Form-row">
                    <div class="Form-label">
                        <label for="target"><?= t('server.user.link_user_with') ?>:</label>
                    </div>
                    <div class="Form-inputs">
                        <input class="Input is-small" type="text" name="target" id="target" />
                    </div>
                </div>
                <div class="Form-row">
                    <div class="Form-items">
                        <div id="editdupecomments" class="<?= ($DupeCount ? 'hidden' : '') ?>">
                            <textarea class="Input" name="dupecomments" onkeyup="resize('dupecommentsbox');" id="dupecommentsbox" cols="65" rows="5"><?= display_str($Comments) ?></textarea>
                        </div>
                    </div>
                </div>
                <div class="Form-row">
                    <input class="Button" type="submit" value="<?= t('server.common.submit') ?>" id="submitlink" />
                </div>
            </div>
        </form>
    </div>
</div>
<? View::show_footer(); ?>