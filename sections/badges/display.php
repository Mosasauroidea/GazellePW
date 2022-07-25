<?
if (!empty($_POST)) {
    foreach (['action', 'do', 'label'] as $arg) {
        if (!isset($_POST[$arg])) {
            error(403);
        }
    }
    if ($_POST['action'] !== 'display') {
        error(403);
    }
    $r = 0;
    switch ($_POST['do']) {
        case 'wear':
            $r = Badges::wearUsername($LoggedUser['ID'], db_string($_POST['label']));
            break;
        case 'display':
            $r = Badges::WearProfile($LoggedUser['ID'], db_string($_POST['label']));
            break;
        case 'unwear':
            Badges::unWearUsername($LoggedUser['ID'], db_string($_POST['label']));
            break;
        case 'undisplay':
            Badges::unWearProfile($LoggedUser['ID'], db_string($_POST['label']));
            break;
        default:
            error(403);
    }
    echo json_encode(array("code" => $r));
    return;
}
View::show_header(t('server.badges.badges_center'), '', 'PageBadgeDisplay');
$BadgeLabelsByType = Badges::get_badge_labels_by_type();
$BadgesByLabel = Badges::get_badges_by_label();
$BadgesByUserID = Badges::get_badges_by_userid($LoggedUser['ID']);
$BadgeLabels = Badges::get_badge_labels();
$WearOrDisplay = Badges::get_wear_badges($LoggedUser['ID']);
?>
<div class="LayoutBody">
    <div id="lightbox" class="lightbox" style="display: none;" onclick="hideBadges()">
        <?
        foreach ($BadgeLabels as $Label => $BadgeLabel) {
        ?>
            <div id="badge_<?= $Label ?>_display" class="badge_level_display" style="display: none;">
                <?
                $Badges = Badges::get_badges_by_label($Label);
                foreach ($Badges as $Badge) {
                ?>
                    <img class="badge_img" src="<?= isset($BadgesByUserID[$Badge['ID']]) ? $Badge['BigImage'] : $BadgeLabel['DisImage'] ?>">
                <?
                }
                ?>
            </div>
        <?
        }
        ?>
    </div>
    <div id="curtain" class="curtain hidden"></div>
    <div class="BodyHeader">
        <h2 class="BodyHeader-nav"><?= Users::format_username($UserID, false, false, false) ?> &gt; <?= t('server.badges.index_badge') ?> &gt; <?= t('server.badges.badge_display') ?></h2>
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
    ?><div class="badge_progress">
            <div class="head">
                <h3><?= Badges::get_text($Type, 'type_head') ?></h3>
            </div>
            <div class="body card_container" id="data_group">
                <?
                foreach ($BadgeLabels as $BadgeLabel) {
                    $Label = $BadgeLabel['Label'];
                    $Badges = Badges::get_badges_by_label($Label);
                    $NowBadge = false;
                    $Wear = false;
                    $Display = false;
                    foreach ($Badges as $Badge) {
                        if (isset($BadgesByUserID[$Badge['ID']])) {
                            $NowBadge = $Badge;
                            if (in_array($Badge['ID'], $WearOrDisplay['Username'])) {
                                $Wear = true;
                            }
                            if (in_array($Badge['ID'], $WearOrDisplay['Profile'])) {
                                $Display = true;
                            }
                        }
                    }
                ?>
                    <div class="card" id="uploaded">
                        <div class="card_inside">
                            <div class="badge_container">
                                <img onclick="showBadges('<?= $Label ?>')" src="<?= $NowBadge ? $NowBadge['BigImage'] : $BadgeLabel['DisImage'] ?>" data-tooltip="<?= Badges::get_text($Label, 'badge_title') ?>">
                            </div>
                            <div class="badge_title">
                                <h4><?= Badges::get_text($Label, 'badge_name_in_card') ?></h4>
                            </div>
                            <div class="badge_introduction">
                                <div><?= Badges::get_text($Label, 'badge_introduction') ?></div>
                            </div>
                            <div class="badge_display_btns">
                                <a id="wear_badge_<?= $Label ?>" <?= $Wear ? " class=\"active\"" : "" ?> data-tooltip="<?= Badges::get_text('button', 'wear_badge') ?>" <?= $NowBadge ? " href=\"javascript:" . ($Wear ? "unW" : "w") . "earBadge('$Label')\"" : "" ?>>
                                    <?= icon("User/wear-badge") ?>
                                </a>
                                <a id="display_badge_<?= $Label ?>" <?= $Display ? " class=\"active\"" : "" ?> data-tooltip="<?= Badges::get_text('button', 'display_badge') ?>" <?= $NowBadge ? " href=\"javascript:" . ($Display ? "unD" : "d") . "isplayBadge('$Label')\"" : "" ?>>
                                    <?= icon("User/display-badge") ?>
                                </a>
                            </div>
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
<script>
    function showBadges(label) {
        $("#lightbox").show()
        $("#badge_" + label + "_display").show()
    }

    function hideBadges() {
        $("#lightbox").hide()
        $(".badge_level_display").hide()
    }

    function wearBadge(label) {
        $.ajax({
            url: "badges.php",
            data: {
                "action": "display",
                "do": "wear",
                "label": label
            },
            type: "POST",
            success: data => {
                if (data.code) {
                    document.getElementById("wear_badge_" + label).href = document.getElementById("wear_badge_" + label).href.replace('wear', "unWear")
                    $("#wear_badge_" + label).addClass("active")
                    alert("佩戴成功！")
                } else {
                    alert("佩戴失败，超出佩戴数量限制！")
                }
            },
            dataType: "json",
        })
    }

    function displayBadge(label) {
        $.ajax({
            url: "badges.php",
            data: {
                "action": "display",
                "do": "display",
                "label": label
            },
            type: "POST",
            success: data => {
                if (data.code) {
                    document.getElementById("display_badge_" + label).href = document.getElementById("display_badge_" + label).href.replace('display', "unDisplay")
                    $("#display_badge_" + label).addClass("active")
                    alert("佩戴成功！")
                } else {
                    alert("佩戴失败，超出展示数量限制！")
                }
            },
            dataType: "json",
        })
    }

    function unWearBadge(label) {
        $.ajax({
            url: "badges.php",
            data: {
                "action": "display",
                "do": "unwear",
                "label": label
            },
            type: "POST",
            success: data => {
                document.getElementById("wear_badge_" + label).href = document.getElementById("wear_badge_" + label).href.replace('unWear', "wear")
                $("#wear_badge_" + label).removeClass("active")
                alert("取消成功！")
            },
            dataType: "json",
        })
    }

    function unDisplayBadge(label) {
        $.ajax({
            url: "badges.php",
            data: {
                "action": "display",
                "do": "undisplay",
                "label": label
            },
            type: "POST",
            success: data => {
                document.getElementById("display_badge_" + label).href = document.getElementById("display_badge_" + label).href.replace('unDisplay', "display")
                $("#display_badge_" + label).removeClass("active")
                alert("取消成功！")
            },
            dataType: "json",
        })
    }
</script>
<?
View::show_footer();
