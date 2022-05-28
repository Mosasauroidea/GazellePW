<?php
function formatBool(bool $val) {
    return $val ? 'Yes' : 'No';
}

if (!check_perms('admin_periodic_task_view')) {
    error(403);
}

$scheduler = new \Gazelle\Schedule\Scheduler;

if ($_REQUEST['mode'] === 'run_now' && isset($_REQUEST['id'])) {
    authorize();
    if (!check_perms('admin_schedule')) {
        error(403);
    }
    $scheduler->runNow(intval($_REQUEST['id']));
}

$tasks = $scheduler->getTaskDetails();
$canEdit = check_perms('admin_periodic_task_manage');
$canLaunch = check_perms('admin_schedule');

View::show_header('Periodic Task Status', '', 'PageToolPeriodicView');
?>
<div class="BodyHeader">
    <h2 class="BodyHeader-nav">Periodic Task Status</h2>
</div>
<?php include(__DIR__ . '/periodic_links.php'); ?>
<table class="Table">
    <tr class="Table-rowHeader">
        <td class="Table-cell">Name</td>
        <td class="Table-cell">Interval</td>
        <td class="Table-cell">Last Run <a href="#" onclick="$('#tasks .reltime').gtoggle(); $('#tasks .abstime').gtoggle(); return false;" class="brackets">Toggle</a></td>
        <td class="Table-cell">Duration</td>
        <td class="Table-cell">Next Run</td>
        <td class="Table-cell">Status</td>
        <td class="Table-cell">Runs</td>
        <td class="Table-cell">Processed</td>
        <td class="Table-cell">Errors</td>
        <td class="Table-cell">Events</td>
        <td class="Table-cell"></td>
    </tr>
    <?php
    $row = 'b';
    foreach ($tasks as $task) {
        list($id, $name, $description, $period, $isEnabled, $isSane, $runNow, $runs, $processed, $errors, $events, $lastRun, $duration, $status) = array_values($task);

        if ($runs == 0) {
            $lastRun = 'Never';
            $nextRun = sqltime();
            $duration = '-';
            $status = '-';
            $processed = '0';
            $errors = '0';
            $events = '0';
        } else {
            $duration .= 'ms';
            $nextRun = sqltime(strtotime($lastRun) + $period);
            if ($status === 'running') {
                $duration = time_diff(time() - strtotime($lastRun) + time());
            }
        }
        $period = time_diff(sqltime(time() + $period), 2, false);

        $row = $row === 'a' ? 'b' : 'a';
        $prefix = '';
        $color = null;
        if (!$isSane) {
            $color = " color:tomato;";
            $prefix .= 'Insane: ';
        }
        if (!$isEnabled && !$runNow) {
            $color = " color:sandybrown;";
            $prefix .= 'Disabled: ';
        }
        if ($runNow) {
            $color = " color:green;";
            $prefix .= 'Run Now: ';
        }
    ?>
        <tr class="Table-row">
            <td class="Table-cell" data-tooltip="<?= $description ?>">
                <a style="<?= $color ?? '' ?>" href="tools.php?action=periodic&amp;mode=detail&amp;id=<?= $id ?>"><?= $prefix . $name ?></a>
            </td>
            <td class="Table-cell"><?= $period ?></td>
            <td class="Table-cell">
                <span class="reltime"><?= time_diff($lastRun) ?></span>
                <span class="abstime hidden"><?= $lastRun ?></span>
            </td>
            <td class="Table-cell"><?= $duration ?></td>
            <td class="Table-cell">
                <span class="reltime"><?= time_diff($nextRun) ?></span>
                <span class="abstime hidden"><?= $nextRun ?></span>
            </td>
            <td class="Table-cell"><?= $status ?></td>
            <td class="Table-cell Table-cellRight"><?= number_format($runs) ?></td>
            <td class="Table-cell Table-cellRight"><?= number_format($processed) ?></td>
            <td class="Table-cell Table-cellRight"><?= number_format($errors) ?></td>
            <td class="Table-cell Table-cellRight"><?= number_format($events) ?></td>
            <td class="Table-cell">
                <?php if ($canLaunch) { ?>
                    <a class="brackets" href="tools.php?action=periodic&amp;mode=run_now&amp;auth=<?= $LoggedUser['AuthKey'] ?>&amp;id=<?= $id ?>">Run Now</a>
                    <a class="brackets" href="schedule.php?auth=<?= $LoggedUser['AuthKey'] ?>&amp;id=<?= $id ?>">Debug</a>
                <?php } ?>
            </td>
        </tr>
    <?php } ?>
</table>
<?php
View::show_footer();
