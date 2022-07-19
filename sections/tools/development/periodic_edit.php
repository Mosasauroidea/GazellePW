<?php
if (!check_perms('admin_periodic_task_manage')) {
    error(403);
}

$scheduler = new \Gazelle\Schedule\Scheduler;
$tasks = $scheduler->getTasks();
$canEdit = true;

View::show_header('Periodic Task Manager', '', 'PageToolPeriodicEdit');
?>
<div class="BodyHeader">
    <h2 class="BodyHeader-nav">Periodic Task Manager</h2>
</div>
<?php
include(CONFIG['SERVER_ROOT'] . '/sections/tools/development/periodic_links.php');

if (isset($err)) { ?>
    <strong class="u-colorWarning"><?= $err ?></strong>
<?php } ?>
<table class="Table">
    <tr class="Table-rowHeader">
        <td class="Table-cell">Name</td>
        <td class="Table-cell">Class Name</td>
        <td class="Table-cell">Description</td>
        <td class="Table-cell">Interval</td>
        <td class="Table-cell">Enabled</td>
        <td class="Table-cell">Sane</td>
        <td class="Table-cell">Debug</td>
        <td class="Table-cell"></td>
    </tr>
    <?php
    $row = 'b';
    foreach ($tasks as $task) {
        list($id, $name, $classname, $description, $period, $isEnabled, $isSane, $isDebug) = array_values($task);
    ?>
        <tr class="Table-row">
            <form class="manage_form" name="accounts" action="" method="post">
                <input type="hidden" name="id" value="<?= $id ?>" />
                <input type="hidden" name="action" value="periodic" />
                <input type="hidden" name="mode" value="alter" />
                <input type="hidden" name="auth" value="<?= $LoggedUser['AuthKey'] ?>" />
                <td class="Table-cell">
                    <input class="Input" type="text" size="15" name="name" value="<?= $name ?>" />
                </td>
                <td class="Table-cell">
                    <input class="Input" type="text" size="15" name="classname" value="<?= $classname ?>" />
                </td>
                <td class="Table-cell">
                    <input class="Input" type="text" size="40" name="description" value="<?= $description ?>" />
                </td>
                <td class="Table-cell">
                    <input class="Input" type="text" size="7" name="interval" value="<?= $period ?>" />
                </td>
                <td class="Table-cell">
                    <input type="checkbox" name="enabled" <?= ($isEnabled == '1') ? ' checked="checked"' : '' ?> />
                </td>
                <td class="Table-cell">
                    <input type="checkbox" name="sane" <?= ($isSane == '1') ? ' checked="checked"' : '' ?> />
                </td>
                <td class="Table-cell">
                    <input type="checkbox" name="debug" <?= ($isDebug == '1') ? ' checked="checked"' : '' ?> />
                </td>
                <td class="Table-cell">
                    <input class="Button" type="submit" name="submit" value="Edit" />
                    <input class="Button" type="submit" name="submit" value="Delete" onclick="return confirm('Are you sure you want to delete this task? This is an irreversible action!')" />
                </td>
            </form>
        </tr>
    <?php
    }
    ?>
    <tr class="Table-rowHeader">
        <td class="Table-cell" colspan="8">Create Task</td>
    </tr>
    <tr class="Table-row">
        <form class="create_form" name="accounts" action="" method="post">
            <input type="hidden" name="action" value="periodic" />
            <input type="hidden" name="mode" value="alter" />
            <input type="hidden" name="auth" value="<?= $LoggedUser['AuthKey'] ?>" />
            <td class="Table-cell">
                <input class="Input" type="text" size="10" name="name" />
            </td>
            <td class="Table-cell">
                <input class="Input" type="text" size="15" name="classname" />
            </td>
            <td class="Table-cell">
                <input class="Input" type="text" size="10" name="description" />
            </td>
            <td class="Table-cell">
                <input class="Input" type="text" size="10" name="interval" />
            </td>
            <td class="Table-cell">
                <input type="checkbox" name="enabled" checked="checked" />
            </td>
            <td class="Table-cell">
                <input type="checkbox" name="sane" checked="checked" />
            </td>
            <td class="Table-cell">
                <input type="checkbox" name="debug" />
            </td>
            <td class="Table-cell">
                <input class="Button" type="submit" name="submit" value="Create" />
            </td>
        </form>
    </tr>
</table>
<?php
View::show_footer();
?>