<?
View::show_header(t('server.login.recovery'), 'validate', 'PageLoginRecoveryStep1');
echo $Validate->GenerateJS('recoverform');
?>
<form class="auth_form" name="recovery" id="recoverform" method="post" action="" onsubmit="return formVal();">
    <div style="width: 500px;">
        <span class="titletext"><?= t('server.login.recovery_1') ?></span><br />
        <?
        if (empty($Sent) || (!empty($Sent) && $Sent != 1)) {
            if (!empty($Err)) {
        ?>
                <strong class="u-colorWarning"><?= $Err ?></strong><br /><br />
            <?  } ?>
            <?= t('server.login.recovery_note') ?>
            <div id="input-email-address-for-reset">

                <span><?= t('server.login.email') ?>:</span>
                <input class="Input" type="email" name="email" id="email" />
                <input class="Button" type="submit" name="reset" value="Reset!" id="submit-1" />
            </div>
        <?
        } else { ?>
            <?= t('server.login.email_send') ?>
        <?
        } ?>
    </div>
</form>
<?
View::show_footer(['recover' => true]);
?>