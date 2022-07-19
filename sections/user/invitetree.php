<?
if (isset($_GET['userid']) && check_perms('users_view_invites')) {
    if (!is_number($_GET['userid'])) {
        error(403);
    }

    $UserID = $_GET['userid'];
    $Sneaky = true;
} else {
    if (!$UserCount = $Cache->get_value('stats_user_count')) {
        $DB->query("
			SELECT COUNT(ID)
			FROM users_main
			WHERE Enabled = '1'");
        list($UserCount) = $DB->next_record();
        $Cache->cache_value('stats_user_count', $UserCount, 0);
    }

    $UserID = $LoggedUser['ID'];
    $Sneaky = false;
}

list($UserID, $Username, $PermissionID) = array_values(Users::user_info($UserID));

include(CONFIG['SERVER_ROOT'] . '/classes/invite_tree.class.php');
$Tree = new INVITE_TREE($UserID);

View::show_header($Username . Lang::get('user', 'username_invites_tree'), '', 'PageUserInviteTree');
?>
<div class="LayoutBody">
    <div class="BodyHeader">
        <h2 class="BodyHeader-nav"><?= Users::format_username($UserID, false, false, false) ?> &gt; <a href="user.php?action=invite&amp;userid=<?= $UserID ?>"><?= Lang::get('user', 'invites') ?></a> &gt; <?= Lang::get('user', 'tree') ?></h2>
    </div>
    <div class="BoxBody">
        <? $Tree->make_tree(); ?>
    </div>
</div>
<? View::show_footer(); ?>