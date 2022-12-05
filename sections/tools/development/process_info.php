<?
if (!check_perms('site_debug')) {
    error(403);
}
View::show_header(t('server.tools.php_processes'), '', 'PageToolProcessInfo');

// From Opsnet/Gazelle
preg_match('/.*\/(.*)/', PHP_BINARY, $match, PREG_UNMATCHED_AS_NULL);
$binary = $match[1] ?? 'php-fpm';
$PIDList = trim(`ps -C $binary -o pid --no-header`);

$PIDs = explode("\n", $PIDList);
$Debug->log_var($PIDList, 'PID list');
$Debug->log_var($PIDs, 'PIDs');
?>
<div class="LayoutBody">
    <div class="BodyHeader">
        <h2 class="BodyHeader-nav"><?= t('server.tools.php_processes') ?></h2>
    </div>
    <?= count($PIDs) . ' processes' ?>
    <table class="TableProcessIinfo Table">
        <tr class="Table-rowHeader">
            <td class="Table-cellHeader">
                PID
            </td>
            <td class="Table-cellHeader">
                Info
            </td>
        </tr>
        <?
        foreach ($PIDs as $PID) {
            $PID = trim($PID);
            if (!$ProcessInfo = $Cache->get_value("php_$PID")) {
                continue;
            }
        ?>
            <tr class="Table-row">
                <td class="Table-cell">
                    <?= $PID ?>
                </td>
                <td class="Table-cell">
                    <pre><? print_r($ProcessInfo) ?></pre>
                </td>
            </tr>
        <? } ?>
    </table>
</div>
<?
View::show_footer();
