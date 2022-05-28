<?
if (!check_perms('site_debug')) {
    error(403);
}
View::show_header('PHP Processes', '', 'PageToolProcessInfo');
$PIDList = trim(`ps -C php-fpm -o pid --no-header`);
$PIDs = explode("\n", $PIDList);
$Debug->log_var($PIDList, 'PID list');
$Debug->log_var($PIDs, 'PIDs');
?>
<div class="LayoutBody">
    <table class="TableProcessIinfo Table">
        <tr class="Table-rowHeader">
            <td class="Table-cell" colspan="2">
                <?= count($PIDs) . ' processes' ?>
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
