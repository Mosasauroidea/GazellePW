<?php

$ID = G::$LoggedUser['ID'];
$Label = $_REQUEST['label'];

if (isset($_POST['confirm'])) {
    authorize();

    if ($Bonus->purchaseFreeTorrent($ID, $Label, $_POST['torrent_group_id'], G::$LoggedUser['EffectiveClass'])) {
        header('Location: bonus.php?complete=' . urlencode($Label));
    } else {
        error(t('server.bonus.you_cannot_afford_this_item'));
    }
}

View::show_header(t('server.bonus.bonus_points_title'), 'bonus', 'PageBonusTitle');
?>
<div class="LayoutBody">
    <div class="BodyHeader">
        <h2 class="BodyHeader-nav"><?= t('server.bonus.bonus_points_shop') ?></h2>
    </div>
    <form action="bonus.php?action=purchase&label=<?= $Label ?>" method="post">
        <input type="hidden" name="auth" value="<?= G::$LoggedUser['AuthKey'] ?>" />
        <input type="hidden" name="confirm" value="true" />
        <table class="Form-rowList" id="custom-title-setting" variant="header">
            <tr class="Form-rowHeader">
                <td>
                    <?= t('server_bonus_purchase_confirmation') ?>
                </td>
            </tr>
            <tr class="Form-row">
                <td class="Form-label">
                    <?= t('server.bonus.th_item') ?>:
                </td>
                <td class="Form-inputs">
                    <?= t('server.bonus.free_and_top_torrent_group') ?>
                </td>
            </tr>
            <tr class="Form-row">
                <td class="Form-label">
                    <?= t('server.bonus.th_price') ?>:
                </td>
                <td class="Form-inputs">
                    <?= number_format($Price) ?>
                </td>
            </tr>
            <tr class="Form-row">
                <td class="Form-label">
                    <?= t('server.bonus.torrent_group_id') ?><span class="read">*</span>:
                </td>
                <td class="Form-inputs">
                    <input class="is-small Input" type="text" id="torrent_group_id" name="torrent_group_id" />
                </td>
            </tr>
            <tr class="Form-row">
                <td class="Form-label">
                    <?= t('server.bonus.free_days') ?><span class="read"></span>:
                </td>
                <td class="Form-inputs">
                    <input class="is-small Input" disabled type="text" id="free_days" name="free_days" value="1" />
                </td>
            </tr>
            <tr class="Form-row">
                <td>
                    <input class="Button" type="submit" onclick="ConfirmPurchase(event, '<?= t('server.bonus.free_and_top_torrent_group') ?>')" value="<?= t('server.common.submit') ?>" />&nbsp;
                </td>
            </tr>
            </td>
            </tr>
        </table>
    </form>
</div>

<? View::show_footer();
