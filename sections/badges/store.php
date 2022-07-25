<?
$StoreList = array(
    '1st_ann_normal' => array(
        0 => array(
            'price' => 1000,
            'unshelve' => '2020-06-15'
        )
    ),
    '1st_ann_blackgold' => array(
        0 => array(
            'price' => 10000,
            'unshelve' => '2020-06-15'
        )
    )
);
if (!empty($_POST)) {
    foreach (['action', 'id'] as $arg) {
        if (!isset($_POST[$arg])) {
            error(403);
        }
    }
    if ($_POST['action'] !== 'store') {
        error(403);
    }
    $Badge = Badges::get_badges_by_id(intval($_POST['id']));
    if (!isset($StoreList[$Badge['Label']]) || !isset($StoreList[$Badge['Label']][$Badge['Level']])) {
        error(403);
    }
    if (strtotime($StoreList[$Badge['Label']][$Badge['Level']]['unshelve']) - time() < 0) {
        error(403);
    }
    $r = Badges::buy($LoggedUser['ID'], $Badge['ID'], $StoreList[$Badge['Label']][$Badge['Level']]['price']);
    echo json_encode(array("code" => $r));
    return;
}
View::show_header(t('server.badges.badges_center'), '', 'PageBadgeStore');
?>
<div class="LayoutBody">
    <div class="BodyHeader">
        <h2 class="BodyHeader-nav"><?= Users::format_username($UserID, false, false, false) ?> &gt; <?= t('server.badges.index_badge') ?> &gt; <?= t('server.badges.badge_store') ?></h2>
    </div>
    <div class="BodyNavLinks">
        <a href="/badges.php?action=display" class="brackets"><?= t('server.badges.badge_display') ?></a>
        <a href="/badges.php" class="brackets"><?= t('server.badges.badge_achievement_progress') ?></a>
        <a href="/badges.php?action=history" class="brackets"><?= t('server.badges.badge_log') ?></a>
        <a href="/badges.php?action=store" class="brackets"><?= t('server.badges.badge_store') ?></a>
        <!-- <a href="" class="brackets">游乐中心</a> -->
    </div>
    <div class="TableContainer badge_store_container">
        <table class="TableBadgeStore Table">
            <tr class="Table-rowHeader">
                <td class="Table-cell badge_number">#</td>
                <td class="Table-cell badge_preview"><?= t('server.badges.badge_preview') ?></td>
                <td class="Table-cell badge_name"><?= t('server.badges.badge_name') ?></td>
                <td class="Table-cell badge_introduction"><?= t('server.badges.badge_introduction') ?></td>
                <td class="Table-cell badge_unshelve"><?= t('server.badges.badge_unshelve') ?></td>
                <td class="Table-cell badge_price"><?= t('server.badges.badge_price') ?></td>
                <td class="Table-cell badge_exchange"><?= t('server.badges.badge_exchange') ?></td>
                <!-- 每一列都要加这些 class -->
            </tr>
            <?
            $i = 0;
            $BadgesByUserID = Badges::get_badges_by_userid($LoggedUser['ID']);
            foreach ($StoreList as $Label => $Items) {
                $Badges = Badges::get_badges_by_label($Label);
                $BadgeLabels = Badges::get_badge_labels();
                foreach ($Badges as $Badge) {
                    if (!isset($Items[$Badge['Level']])) continue;
                    $i++;
                    $Exchanged = isset($BadgesByUserID[$Badge['ID']]);
                    $Unshelved = strtotime($Items[$Badge['Level']]['unshelve']) - time() < 0;
                    $CanExchange = $LoggedUser['BonusPoints'] >= $Items[$Badge['Level']]['price'];
            ?>
                    <tr class="Table-row">
                        <td class="Table-cell badge_number"><?= $i ?></td>
                        <td class="Table-cell badge_preview">
                            <img src="<?= $Badge['BigImage'] ?>">
                        </td>
                        <td class="Table-cell badge_name"><?= Badges::get_text($Label, 'badge_name') ?></td>
                        <td class="Table-cell badge_introduction"><?= Badges::get_text($Label, 'badge_introduction') ?></td>
                        <td class="Table-cell badge_unshelve"><?= time_diff($Items[$Badge['Level']]['unshelve']) ?></td>
                        <td class="Table-cell badge_price"><?= $Items[$Badge['Level']]['price'] ?></td>
                        <td class="Table-cell badge_exchange">
                            <?=
                            $Exchanged ?
                                "Exchanged" : ($Unshelved ?
                                    t('server.badges.sold_out') : ($CanExchange ?
                                        "<a href=\"javascript:buy(" . $Badge['ID'] . ")\">Exchange</a>" :
                                        "Too Expensive"))
                            ?></td>
                    </tr>
                    <!-- <tr class="rowb" data-tooltip="仅在没有商品时显示！">
                <td colspan="7" align="center">No badge selling!</td>
            </tr> -->
                <?
                }
            }
            if (!$i) {
                ?>
                <tr class="Table-row" data-tooltip="仅在没有商品时显示！">
                    <td class="Table-cell Table-cellCenter" colspan="7">No badge selling!</td>
                </tr>
            <?
            }
            ?>
        </table>
    </div>
</div>
<script>
    function buy(badgeid) {
        if (confirm("确认购买这个印记吗？")) {
            $.ajax({
                url: "badges.php",
                data: {
                    "action": "store",
                    "id": badgeid
                },
                type: "POST",
                success: data => {
                    if (data.code) {
                        alert("购买成功！")
                    } else {
                        alert("购买失败！")
                    }
                },
                dataType: "json",
            })
        }
    }
</script>

<?
View::show_footer();
