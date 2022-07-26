<?
View::show_header(t('server.user.two_factor_authentication'), '', 'Pageuser2FAStep2');
?>

<div class="BoxBody">
    <p><?= t('server.user.two_factor_authentication_note') ?></p>
</div>

<form method="post">
    <table id="user_2fa_step2_table" class="layout border">
        <thead>
            <tr class="colhead_dark">
                <td colspan="2">
                    <strong><?= t('server.user.please_enter_2fa_key') ?></strong>
                </td>
            </tr>
        </thead>

        <tbody>
            <tr>
                <td class="label" data-tooltip-interactive="<?= t('server.user.authentication_key_title') ?>" data-title-plain="If all went to plan last step, your authentication app should've given you a code. Please enter that here.">
                    <label for="2fa"><strong><?= t('server.user.authentication_key') ?>:</strong></label>
                </td>

                <td>
                    <input class="Input" type="text" size="50" name="2fa" id="2fa" />
                </td>
            </tr>

            <tr>
                <td colspan="2">
                    <input class="Button" type="submit">
                </td>
            </tr>
        </tbody>
    </table>
</form>

<? View::show_footer(); ?>