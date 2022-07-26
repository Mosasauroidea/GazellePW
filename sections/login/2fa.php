<? View::show_header(t('server.login.two_factor_authentication'), '', 'PageLogin2FA'); ?>
<span id="no-cookies" class="hidden u-colorWarning"><?= t('server.login.cookies_disabled') ?><br /><br /></span>
<noscript><span class="u-colorWarning"><?= CONFIG['SITE_NAME'] ?><?= t('server.login.requires_js') ?></span><br /><br />
</noscript>
<?
if (strtotime($BannedUntil) < time()) {
?>
    <form class="auth_form" name="login" id="loginform" method="post" action="login.php?act=2fa">
        <?

        if (!empty($BannedUntil) && $BannedUntil != '0000-00-00 00:00:00') {
            $DB->query("
			UPDATE login_attempts
			SET BannedUntil = '0000-00-00 00:00:00', Attempts = '0'
			WHERE ID = '" . db_string($AttemptID) . "'");
            $Attempts = 0;
        }
        if (isset($Err)) {
        ?>
            <span class="u-colorWarning"><?= $Err ?><br /><br /></span>
        <? } ?>
        <? if ($Attempts > 0) { ?>
            <? $RemainingAttempts = 6 - $Attempts; ?>
            <?= t('server.login.attempts', ['Values' => [
                "<span class='info'>${RemainingAttempts}</span>"
            ]]) ?>
            <br />
        <? } ?>
        <table class="layout">
            <tr>
                <td><?= t('server.login.2fa_key') ?></td>
                <td colspan="2">
                    <input class="Input" type="text" name="2fa" id="2fa" required="required" maxlength="6" pattern="[0-9]{6}" autofocus="autofocus" placeholder="<?= t('server.login.two_factor_auth_key') ?>" />
                </td>
            </tr>

            <tr>
                <td></td>
                <td><input class="Button" type="submit" name="login" value="Log in" /></td>
            </tr>
        </table>
    </form>
    <br /><br />
    <a href="login.php?act=2fa_recovery" data-tooltip="<?= t('server.login.use_2fa_recovery_code') ?>"><?= t('server.login.use_recovery_key') ?></a>
<?
} else {
?>
    <span class="u-colorWarning">
        <?= t('server.login.you_are_banned_from_logging', ['Values' => [
            time_diff($BannedUntil)
        ]]) ?>
    </span>
<?
}

?>
<script type="text/javascript">
    cookie.set('cookie_test', 1, 1);
    if (cookie.get('cookie_test') != null) {
        cookie.del('cookie_test');
    } else {
        $('#no-cookies').gshow();
    }
</script>
<? View::show_footer(); ?>