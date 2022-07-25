<?
if (isset($_POST['do'])) {
    switch ($_POST['do']) {
        case 'gave':
            Badges::gave($_POST['userid'], $_POST['badgeid']);
            break;
        case 'query':

            break;
        case 'take':
            Badges::take($_POST['userid'], $_POST['badgeid']);
            break;
    }
    header("Location: /tools.php?action=badges_gave&userid=" . $_POST['userid']);
}
View::show_header(t('server.tools.badge_management'), '', 'PageToolBadgeGave'); ?>

<h2><?= t('server.tools.badge_management') ?></h2>
<?
//$Badges = Badges::get_badges_by_label();
//$Labels = array_keys($Badges);

$Badges = Badges::get_badges_by_id();
$BadgeLabels = Badges::get_badge_labels();
?>
<h3><?= t('server.tools.badge_send') ?></h3>
<form action="tools.php?action=badges_gave" method="POST">
    <select class="Input" name='badgeid'>
        <?
        foreach ($Badges as $Badge) {
            echo "<option value='" . $Badge['ID'] . "'>" . $Badge['Level'] . "-" . $BadgeLabels[$Badge['Label']]['Remark'] . "</option>";
        }
        ?>
    </select>
    <span><?= t('server.tools.badge_to') ?></span>
    <input class="Input" type="number" name="userid" placeholder="UserID">
    <input name="do" type="submit" value="gave">
</form>
<h3><?= t('server.tools.badge_query') ?></h3>
<form action="tools.php?action=badges_gave" method="POST">
    <input class="Input" type="number" name="userid" placeholder="UserID">
    <input name="do" type="submit" value="query">
</form>
<h3><?= t('server.tools.badge_withdraw') ?></h3>
<form action="tools.php?action=badges_gave" method="POST">
    <input class="Input" type="number" name="userid" placeholder="UserID" value="<?= $_GET['userid'] ?>" readonly>
    <select class="Input" name='badgeid'>
        <?
        $UserBadges = Badges::get_badges_by_userid($_GET['userid']);

        foreach ($UserBadges as $Badge) {
            echo "<option value='" . $Badge['BadgeID'] . "'>" . $Badges[$Badge['BadgeID']]['Level'] . "-" . $BadgeLabels[$Badges[$Badge['BadgeID']]['Label']]['Remark'] . "</option>";
        }
        ?>
    </select>
    <input name="do" type="submit" value="take">
</form>
<? View::show_footer(); ?>