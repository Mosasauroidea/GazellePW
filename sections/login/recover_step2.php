<?
View::show_header(t('server.login.recovery'), '', 'PageLoginRecoveryStep2');
echo $Validate->GenerateJS('recoverform');
?>
<script src="<?= (CONFIG['STATIC_SERVER']) ?>functions/validate.js" type="text/javascript"></script>
<script src="<?= (CONFIG['STATIC_SERVER']) ?>functions/password_validate.js" type="text/javascript"></script>
<form class="auth_form" name="recovery" id="recoverform" method="post" action="" onsubmit="return formVal();">
    <input type="hidden" name="key" value="<?= display_str($_REQUEST['key']) ?>" />
    <div style="width: 500px;">
        <span class="titletext"><?= t('server.login.recovery_2') ?></span><br /><br />
        <?
        if (empty($Reset)) {
            if (!empty($Err)) {
        ?>
                <strong class="u-colorWarning"><?= display_str($Err) ?></strong><br /><br />
                <?  } ?><?= t('server.login.recovery_2_note') ?>
                <table class="layout" cellpadding="2" cellspacing="1" border="0" align="center" width="100%">
                    <tr valign="top">
                        <td align="right" style="width: 100px;"><?= t('server.login.new_password') ?></td>
                        <td align="left"><input class="Input" type="password" name="password" id="new_pass_1" /> <strong id="pass_strength"></strong></td>
                    </tr>
                    <tr valign="top">
                        <td align="right"><?= t('server.login.re_new_password') ?></td>
                        <td align="left"><input class="Input" type="password" name="verifypassword" id="new_pass_2" /> <strong id="pass_match"></strong></td>
                    </tr>
                    <tr>
                        <td colspan="2" align="right">
                            <input class="Button" type="submit" name="reset" value="Reset!" />
                        </td>
                    </tr>
                </table>
            <? } else { ?>
                <?= t('server.login.password_send') ?>
            <? } ?>
    </div>
</form>
<?
View::show_footer(['recover' => true]);
?>