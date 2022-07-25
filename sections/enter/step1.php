<?
View::show_header(t('server.register.register'), '', 'PageEnterStep1');
echo $Val->GenerateJS('registerform');
?>
<script src="<?= CONFIG['STATIC_SERVER'] ?>functions/validate.js" type="text/javascript"></script>
<script src="<?= CONFIG['STATIC_SERVER'] ?>functions/password_validate.js" type="text/javascript"></script>
<form class="create_form" name="user" id="registerform" method="post" action="" onsubmit="return formVal();">
    <div style="width: 500px;">
        <input type="hidden" name="auth" value="<?= $LoggedUser['AuthKey'] ?>" />
        <?

        if (empty($Sent)) {
            if (!empty($_REQUEST['invite'])) {
                echo '<input type="hidden" name="invite" value="' . display_str($_REQUEST['invite']) . '" />' . "\n";
            }
            if (!empty($Err)) {
        ?>
                <strong class="u-colorWarning"><?= $Err ?></strong><br /><br />
            <?  } ?>
            <table class="layout" cellpadding="2" cellspacing="1" border="0" align="center">
                <tr valign="top">
                    <td align="right" style="width: 100px;"><?= t('server.register.username') ?></td>
                    <td align="left">
                        <input class="Input" type="text" name="username" id="username" placeholder="Username" value="<?= (!empty($_REQUEST['username']) ? display_str($_REQUEST['username']) : '') ?>" />
                        <p class="note"><?= t('server.register.username_note') ?></p>
                    </td>
                </tr>
                <tr valign="top">
                    <td align="right"><?= t('server.register.email') ?></td>
                    <td align="left">
                        <input class="Input" type="email" name="email" id="email" placeholder="Email" value="<?= (!empty($_REQUEST['email']) ? display_str($_REQUEST['email']) : (!empty($InviteEmail) ? display_str($InviteEmail) : '')) ?>" />
                    </td>
                </tr>
                <tr valign="top">
                    <td align="right"><?= t('server.register.password') ?></td>
                    <td align="left">
                        <input class="Input" type="password" name="password" id="new_pass_1" placeholder="Password" /> <strong id="pass_strength"></strong>
                    </td>
                </tr>
                <tr valign="top">
                    <td align="right"><?= t('server.register.re_password') ?></td>
                    <td align="left">
                        <input class="Input" type="password" name="confirm_password" id="new_pass_2" placeholder="Verify password" /> <strong id="pass_match"></strong>
                        <p class="note"><?= t('server.register.password_note') ?></p>
                    </td>
                </tr>
                <tr valign="top">
                    <td></td>
                    <td align="left">
                        <div class="Checkbox">
                            <input class="Input" type="checkbox" name="readrules" id="readrules" value="1" <? if (!empty($_REQUEST['readrules'])) { ?> checked="checked" <? } ?> />
                            <label class="Checkbox-label" for="readrules"><?= t('server.register.readrules') ?></label>
                        </div>
                    </td>
                </tr>
                <tr valign="top">
                    <td></td>
                    <td align="left">
                        <div class="Checkbox">
                            <input class="Input" type="checkbox" name="readwiki" id="readwiki" value="1" <? if (!empty($_REQUEST['readwiki'])) { ?> checked="checked" <? } ?> />
                            <label class="Checkbox-label" for="readwiki"><?= t('server.register.readwiki') ?></label>
                        </div>
                    </td>
                </tr>
                <tr valign="top">
                    <td></td>
                    <td align="left">
                        <div class="Checkbox">
                            <input class="Input" type="checkbox" name="agereq" id="agereq" value="1" <? if (!empty($_REQUEST['agereq'])) { ?> checked="checked" <? } ?> />
                            <label class="Checkbox-label" for="agereq"><?= t('server.register.agereq') ?></label>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td colspan="2" height="10"></td>
                </tr>
                <tr>
                    <td colspan="2" align="right"><input class="Button" type="submit" name="submit" value="<?= t('server.register.register') ?>" class="submit" /></td>
                </tr>
            </table>
        <? } else { ?>
            <p sytle="margin-top: 2.5rem;"><?= t('server.register.send_note') ?></p>

        <? if ($NewInstall) {
                echo t('server.register.send_note');
            }
        } ?>
    </div>
</form>
<?
View::show_footer();
