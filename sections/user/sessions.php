<?

//TODO: restrict to viewing below class, username in h2
if (isset($_GET['userid']) && check_perms('users_view_ips') && check_perms('users_logout')) {
    if (!is_number($_GET['userid'])) {
        error(404);
    }
    $UserID = $_GET['userid'];
} else {
    $UserID = G::$LoggedUser['ID'];
}

if (isset($_POST['all'])) {
    authorize();

    $DB->query("
		DELETE FROM users_sessions
		WHERE UserID = '$UserID'
			AND SessionID != '$SessionID'");
    $Cache->delete_value("users_sessions_$UserID");
}

if (isset($_POST['session'])) {
    authorize();

    $DB->query("
		DELETE FROM users_sessions
		WHERE UserID = '$UserID'
			AND SessionID = '" . db_string($_POST['session']) . "'");
    $Cache->delete_value("users_sessions_$UserID");
}

$UserSessions = $Cache->get_value('users_sessions_' . $UserID);
if (!is_array($UserSessions)) {
    $DB->query("
		SELECT
			SessionID,
			Browser,
			OperatingSystem,
			IP,
			LastUpdate
		FROM users_sessions
		WHERE UserID = '$UserID'
		ORDER BY LastUpdate DESC");
    $UserSessions = $DB->to_array('SessionID', MYSQLI_ASSOC);
    $Cache->cache_value("users_sessions_$UserID", $UserSessions, 0);
}

list($UserID, $Username) = array_values(Users::user_info($UserID));
View::show_header($Username . t('server.user.space_bigger_than_sessions'), '', 'PageUserSession');
?>
<div class="LayoutBody">
    <h2><?= Users::format_username($UserID, $Username) ?><?= t('server.user.space_bigger_than_sessions') ?></h2>
    <div class="BoxBody">
        <p><?= t('server.user.clearing_cookies_can_result_in_ghost_sessions') ?></p>
    </div>

    <table class="TableUserSession Table">
        <tr class="Table-rowHeader">
            <td class="Table-cell"><?= t('server.user.ip_address') ?></td>
            <td class="Table-cell"><?= t('server.user.browser') ?></td>
            <td class="Table-cell"><?= t('server.user.platform') ?></td>
            <td class="Table-cell"><?= t('server.user.last_activity') ?></td>
            <td class="Table-cell">
                <form class="manage_form" name="sessions" action="" method="post">
                    <input type="hidden" name="action" value="sessions" />
                    <input type="hidden" name="auth" value="<?= $LoggedUser['AuthKey'] ?>" />
                    <input type="hidden" name="all" value="1" />
                    <input class="Button" type="submit" value="<?= t('server.user.logout_all') ?>" />
                </form>
            </td>
        </tr>
        <?
        foreach ($UserSessions as $Session) {
        ?>
            <tr class="Table-row">
                <td class="Table-cell"><?= $Session['IP'] ?></td>
                <td class="Table-cell"><?= $Session['Browser'] ?></td>
                <td class="Table-cell"><?= $Session['OperatingSystem'] ?></td>
                <td class="Table-cell"><?= time_diff($Session['LastUpdate']) ?></td>
                <td class="Table-cell">
                    <form class="delete_form" name="session" action="" method="post">
                        <input type="hidden" name="action" value="sessions" />
                        <input type="hidden" name="auth" value="<?= $LoggedUser['AuthKey'] ?>" />
                        <input type="hidden" name="session" value="<?= $Session['SessionID'] ?>" />
                        <buton class="Button" type="submit" value="<?= (($Session['SessionID'] === $SessionID) ? 'Current" disabled="disabled' : 'Log out') ?>"><?= t('server.common.logout') ?></button>
                    </form>
                </td>
            </tr>
        <?  } ?>
    </table>
</div>
<?

View::show_footer();
?>