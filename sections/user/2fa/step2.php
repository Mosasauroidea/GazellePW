<?
View::show_header(t('server.user.two_factor_authentication'), '', 'Pageuser2FAStep2');
?>
<div class="LayoutBody">
    <div class="BodyHeader">
        <div class="BodyHeader-nav">
            <?= t('server.user.two_factor_authentication') ?>
        </div>
    </div>
    <form class="Form" method="post">
        <table id="user_2fa_step2_table" class="Form-rowList" variant="header">
            <tr class="Form-rowHeader">
                <td>
                    <div><?= t('server.user.authentication_key') ?></div>
                </td>
            </tr>
            <tr class="Form-row">
                <td>
                    <p><?= t('server.user.two_factor_authentication_note') ?></p>
                </td>
            </tr>
            <tr class="Form-row">
                <td class="Form-label"><?= t('server.user.authentication_key') ?>:
                </td>
                <td class="Form-inputs">
                    <input placeholder="<?= t('server.user.authentication_key_title') ?>" class="Input" type="text" size="50" name="2fa" id="2fa" />
                </td>
            </tr>

            <tr class="Form-row">
                <td colspan="2">
                    <input class="Button" type="submit">
                </td>
            </tr>
        </table>
    </form>
</div>

<? View::show_footer(); ?>