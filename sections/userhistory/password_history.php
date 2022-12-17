<?

/************************************************************************
||------------|| Password reset history page ||------------------------||

This page lists password reset IP and Times a user has made on the site.
It gets called if $_GET['action'] == 'password'.

It also requires $_GET['userid'] in order to get the data for the correct
user.

 ************************************************************************/

$UserID = $_GET['userid'];
if (!is_number($UserID)) {
    error(404);
}

$DB->query("
	SELECT
		um.Username,
		p.Level AS Class
	FROM users_main AS um
		LEFT JOIN permissions AS p ON p.ID = um.PermissionID
	WHERE um.ID = $UserID");
list($Username, $Class) = $DB->next_record();

if (!check_perms('users_view_keys', $Class)) {
    error(403);
}

View::show_header(t('server.userhistory.password_reset_history_for', ['Values' => [$Username]]), '', 'PageUserHistoryPassword');

$DB->query("
	SELECT
		ChangeTime,
		ChangerIP
	FROM users_history_passwords
	WHERE UserID = $UserID
	ORDER BY ChangeTime DESC");

?>
<div class="LayoutBody">
    <div class="BodyHeader">
        <h2 class="BodyHeader-nav">
            <?= t('server.userhistory.password_reset_history_for', ['Values' => [
                "<a href='/user.php?id=${UserID}'>${Username}</a>"
            ]]) ?>
        </h2>
    </div>
    <div class="TableContainer">
        <table class="TableUserPasswordHistory Table">
            <tr class="Table-rowHeader">
                <td class="Table-cell"><?= t('server.userhistory.changed') ?></td>
                <td class="Table-cell">IP <a href="/userhistory.php?action=ips&amp;userid=<?= $UserID ?>" class="brackets">H</a></td>
            </tr>
            <? while (list($ChangeTime, $ChangerIP) = $DB->next_record()) { ?>
                <tr class="Table-row">
                    <td class="Table-cell"><?= time_diff($ChangeTime) ?></td>
                    <td class="Table-cell"><?= display_str($ChangerIP) ?> <a href="/user.php?action=search&amp;ip_history=on&amp;ip=<?= display_str($ChangerIP) ?>" class="brackets" data-tooltip="Search">S</a><br /><?= Tools::get_host_by_ajax($ChangerIP) ?></td>
                </tr>
            <? } ?>
        </table>
    </div>
</div>
<? View::show_footer(); ?>