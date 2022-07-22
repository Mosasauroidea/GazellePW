<? View::show_header(Lang::get('login.two_factor_authentication'), '', 'PageLogin2FA'); ?>
<span id="no-cookies" class="hidden u-colorWarning"><?= Lang::get('login.cookies_disabled') ?><br /><br /></span>
<noscript><span class="u-colorWarning"><?= CONFIG['SITE_NAME'] ?><?= Lang::get('login.requires_js') ?></span><br /><br />
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
            <?= Lang::get('login.attempts_1') ?><span class="info"><?= (6 - $Attempts) ?></span><?= Lang::get('login.attempts_2') ?><br />
        <? } ?>
        <table class="layout">
            <tr>
                <td><?= Lang::get('login.2fa_key') ?></td>
                <td colspan="2">
                    <input class="Input" type="text" name="2fa" id="2fa" required="required" maxlength="6" pattern="[0-9]{6}" autofocus="autofocus" placeholder="<?= Lang::get('login.two_factor_auth_key') ?>" />
                </td>
            </tr>

            <tr>
                <td></td>
                <td><input class="Button" type="submit" name="login" value="Log in" /></td>
            </tr>
        </table>
    </form>
    <br /><br />
    <a href="login.php?act=2fa_recovery" data-tooltip="<?= Lang::get('login.use_2fa_recovery_code') ?>"><?= Lang::get('login.use_recovery_key') ?></a>
<?
} else {
?>
    <span class="u-colorWarning"><?= Lang::get('login.you_are_banned_from_logging_1') ?><?= time_diff($BannedUntil) ?><?= Lang::get('login.you_are_banned_from_logging_2') ?></span>
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