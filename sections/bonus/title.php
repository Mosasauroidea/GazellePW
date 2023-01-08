<?php

if (isset($_REQUEST['preview']) && isset($_REQUEST['title']) && isset($_REQUEST['BBCode'])) {
    echo $_REQUEST['BBCode'] === 'true'
        ? Text::full_format($_REQUEST['title'])
        : Text::strip_bbcode($_REQUEST['title']);
    die();
}

$ID = G::$LoggedUser['ID'];
$Label = $_REQUEST['label'];
if ($Label === 'title-off') {
    authorize();
    Users::removeCustomTitle($ID);
    header('Location: bonus.php?complete=' . urlencode($Label));
}
if ($Label === 'title-bb-y') {
    $BBCode = 'true';
} elseif ($Label === 'title-bb-n') {
    $BBCode = 'false';
} else {
    error(403);
}

if (isset($_POST['confirm'])) {
    authorize();
    if (!isset($_POST['title'])) {
        error(403);
    }
    if ($Bonus->purchaseTitle($ID, $Label, $_POST['title'], G::$LoggedUser['EffectiveClass'])) {
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
                    <?= t('server.bonus.custom_title') ?>, <?= ($BBCode === 'true') ? t('server.bonus.custom_title') : t('server.bonus.no_bbcode_allowed') ?>
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
                    <?= t('server.bonus.custom_title') ?>:
                </td>
                <td class="Form-inputs">
                    <input class="is-small Input" type="text" id="title" name="title" />
                    <input class="Button" type="button" onclick="PreviewTitle(<?= $BBCode ?>);" value="Preview" />
                    <div id="preview" class="Username-customTitle"></div>
                </td>
            </tr>
            <tr class="Form-row">
                <td>
                    <input class="Button" type="submit" onclick="ConfirmPurchase(event, '<?= $Item['Title'] ?>')" value="<?= t('server.common.submit') ?>" />&nbsp;
                </td>
            </tr>
            </td>
            </tr>
        </table>
    </form>
</div>

<? View::show_footer();
