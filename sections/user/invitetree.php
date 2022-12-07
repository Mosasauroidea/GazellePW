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

View::show_header(t('server.user.tree'), '', 'PageUserInviteTree');
?>
<div class="LayoutBody">
    <div class="BodyHeader">
        <h2 class="BodyHeader-nav"><?= t('server.user.tree') ?></h2>
    </div>
    <? $Tree->make_tree(); ?>
</div>
<? View::show_footer(); ?>