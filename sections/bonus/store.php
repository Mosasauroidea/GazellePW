<?php

View::show_header('积分商城', 'bonus', 'PageBonusStore');

if (isset($_GET['complete'])) {
    $label = $_GET['complete'];
    $item = $Bonus->getItem($label);
?>
    <div class="alertbar blend">
        &quot;<?= Lang::get('bonus', $item['Label']) ?>&quot;&nbsp;<?= Lang::get('bonus', 'purchased') ?>
    </div>
<?
}

?>
<div class=LayoutBody>
    <div class="BodyHeader">
        <h2 class="BodyHeader-nav"><?= Lang::get('bonus', 'bonus_points_shop') ?></h2>
    </div>
    <div class="BodyNavLinks">
        <a href="wiki.php?action=article&id=47" class="brackets"><?= Lang::get('bonus', 'about_bonus_points') ?></a>
        <a href="bonus.php?action=bprates" class="brackets"><?= Lang::get('bonus', 'bonus_point_rates') ?></a>
        <a href="bonus.php?action=history" class="brackets"><?= Lang::get('bonus', 'history') ?></a>
    </div>

    <div class="LayoutBody">
        <div class="TableContainer">
            <table class="TableBonusStore Table">
                <thead>
                    <tr class="Table-rowHeader">
                        <td class="Table-cell" width="30px">#</td>
                        <td class="Table-cell"><?= Lang::get('bonus', 'description') ?></td>
                        <td class="Table-cell" width="45px"><?= Lang::get('bonus', 'points_price') ?></td>
                        <td class="Table-cell" width="70px"><?= Lang::get('bonus', 'checkout') ?></td>
                    </tr>
                </thead>
                <tbody>
                    <?php

                    $Cnt = 0;
                    $Items = $Bonus->getList();

                    foreach ($Items as $Label => $Item) {
                        /*
    if ($Item['MinClass'] >  G::$LoggedUser['EffectiveClass']) {
        continue;
    }
    */
                        $Cnt++;
                        $Price = $Bonus->getEffectivePrice($Label, G::$LoggedUser['EffectiveClass']);
                        $FormattedPrice = number_format($Price);
                    ?>
                        <tr class="Table-row">
                            <td class="Table-cell"><?= $Cnt ?></td>
                            <td class="Table-cell"><?= Lang::get('bonus', $Item['Label']) ?></td>
                            <td class="Table-cell"><?= $FormattedPrice ?></td>
                            <td class="Table-cell">
                                <?
                                if ($Item['MinClass'] >  $LoggedUser['EffectiveClass']) {
                                ?>
                                    <span style="font-style: italic"><?= Lang::get('bonus', 'need_higher_user_class') ?></span>
                                    <?
                                } else {
                                    if (G::$LoggedUser['BonusPoints'] >= $Price) {
                                        $NextFunction = preg_match('/^other-\d$/',          $Label) ? 'ConfirmOther' : 'null';
                                        $OnClick      = preg_match('/^title-bbcode-[yn]$/', $Label) ? "NoOp" : "ConfirmPurchase";
                                    ?>
                                        <a id="bonusconfirm" href="bonus.php?action=purchase&label=<?= $Label ?>&auth=<?= $LoggedUser['AuthKey'] ?>" onclick="<?= $OnClick ?>(event, '<?= Lang::get('bonus', $Item['Label']) ?>', <?= $NextFunction ?>, this);"><?= Lang::get('bonus', 'purchase') ?></a>
                                    <?
                                    } else {
                                    ?>
                                        <span style="font-style: italic"><?= Lang::get('bonus', 'too_expensive') ?></span>
                            <?
                                    }
                                }

                                print <<<HTML
				</td>
	</tr>
HTML;
                            }
                            ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php

View::show_footer();
