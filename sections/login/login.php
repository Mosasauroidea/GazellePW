<?

if (CONFIG['CLOSE_LOGIN']) {
    if (isset($_GET['loginkey'])) {
        $CheckKey = checkLoginKey($_GET['loginkey']);
        if (!$CheckKey[0]) {
            header('HTTP/1.1 301 Moved Permanently');
            header('Location: ' . $CONFIG['CLOSE_REDIRECT_URL']);
            return;
        }
    } else {
        header('HTTP/1.1 301 Moved Permanently');
        header('Location: ' . $CONFIG['CLOSE_REDIRECT_URL']);
        return;
    }
}

?>
<? View::show_header(t('server.login.login'), '', 'PageLoginHome'); ?>
<span id="no-cookies" class="hidden warning">你似乎禁用了 Cookies。</span>
<noscript><span class="u-colorWarning"><?= CONFIG['SITE_NAME'] ?>本页面需要启用 JavaScript 才能正常工作，请为你的浏览器启用 JavaScript。</span><br /></noscript>
<?


if (strtotime($BannedUntil) < time()) {
?>
    <form class="auth_form" name="login" id="loginform" method="post" action="login.php">
        <div id="form-body">
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
                <br /><span class="u-colorWarning"><?= $Err ?></span>
            <?
            }
            ?>
            <? if ($Attempts > 0) { ?>
                <br />
                <span>
                    <? $RemainingAttempts = 6 - $Attempts; ?>
                    <?= t('server.login.attempts', ['Values' => [
                        "<span class='info'>${RemainingAttempts}</span>"
                    ]]) ?>
                </span>
                <br />
            <?    } ?>
            <? if (isset($_GET['invalid2fa'])) { ?>
                <span class="u-colorWarning"><?= t('server.login.warning') ?> </span><br />
            <?    } ?>

            <div id="login-table">
                <?
                if (isset($_GET['loginkey']) && preg_match('/[a-zA-Z0-9]{32}/', $_GET['loginkey'])) {
                    echo '<input type="hidden" name="loginkey" value="' . $_GET['loginkey'] . '"/>';
                }
                ?>
                <div class="username-title"><?= t('server.login.username') ?>:</div>
                <div class="username-input">
                    <input class="Input" type="text" name="username" id="username" required="required" maxlength="20" autofocus="autofocus" placeholder="Username" />
                </div>
                <div class="space"></div>

                <div class="password-title"><?= t('server.login.password') ?>:</div>
                <div class="password-input">
                    <input class="Input" type="password" name="password" id="password" required="required" maxlength="100" pattern=".{6,100}" placeholder="Password" />
                </div>
                <div class="space"></div>

                <div class="space"></div>
                <div id="remember-login">
                    <div class="Checkbox">
                        <input class="Input" type="checkbox" id="keeplogged" name="keeplogged" value="1" <?= (isset($_REQUEST['keeplogged']) && $_REQUEST['keeplogged']) ? ' checked="checked"' : '' ?> />
                        <label class="Checkbox-label" for="keeplogged" id="keeplogged-label"><?= t('server.login.remember_me') ?></label>
                    </div>
                    <input class="Button" type="submit" name="login" value="<?= t('server.login.login') ?>" class="submit" id="login-btn" />
                </div>
                <div class="space"></div>

            </div>
    </form>
<?
} else {
?>
    <span class="u-colorWarning">
        <?= t('server.login.warning_disable', ['Values' => [time_diff($BannedUntil)]]) ?>
    </span><br />
<?
}

if ($Attempts > 0) {
?>
    <span id="find-pw"><?= t('server.login.forget_pw') ?> <a href="login.php?act=recover" data-tooltip="Recover your password"><?= t('server.login.find_pw') ?></a></span>
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
    window.onload = function() {
        document.getElementById("username").focus();
    };
</script>
<? View::show_footer();
