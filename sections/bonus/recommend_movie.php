<?php

require(CONFIG['SERVER_ROOT'] . '/classes/recommend_groups.class.php');

$ID = G::$LoggedUser['ID'];
$Label = $_REQUEST['label'];

if (isset($_POST['confirm']) && isset($_POST['torrent_group_id'])) {
    authorize();

    if (!preg_match('/^recommend-movie-([7|30])$/', $Label, $match)) {
        error(t('server.bonus.you_cannot_afford_this_item'));
    }

    $flag = $Bonus->purchaseRecommendMovie($ID, $Label, G::$LoggedUser['EffectiveClass']);

    if ($flag) {
        $LimitEndTime = date('Y-m-d H:i:s', strtotime("+{$match[1]} day"));
        \Torrents::freeleech_groups($_POST['torrent_group_id'], 1, 0, $LimitEndTime);
        \RecommendGroups::recommend_group_buy($ID, $_POST['torrent_group_id'], $LimitEndTime);
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
                    <?= t("server.bonus.$Label") ?>
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
                <td>
                    <input class="Button" type="submit" onclick="ConfirmPurchase(event, '<?= t("server.bonus.$Label") ?>')" value="<?= t('server.common.submit') ?>" />&nbsp;
                </td>
            </tr>
            </td>
            </tr>
        </table>
    </form>
</div>

<? View::show_footer();
