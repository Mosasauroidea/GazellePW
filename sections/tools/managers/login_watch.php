<?
if (!check_perms('admin_login_watch')) {
    error(403);
}

if (isset($_POST['submit']) && isset($_POST['id']) && $_POST['submit'] == 'Unban' && is_number($_POST['id'])) {
    authorize();
    $DB->query('
		DELETE FROM login_attempts
		WHERE ID = ' . $_POST['id']);
}

View::show_header(t('server.tools.login_watch'));
?>
<div class="LayoutBody">
    <div class="BodyHeader">
        <h2 class="BodyHeader-nav"><?= t('server.tools.login_watch') ?></h2>
    </div>
    <?

    $DB->query('
	SELECT
		ID,
		IP,
		UserID,
		LastAttempt,
		Attempts,
		BannedUntil,
		Bans
	FROM login_attempts
	WHERE BannedUntil > "' . sqltime() . '"
	ORDER BY BannedUntil ASC');
    if (G::$DB->record_count() == 0) {
        View::line(t('server.common.no_results'));
    } else {
    ?>
        <table class="Table">
            <tr class="Table-rowHeader">
                <td class="Table-cell">IP</td>
                <td class="Table-cell"><?= t('server.tools.user') ?></td>
                <td class="Table-cell"><?= t('server.tools.bans') ?></td>
                <td class="Table-cell"><?= t('server.tools.remaining') ?></td>
                <td class="Table-cell"><?= t('server.tools.submit') ?></td>
            </tr>
            <?
            while (list($ID, $IP, $UserID, $LastAttempt, $Attempts, $BannedUntil, $Bans) = $DB->next_record()) {
            ?>
                <tr class="Table-row">
                    <td class="Table-cell">
                        <?= $IP ?>
                    </td>
                    <td class="Table-cell">
                        <? if ($UserID != 0) {
                            echo Users::format_username($UserID, true, true, true, true);
                        } ?>
                    </td>
                    <td class="Table-cell">
                        <?= $Bans ?>
                    </td>
                    <td class="Table-cell">
                        <?= time_diff($BannedUntil) ?>
                    </td>
                    <td class="Table-cell">
                        <form class="manage_form" name="bans" action="" method="post" style="display:inline-block">
                            <input type="hidden" name="auth" value="<?= $LoggedUser['AuthKey'] ?>" />
                            <input type="hidden" name="id" value="<?= $ID ?>" />
                            <input type="hidden" name="action" value="login_watch" />
                            <button class="Button" type="submit" name="submit" value="Unban"><?= t('server.tools.unban') ?></button>
                        </form>
                        <? if (check_perms('admin_manage_ipbans')) { ?>
                            <form class="manage_form" name="bans" action="" method="post" style="display:inline-block">
                                <input type="hidden" name="auth" value="<?= $LoggedUser['AuthKey'] ?>" />
                                <input type="hidden" name="id" value="<?= $ID ?>" />
                                <input type="hidden" name="action" value="ip_ban" />
                                <input type="hidden" name="start" value="<?= $IP ?>" />
                                <input type="hidden" name="end" value="<?= $IP ?>" />
                                <input type="hidden" name="notes" value="Banned per <?= $Bans ?> bans on login watch." />
                                <button class="Button" type="submit" name="submit" value="IP Ban"><?= t('server.tools.ban_ip') ?></button>
                            </form>
                    </td>
                <? } ?>
                </tr>
            <?
            }
            ?>
        </table>
    <?
    }
    ?>
</div>
<? View::show_footer(); ?>