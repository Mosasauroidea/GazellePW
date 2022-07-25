<?

View::show_header(t('server.badges.badges_center'), '', 'PageBadgeShow');
$BadgeLabelsByType = Badges::get_badge_labels_by_type();
$BadgesByUserID = Badges::get_badges_by_userid($LoggedUser['ID']);
$BadgesCount = Badges::get_badges_count($LoggedUser['ID']);
?>
<div class="LayoutBody">
    <div class="BodyHeader">
        <h2 class="BodyHeader-nav"><?= Users::format_username($UserID, false, false, false) ?> &gt; <?= t('server.badges.index_badge') ?> &gt; <?= t('server.badges.badge_achievement_progress') ?></h2>
    </div>
    <div class="BodyNavLinks">
        <a href="/badges.php?action=display" class="brackets"><?= t('server.badges.badge_display') ?></a>
        <a href="/badges.php" class="brackets"><?= t('server.badges.badge_achievement_progress') ?></a>
        <a href="/badges.php?action=history" class="brackets"><?= t('server.badges.badge_log') ?></a>
        <a href="/badges.php?action=store" class="brackets"><?= t('server.badges.badge_store') ?></a>
        <!-- <a href="" class="brackets">游乐中心</a> -->
    </div>
    <?
    foreach ($BadgeLabelsByType as $Type => $BadgeLabels) {
        $ShowType = false;
        foreach ($BadgeLabels as $BadgeLabel) {
            if ($BadgeLabel['Progress']) $ShowType = true;
        }
        if (!$ShowType) continue;
    ?>
        <div class="badge_progress">
            <div class="head">
                <h3><?= Badges::get_text($Type, 'type_head') ?></h3>
            </div>
            <div class="body" id="data_group">
                <?
                foreach ($BadgeLabels as $BadgeLabel) {
                    if (!$BadgeLabel['Progress']) continue;
                    $NowBadge = false;
                    $Label = $BadgeLabel['Label'];
                    $Badges = Badges::get_badges_by_label($Label);
                    foreach ($Badges as $Badge) {
                        if (isset($BadgesByUserID[$Badge['ID']])) {
                            $NowBadge = $Badge;
                        }
                    }
                ?>
                    <div id="badge_<?= $Label ?>" class="<?= $BadgeLabel['Father'] ? "father" : "son" ?> badge_and_progress">
                        <div class="badge_img_container" data-tooltip="<?= Badges::get_text($Label, 'badge_title') ?>">
                            <img class="badge_img" src="<?= $NowBadge ? $NowBadge['BigImage'] : $BadgeLabel['DisImage'] ?>">
                        </div>
                        <div class="title_and_progressbar">
                            <h4><?= Badges::get_text($Label, 'badge_name') ?></h4>
                            <div class="progressbar" data-tooltip="<?= number_format($BadgesCount[$Label], 2) ?>">
                                <?
                                $BadgeP = 0;
                                $LastBadgeCount = 0;
                                foreach ($Badges as $Badge) {
                                    $Has = false;
                                    $CountP = 100;
                                    if (isset($BadgesByUserID[$Badge['ID']])) {
                                        $Has = true;
                                    } else {
                                        $CountP = ($BadgesCount[$Label] - $LastBadgeCount) > ($Badge['Count'] - $LastBadgeCount) ? 100 : (($BadgesCount[$Label] - $LastBadgeCount) / ($Badge['Count'] - $LastBadgeCount) * 100);
                                        $CountP = $CountP < 0 ? 0 : $CountP;
                                    }
                                    $BadgeP += $CountP;
                                    $LastBadgeCount = $Badge['Count'];
                                ?>
                                    <div class="level<?= $Badge['Level'] ?> progress_container">
                                        <div class="level<?= $Badge['Level'] ?> progress" style="width: <?= $CountP ?>%;"></div>
                                    </div>
                                    <div class="level<?= $Badge['Level'] ?> circle_container<?= $Has ? ($NowBadge['Level'] == $Badge['Level'] ? " current" : " achieved") : "" ?>">
                                        <div class="level<?= $Badge['Level'] ?> circle">
                                            <i class="<?= $Has && $NowBadge['Level'] == $Badge['Level'] ? "" : " hidden" ?>"><?= icon("check") ?></i>
                                        </div>
                                    </div>
                                <?
                                }
                                ?>
                            </div>
                            <div class="level_numbers">
                                <span><?= Badges::get_text($Label, 'level0') ?></span>
                                <?
                                foreach ($Badges as $Badge) {
                                    echo "<span>" . Badges::get_text($Label, 'level' . $Badge['Level']) . "</span>";
                                }
                                ?>
                            </div>
                        </div>
                        <div class="total_percent">
                            <span><?= number_format($BadgeP == 700 ? 100 : $BadgeP % 100) ?>%</span>
                        </div>
                    </div>
                <?
                }
                ?>
            </div>
        </div>
    <?
    }
    ?>
</div>


<?
View::show_footer();
