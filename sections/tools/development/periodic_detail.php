<?php
if (!check_perms('admin_periodic_task_view')) {
    error(403);
}

$id = (int) ($_GET['id'] ?? 0);
if (!$id) {
    error(0);
}

$scheduler = new \Gazelle\Schedule\Scheduler;
if (!$scheduler->getTask($id)) {
    error(404);
}

define('TASKS_PER_PAGE', 100);
list($page, $limit, $offset) = \Gazelle\DB::pageLimit(TASKS_PER_PAGE);

$header = new \Gazelle\Util\SortableTableHeader('launchtime', [
    'id'         => ['defaultSort' => 'desc'],
    'launchtime' => ['defaultSort' => 'desc',  'text' => 'Launch Time'],
    'duration'   => ['defaultSort' => 'desc',  'text' => 'Duration'],
    'status'     => ['defaultSort' => 'desc',  'text' => 'Status'],
    'items'      => ['defaultSort' => 'desc',  'text' => 'Processed'],
    'errors'     => ['defaultSort' => 'desc',  'text' => 'Errors']
]);

$task = $scheduler->getTaskHistory($id, $limit, $offset, $header->getSortKey(), $header->getOrderDir());
$stats = $scheduler->getTaskRuntimeStats($id);
$canEdit = check_perms('admin_periodic_task_manage');

View::show_header('Periodic Task Details', '', 'PageToolPeriodicDetail');
?>
<div class="BodyHeader">
    <h2 class="BodyHeader-nav">Periodic Task Details - <?= $task->name ?></h2>
</div>
<?php include(__DIR__ . '/periodic_links.php');
if ($task->count > 0) { ?>
    <br />
    <div class="BoxBody">
        <div id="daily-totals" style="width: 100%; height: 350px;"></div>
    </div>
    <div class="BodyNavLinks">
        <?= Format::get_pages($page, $task->count, TASKS_PER_PAGE, 11) ?>
    </div>
    <table class="Table">
        <tr class="Table-rowHeader">
            <td class="Table-cell"><?= $header->emit('launchtime') ?> <a href="#" onclick="$('#tasks .reltime').gtoggle(); $('#tasks .abstime').gtoggle(); return false;" class="brackets">Toggle</a></td>
            <td class="Table-cell"><?= $header->emit('duration') ?></td>
            <td class="Table-cell" width="10%"><?= $header->emit('status') ?></td>
            <td class="Table-cell" width="10%"><?= $header->emit('items') ?></td>
            <td class="Table-cell" width="10%"><?= $header->emit('errors') ?></td>
        </tr>
        <?php
        foreach ($task->items as $item) {
            $item->duration .= 'ms';
        ?>
            <tr class="Table-row">
                <td class="Table-cell">
                    <span class="reltime"><?= time_diff($item->launchTime) ?></span>
                    <span class="abstime hidden"><?= $item->launchTime ?></span>
                </td>
                <td class="Table-cell"><?= $item->duration ?></td>
                <td class="Table-cell"><?= $item->status ?></td>
                <td class="Table-cell"><?= $item->numItems ?></td>
                <td class="Table-cell"><?= $item->numErrors ?></td>
            </tr>
            <?php if (count($item->events) > 0) { ?>
                <tr class="Table-row">
                    <td class="Table-cell" colspan="5">
                        <table>
                            <tr class="colhead">
                                <td>Event Time</td>
                                <td>Severity</td>
                                <td>Event</td>
                                <td>Reference</td>
                            </tr>
                            <?php
                            foreach ($item->events as $event) {
                            ?>
                                <tr>
                                    <td>
                                        <span class="reltime"><?= time_diff($event->timestamp) ?></span>
                                        <span class="abstime hidden"><?= $event->timestamp ?></span>
                                    </td>
                                    <td><?= $event->severity ?></td>
                                    <td><?= $event->event ?></td>
                                    <td><?= $event->reference ?></td>
                                </tr>
                            <?php       } ?>
                        </table>
                    </td>
                <?php   } ?>
                </tr>
            <?php } ?>
    </table>

    <script src="<?= CONFIG['STATIC_SERVER'] ?>/functions/highcharts.js"></script>
    <script src="<?= CONFIG['STATIC_SERVER'] ?>/functions/highcharts_custom.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            initialiseChart('daily-totals', 'Daily', [{
                    name: 'Duration',
                    yAxis: 0,
                    data: [<?= implode(',', $stats[0]['data']) ?>]
                },
                {
                    name: 'Processed',
                    yAxis: 1,
                    data: [<?= implode(',', $stats[1]['data']) ?>]
                }
            ], {
                yAxis: [{
                    title: {
                        text: 'Duration'
                    }
                }, {
                    title: {
                        text: 'Items'
                    },
                    opposite: true
                }]
            });
        });
    </script>
<?php
} else {
?>
    <div class="center">
        <h2>No history found</h2>
    </div>
<?php
}
View::show_footer();
