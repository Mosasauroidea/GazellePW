<?
if (isset($_POST['do'])) {
    switch ($_POST['do']) {
        case 'addItem':
            Badges::addItem($_POST['label'], $_POST['bigimage'], $_POST['smallimage'], $_POST['level'], $_POST['count']);
            break;
        case 'editItem':
            Badges::editItem($_POST['id'], $_POST['label'], $_POST['bigimage'], $_POST['smallimage'], $_POST['level'], $_POST['count']);
            break;
        case 'deleteItem':
            if (!Badges::deleteItem($_POST['id'])) {
                // error("删除失败");
                error(t('server.tools.delete_failed'));
            }
            break;
        case 'addLabel':
            Badges::addLabel($_POST['label'], $_POST['disimage'], $_POST['type'], $_POST['auto'], $_POST['father'], $_POST['progress'], $_POST['rank'], $_POST['remark']);
            break;
        case 'editLabel':
            Badges::editLabel($_POST['label'], $_POST['disimage'], $_POST['type'], $_POST['auto'], $_POST['father'], $_POST['progress'], $_POST['rank'], $_POST['remark']);
            break;
        case 'deleteLabel':
            if (!Badges::deleteLabel($_POST['label'])) {
                // error("删除失败");
                error(t('server.tools.delete_failed'));
            }
            break;
    }
    header("Location: /tools.php?action=badges&label=" . $_POST['label']);
}
View::show_header(t('server.tools.badge_management'), '', 'PageToolBadge'); ?>
<h2><?= t('server.tools.badge_management') ?></h2>
<?
$BadgeLabels = Badges::get_badge_labels();
?>
<table id="badge_management_table">
    <tr>
        <th></th>
        <th><?= t('server.tools.badge_tag') ?></th>
        <th><?= t('server.tools.badge_image') ?></th>
        <th><?= t('server.tools.badge_type') ?></th>
        <th><?= t('server.tools.badge_auto') ?></th>
        <th><?= t('server.tools.badge_class') ?></th>
        <th><?= t('server.tools.badge_progress') ?></th>
        <th><?= t('server.tools.badge_sort') ?></th>
        <th><?= t('server.tools.badge_note') ?></th>
        <th><?= t('server.tools.badge_operations') ?></th>
    </tr>
    <tr>
        <form action="tools.php?action=badges" method="POST">
            <td></td>
            <td><input class="Input" type="text" name="label"></td>
            <td><input class="Input" type="text" name="disimage"></td>
            <td><input class="Input" type="text" name="type"></td>
            <td><input class="Input" type="number" name="auto" min="0" max="1"></td>
            <td><input class="Input" type="number" name="father" min="0" max="1"></td>
            <td><input class="Input" type="number" name="progress" min="0" max="1"></td>
            <td><input class="Input" type="number" name="rank" min="1" max="9999"></td>
            <td><input class="Input" type="text" name="remark"></td>
            <td><input name="do" type="submit" value="addLabel"></td>
        </form>
    </tr>
    <tr>
        <th></th>
        <th><?= t('server.tools.badge_tag') ?></th>
        <th><?= t('server.tools.badge_image') ?></th>
        <th><?= t('server.tools.badge_icon') ?></th>
        <th><?= t('server.tools.badge_level') ?></th>
        <th><?= t('server.tools.badge_level_number') ?></th>
    </tr>
    <tr>
        <form action="tools.php?action=badges" method="POST">
            <td></td>
            <td><input class="Input" type="text" name="label"></td>
            <td><input class="Input" type="text" name="bigimage"></td>
            <td><input class="Input" type="text" name="smallimage"></td>
            <td><input class="Input" type="number" name="level" min="-1" max="7"></td>
            <td><input class="Input" type="number" name="count" min="-1" max="9999"></td>
            <td><input name="do" type="submit" value="addItem"></td>
        </form>
    </tr>
    <?
    foreach ($BadgeLabels as $BadgeLabel) {
    ?>
        <tr>
            <th></th>
            <th><?= t('server.tools.badge_tag') ?></th>
            <th><?= t('server.tools.badge_image') ?></th>
            <th><?= t('server.tools.badge_type') ?></th>
            <th><?= t('server.tools.badge_auto') ?></th>
            <th><?= t('server.tools.badge_class') ?></th>
            <th><?= t('server.tools.badge_progress') ?></th>
            <th><?= t('server.tools.badge_sort') ?></th>
            <th><?= t('server.tools.badge_note') ?></th>
            <th><?= t('server.tools.badge_operations') ?></th>
        </tr>
        <tr>
            <form action="tools.php?action=badges" method="POST">
                <td><a href="javascript:toggle('<?= $BadgeLabel['Label'] ?>')">+</a></td>
                <td><input class="Input" type="text" name="label" value="<?= $BadgeLabel['Label'] ?>"></td>
                <td><input class="Input" type="text" name="disimage" value="<?= $BadgeLabel['DisImage'] ?>"></td>
                <td><input class="Input" type="text" name="type" value="<?= $BadgeLabel['Type'] ?>"></td>
                <td><input class="Input" type="number" name="auto" value="<?= $BadgeLabel['Auto'] ?>" min="0" max="1"></td>
                <td><input class="Input" type="number" name="father" value="<?= $BadgeLabel['Father'] ?>" min="0" max="1"></td>
                <td><input class="Input" type="number" name="progress" value="<?= $BadgeLabel['Progress'] ?>" min="0" max="1"></td>
                <td><input class="Input" type="number" name="rank" value="<?= $BadgeLabel['Rank'] ?>" min="1" max="9999"></td>
                <td><input class="Input" type="text" name="remark" value="<?= $BadgeLabel['Remark'] ?>"></td>
                <td><input name="do" type="submit" value="editLabel"></td>
                <td><input name="do" type="submit" value="deleteLabel"></td>
            </form>
        </tr>
        <tr class="badge_<?= $BadgeLabel['Label'] ?>" <?= $_GET['label'] == $BadgeLabel['Label'] ? "" : "style=\"display: none;\"" ?>>
            <th></th>
            <th><?= t('server.tools.badge_tag') ?></th>
            <th><?= t('server.tools.badge_image') ?></th>
            <th><?= t('server.tools.badge_icon') ?></th>
            <th><?= t('server.tools.badge_level') ?></th>
            <th><?= t('server.tools.badge_level_number') ?></th>
        </tr>
        <?
        $Badges = Badges::get_badges_by_label($BadgeLabel['Label']);

        foreach ($Badges as $Badge) {
        ?>
            <tr class="badge_<?= $BadgeLabel['Label'] ?>" <?= $_GET['label'] == $BadgeLabel['Label'] ? "" : "style=\"display: none;\"" ?>>
                <form action="tools.php?action=badges" method="POST">
                    <input type="hidden" name="id" value="<?= $Badge['ID'] ?>">
                    <td></td>
                    <td><input class="Input" type="text" name="label" value="<?= $Badge['Label'] ?>"></td>
                    <td><input class="Input" type="text" name="bigimage" value="<?= $Badge['BigImage'] ?>"></td>
                    <td><input class="Input" type="text" name="smallimage" value="<?= $Badge['SmallImage'] ?>"></td>
                    <td><input class="Input" type="number" name="level" value="<?= $Badge['Level'] ?>" min="-1" max="7"></td>
                    <td><input class="Input" type="number" name="count" value="<?= $Badge['Count'] ?>" min="-1" max="9999"></td>
                    <td><input name="do" type="submit" value="editItem"></td>
                    <td><input name="do" type="submit" value="deleteItem"></td>
                </form>
            </tr>
    <?
        }
    }
    ?>
</table>
<script>
    function toggle(label) {
        $(".badge_" + label).toggle()
    }
</script>
<? View::show_footer(); ?>