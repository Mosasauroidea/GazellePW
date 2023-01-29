<?php

View::show_header(t('server.bonus.bonus_points_purchase_history'), 'bonus', 'PageBonusHistory');

if (check_perms('admin_bp_history') && isset($_GET['id']) && is_number($_GET['id'])) {
    $ID = (int)$_GET['id'];
    $Header = t('server.bonus.bonus_points_spending_history_for', ['Values' => [Users::format_username($ID)]]);
    $WhoSpent = Users::format_username($ID) . t('server.bonus.bonus_points_has_spent');
} else {
    $ID = G::$LoggedUser['ID'];
    $Header = t('server.bonus.bonus_points_spending_history');
    $WhoSpent = t('server.bonus.you_have_spent');
}

$Summary = $Bonus->getUserSummary($ID);

$Page  = max(1, isset($_GET['page']) ? intval($_GET['page']) : 1);
$Pages = Format::get_pages($Page, $Summary['nr'], CONFIG['TORRENTS_PER_PAGE']);

if ($Summary['nr'] > 0) {
    $History = $Bonus->getUserHistory($ID, $Page, CONFIG['TORRENTS_PER_PAGE']);
}

?>
<div class=LayoutBody>
    <div class="BodyHeader">
        <h2 class="BodyHeader-nav"><?= $Header ?></h2>
    </div>
    <div class="BodyNavLinks">
        <a href="wiki.php?action=article&id=47" class="brackets"><?= t('server.bonus.about_bonus_points') ?></a>
        <a href="bonus.php" class="brackets"><?= t('server.bonus.bonus_points_shop') ?></a>
        <a href="bonus.php?action=bprates<?= check_perms('admin_bp_history') && $ID != G::$LoggedUser['ID'] ? "&userid=$ID" : '' ?>" class="brackets"><?= t('server.bonus.bonus_point_rates') ?></a>
    </div>

    <div class="LayoutBody">
        <? if ($Summary['total']) { ?>
            <h3><?= $WhoSpent ?> <?= number_format($Summary['total']) ?> <?= t('server.bonus.bonus_points_to_purchase') ?> <?= $Summary['nr'] ?> <?= $Summary['nr'] == 1 ? t('server.bonus.item') : t('server.bonus.items') ?><?= t('server.bonus.period') ?></h3>
        <? } else { ?>
            <div class="center"><?= t('server.bonus.no_purchase_history') ?></div>
            <?
        }
        if (isset($History)) {
            if (!empty($Pages)) {
            ?>
                <div class="BodyNavLinks">
                    <?= $Pages ?>
                </div>
            <?
            }
            ?>
            <table class="TableBonusPurchaseHistory Table">
                <thead>
                    <tr class="Table-rowHeader">
                        <td class="Table-cell"><?= t('server.bonus.th_item') ?></td>
                        <td class="Table-cell Table-cellRight" width="50px"><?= t('server.bonus.th_price') ?></td>
                        <td class="Table-cell" width="150px"><?= t('server.bonus.th_purchase_date') ?></td>
                        <td class="Table-cell"><?= t('server.bonus.th_for') ?></td>
                    </tr>
                </thead>
                <tbody>
                    <? foreach ($History as $Item) { ?>
                        <tr class="Table-row">
                            <td class="Table-cell"><?= t("server.bonus.${Item['Label']}") ?></td>
                            <td class="Table-cell Table-cellRight"><?= number_format($Item['Price']) ?></td>
                            <td class="Table-cell"><?= time_diff($Item['PurchaseDate']) ?></td>
                            <td class="Table-cell"><?= !$Item['OtherUserID'] ? '&nbsp;' : Users::format_username($Item['OtherUserID']) ?></td>
                        </tr>
                    <?  } ?>
                </tbody>
            </table>
        <? }
        if (!empty($Pages)) { ?>
            <div class="BodyNavLinks">
                <?= $Pages ?>
            </div>
        <?
        }
        ?>
    </div>
</div>
<?

View::show_footer();
