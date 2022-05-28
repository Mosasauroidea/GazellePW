<?
enforce_login();
if (check_perms('admin_manage_blog') && !empty($_POST)) {
    switch ($_POST['do']) {
        case 'add':
            Activity::add($_POST['text']);
            break;
        case 'edit':
            Activity::edit($_POST['id'], $_POST['text']);
            break;
        case 'show':
            Activity::show($_POST['id']);
            break;
        case 'hide':
            Activity::hide($_POST['id']);
            break;
        case 'delete':
            Activity::delete($_POST['id']);
            break;
        default:
            error(403);
    }
    header("Location: /activity.php");
    return;
}
View::show_header('Activity', '', 'PageActivityHome');
$Activities = Activity::getActivities();
?>
<div class="LayoutBody">
    <?
    if (check_perms('admin_manage_blog')) {
    ?>
        <form method="POST">
            <input class="Input" type="text" name="text">
            <input class="Button" type="submit" name="do" value="add">
        </form>
    <?
    }
    foreach ($Activities as $Activity) {
        //$BlogTime = strtotime($BlogTime);
    ?>
        <div>
            <?
            if (check_perms('admin_manage_blog')) {
            ?>
                <form method="POST">
                    <input type="hidden" name="id" value="<?= $Activity['ID'] ?>">
                    <input class="Input" type="text" name="text" value="<?= $Activity['Text'] ?>">
                    <input class="Button" type="submit" name="do" value="edit">
                    <input class="Button" type="submit" name="do" value="delete">
                    <input class="Button" type="submit" name="do" value="<?= $Activity['Display'] ? "hide" : "show" ?>">
                </form>
            <?
            } else {
            ?>
                <strong><?= $Activity['Text'] ?></strong> - posted <?= time_diff($Activity['Time']); ?>
            <?
            }
            ?>
        </div>
    <?
    }
    ?>
</div>
<?
View::show_footer();
?>