<?
if (!check_perms('site_debug') || !check_perms('admin_clear_cache')) {
    error(403);
}
if (isset($_POST['global_flush'])) {
    authorize();
    G::$Cache->flush();
}
if (isset($_POST['news_flush'])) {
    authorize();
    G::$Cache->delete_value('news');
    G::$Cache->delete_value('news_latest_id');
    G::$Cache->delete_value('news_latest_title');
    NotificationsManager::send_push(NotificationsManager::get_push_enabled_users(), site_url() . 'index.php', NotificationsManager::NEWS);
}
if (isset($_POST['news_flush_lite'])) {
    authorize();
    $Cache->delete_value('news');
    $Cache->delete_value('feed_news');
}
$DB->query('SHOW GLOBAL STATUS');
$DBStats = $DB->to_array('Variable_name');
$MemStats = $Cache->getStats();

View::show_header(Lang::get('tools', 'service_stats'), '', 'PageToolSericeStat');
?>
<div class="BodyNavLinks">
    <a href="tools.php?action=database_specifics" class="brackets"><?= Lang::get('tools', 'database_specifics') ?></a>
</div>
<div class="permissions">
    <div class="TableContainer">
        <table class="TablePermission Table">
            <tr class="Table-rowHeader">
                <td class="Table-cell" colspan="2"><?= Lang::get('tools', 'service') ?></td>
            </tr>
            <tr class="Table-row">
                <td class="Table-cell" colspan="2"><strong><?= Lang::get('tools', 'threads_active') ?></strong></td>
            </tr>
            <tr class="Table-row">
                <td class="Table-cell"><?= Lang::get('tools', 'cache') ?>:</td>
                <td class="Table-cell"><?= number_format($MemStats['threads']) ?> <span style="float: right;">(100.000%)</span></td>
            </tr>
            <tr class="Table-row">
                <td class="Table-cell <? if ($DBStats['Threads_connected']['Value'] / $DBStats['Threads_created']['Value'] > 0.7) {
                                            echo 'invalid';
                                        } ?>">
                    <?= Lang::get('tools', 'database') ?>:</td>
                <td class="Table-cell"><?= number_format($DBStats['Threads_created']['Value']) ?> <span style="float: right;">(<?= number_format(($DBStats['Threads_connected']['Value'] / $DBStats['Threads_created']['Value']) * 100, 3) ?>%)</span></td>
            </tr>
            <tr class="Table-row">
                <td class="Table-cell" colspan="2"></td>
            </tr>
            <tr class="Table-row">
                <td class="Table-cell" colspan="2"><strong><?= Lang::get('tools', 'connections') ?></strong></td>
            </tr>
            <tr class="Table-row">
                <td class="Table-cell"><?= Lang::get('tools', 'cache') ?>:</td>
                <td class="Table-cell"><?= number_format($MemStats['total_connections']) ?></td>
            </tr>
            <tr class="Table-row">
                <td class="Table-cell"><?= Lang::get('tools', 'database') ?>:</td>
                <td class="Table-cell"><?= number_format($DBStats['Connections']['Value']) ?></td>
            </tr>
            <tr class="Table-row">
                <td class="Table-cell" colspan="2"></td>
            </tr>
            <tr class="Table-row">
                <td class="Table-cell" colspan="2"><strong><?= Lang::get('tools', 'special') ?></strong></td>
            </tr>
            <tr class="Table-row">
                <td class="Table-cell"><?= Lang::get('tools', 'cashe_current_index') ?>:</td>
                <td class="Table-cell"><?= number_format($MemStats['curr_items']) ?></span></td>
            </tr>
            <tr class="Table-row">
                <td class="Table-cell"><?= Lang::get('tools', 'cashe_total_index') ?>:</td>
                <td class="Table-cell"><?= number_format($MemStats['total_items']) ?></span></td>
            </tr>
            <tr class="Table-row">
                <td class="Table-cell <? if ($MemStats['bytes'] / $MemStats['limit_maxbytes'] > 0.85) {
                                            echo 'tooltip invalid" data-tooltip="' . Lang::get('tools', 'cashe_storage_title') . '" ';
                                        } ?>><?= Lang::get('tools', 'cashe_storage') ?>:</td>
                    <td class=" Table-cell"><?= Format::get_size($MemStats['bytes']) ?> <span style="float: right;">(<?= number_format(($MemStats['bytes'] / $MemStats['limit_maxbytes']) * 100, 3); ?>%)</span></td>
            </tr>
            <tr class="Table-row">
                <td class="Table-cell" colspan="2"></td>
            </tr>
            <tr class="Table-row">
                <td class="Table-cell" colspan="2"><strong><?= Lang::get('tools', 'utilities') ?></strong></td>
            </tr>
            <tr class="Table-row">
                <td class="Table-cell"><?= Lang::get('tools', 'global_cache') ?>:</td>
                <td class="Table-cell">
                    <form class="delete_form" name="cache" action="" method="post">
                        <input type="hidden" name="action" value="service_stats" />
                        <input type="hidden" name="auth" value="<?= $LoggedUser['AuthKey'] ?>" />
                        <input type="hidden" name="global_flush" value="1" />
                        <input class="Button" type="submit" value="全局刷新" />
                    </form>
                </td>
            </tr>
            <tr>
                <td class="Table-cell"><?= Lang::get('tools', 'publish_a_new_announcement') ?></td>
                <td class="Table-cell">
                    <form class="delete_form" name="cache" action="" method="post">
                        <input type="hidden" name="action" value="service_stats" />
                        <input type="hidden" name="auth" value="<?= $LoggedUser['AuthKey'] ?>" />
                        <input type="hidden" name="news_flush" value="1" />
                        <input class="Button" type="submit" value="刷新" />
                    </form>
                </td>
            </tr>
            <tr>
                <td class="Table-cell"><?= Lang::get('tools', 'edit_an_announcement') ?></td>
                <td class="Table-cell">
                    <form class="delete_form" name="cache" action="" method="post">
                        <input type="hidden" name="action" value="service_stats" />
                        <input type="hidden" name="auth" value="<?= $LoggedUser['AuthKey'] ?>" />
                        <input type="hidden" name="news_flush_lite" value="1" />
                        <input class="Button" type="submit" value="刷新" />
                    </form>
                </td>
            </tr>

        </table>
    </div>
    <div class="TableContainer">
        <table class="TablePermission Table">
            <tr class="Table-rowHeader">
                <td class="Table-cell" colspan="2"><?= Lang::get('tools', 'activity') ?></td>
            </tr>
            <tr class="Table-row">
                <td class="Table-cell" colspan="2"><strong><?= Lang::get('tools', 'total_reads') ?></strong></td>
            </tr>
            <tr class="Table-row">
                <td class="Table-cell"><?= Lang::get('tools', 'cache') ?>:</td>
                <td class="Table-cell"><?= number_format($MemStats['cmd_get']) ?></td>
            </tr>
            <tr class="Table-row">
                <td class="Table-cell"><?= Lang::get('tools', 'database') ?>:</td>
                <td class="Table-cell"><?= number_format($DBStats['Com_select']['Value']) ?></td>
            </tr>
            <tr class="Table-row">
                <td class="Table-cell" colspan="2"><strong><?= Lang::get('tools', 'total_writes') ?></strong></td>
            </tr>
            <tr class="Table-row">
                <td class="Table-cell"><?= Lang::get('tools', 'cache') ?>:</td>
                <td class="Table-cell"><?= number_format($MemStats['cmd_set']) ?></td>
            </tr>
            <tr class="Table-row">
                <td class="Table-cell"><?= Lang::get('tools', 'database') ?>:</td>
                <td class="Table-cell"><?= number_format($DBStats['Com_insert']['Value'] + $DBStats['Com_update']['Value']) ?></td>
            </tr>
            <tr class="Table-row">
                <td class="Table-cell" colspan="2"></td>
            </tr>
            <tr class="Table-row">
                <td class="Table-cell" colspan="2"><strong><?= Lang::get('tools', 'get_select_success') ?></strong></td>
            </tr>
            <tr class="Table-row">
                <td class="Table-cell <? if ($MemStats['get_hits'] / $MemStats['cmd_get'] < 0.7) {
                                            echo 'invalid';
                                        } ?>"><?= Lang::get('tools', 'cache') ?>:</td>
                <td class="Table-cell"><?= number_format($MemStats['get_hits']) ?> <span style="float: right;">(<?= number_format(($MemStats['get_hits'] / $MemStats['cmd_get']) * 100, 3); ?>%)</span></td>
            </tr>
            <tr class="Table-row">
                <td class="Table-cell"><?= Lang::get('tools', 'database') ?>:</td>
                <td class="Table-cell"><?= number_format($DBStats['Com_select']['Value']) ?> <span style="float: right;">(100.000%)</span></td>
            </tr>
            <tr class="Table-row">
                <td class="Table-cell" colspan="2"><strong><?= Lang::get('tools', 'set_insert_success') ?></strong></td>
            </tr>
            <tr class="Table-row">
                <td class="Table-cell"><?= Lang::get('tools', 'cache') ?>:</td>
                <td class="Table-cell"><?= number_format($MemStats['cmd_set']) ?> <span style="float: right;">(100.000%)</span></td>
            </tr>
            <tr class="Table-row">
                <td class="Table-cell"><?= Lang::get('tools', 'database') ?>:</td>
                <td class="Table-cell"><?= number_format($DBStats['Com_insert']['Value']) ?> <span style="float: right;">(100.000%)</span></td>
            </tr>
            <tr class="Table-row">
                <td class="Table-cell" colspan="2"><strong><?= Lang::get('tools', 'increment_decrement_success') ?></strong></td>
            </tr>
            <tr class="Table-row">
                <td class="Table-cell <? if ($MemStats['incr_hits'] / ($MemStats['incr_hits'] + $MemStats['incr_misses']) < 0.7) {
                                            echo 'invalid';
                                        } ?>"><?= Lang::get('tools', 'cache_increment') ?>:</td>
                <td class="Table-cell"><?= number_format($MemStats['incr_hits']) ?> <span style="float: right;">(<?= number_format(($MemStats['incr_hits'] / ($MemStats['incr_hits'] + $MemStats['incr_misses'])) * 100, 3); ?>%)</span></td>
            </tr>
            <tr class="Table-row">
                <td class="Table-cell <? if ($MemStats['decr_hits'] / ($MemStats['decr_hits'] + $MemStats['decr_misses']) < 0.7) {
                                            echo 'invalid';
                                        } ?>"><?= Lang::get('tools', 'cache_decrement') ?>:</td>
                <td class="Table-cell"><?= number_format($MemStats['decr_hits']) ?> <span style="float: right;">(<?= number_format(($MemStats['decr_hits'] / ($MemStats['decr_hits'] + $MemStats['decr_misses'])) * 100, 3); ?>%)</span></td>
            </tr>
            <tr class="Table-row">
                <td class="Table-cell" colspan="2"><strong><?= Lang::get('tools', 'cas_update_success') ?></strong></td>
            </tr>
            <tr class="Table-row">
                <td class="Table-cell <? if ($MemStats['cas_hits'] > 0 && $MemStats['cas_hits'] / ($MemStats['cas_hits'] + $MemStats['cas_misses']) < 0.7) {
                                            echo 'tooltip invalid" data-tooltip="' . Lang::get('tools', 'cas_update_success_title_1') . '" ';
                                        } elseif ($MemStats['cas_hits'] == 0) {
                                            echo ' class="notice" data-tooltip="' . Lang::get('tools', 'cas_update_success_title_2') . '" ';
                                        } ?>"><?= Lang::get('tools', 'cache') ?>:</td>
                <td class="Table-cell"><?= number_format($MemStats['cas_hits']) ?> <span style="float: right;">(<? if ($MemStats['cas_hits'] > 0) {
                                                                                                                    echo number_format(($MemStats['cas_hits'] / ($MemStats['cas_hits'] + $MemStats['cas_misses'])) * 100, 3);
                                                                                                                } else {
                                                                                                                    echo '0.000';
                                                                                                                } ?>%)</span></td>
            </tr>
            <tr class="Table-row">
                <td class="Table-cell"><?= Lang::get('tools', 'database') ?>:</td>
                <td class="Table-cell"><?= number_format($DBStats['Com_update']['Value']) ?> <span style="float: right;">(100.000%)</span></td>
            </tr>
            <tr class="Table-row">
                <td class="Table-cell" colspan="2"><strong><?= Lang::get('tools', 'delete_success') ?></strong></td>
            </tr>
            <tr class="Table-row">
                <td class="Table-cell <? if ($MemStats['delete_hits'] / ($MemStats['delete_hits'] + $MemStats['delete_misses']) < 0.7) {
                                            echo 'tooltip invalid" data-tooltip="' . Lang::get('tools', 'delete_success_title') . '"';
                                        } ?>"><?= Lang::get('tools', 'cache') ?>:</td>
                <td class="Table-cell"><?= number_format($MemStats['delete_hits']) ?> <span style="float: right;">(<?= number_format(($MemStats['delete_hits'] / ($MemStats['delete_hits'] + $MemStats['delete_misses'])) * 100, 3); ?>%)</span></td>
            </tr>
            <tr class="Table-row">
                <td class="Table-cell"><?= Lang::get('tools', 'database') ?>:</td>
                <td class="Table-cell"><?= number_format($DBStats['Com_delete']['Value']) ?> <span style="float: right;">(100.000%)</span></td>
            </tr>
            <tr class="Table-row">
                <td class="Table-cell" colspan="2"></td>
            </tr>
            <tr class="Table-row">
                <td class="Table-cell" colspan="2"><strong><?= Lang::get('tools', 'special') ?></strong></td>
            </tr>
            <tr class="Table-row">
                <td class="Table-cell <? if ($MemStats['cmd_flush'] > $MemStats['uptime'] / 7 * 24 * 3600) {
                                            echo 'tooltip invalid" data-tooltip="' . Lang::get('tools', 'cache_flushes_title') . '" ';
                                        } ?>"><?= Lang::get('tools', 'cache_flushes') ?>:</td>
                <td class="Table-cell"><?= number_format($MemStats['cmd_flush']) ?></td>
            </tr>
            <tr class="Table-row">
                <td class=Table-cell <? if ($MemStats['evictions'] > 0) {
                                            echo 'invalid';
                                        } ?>""><?= Lang::get('tools', 'cache_evicted') ?>:</td>
                <td class="Table-cell"><?= number_format($MemStats['evictions']) ?></td>
            </tr>
            <tr class="Table-row">
                <td class="Table-cell <? if ($DBStats['Slow_queries']['Value'] > $DBStats['Questions']['Value'] / 7500) {
                                            echo 'tooltip invalid" data-tooltip="' . Lang::get('tools', 'database_slow_title') . '" ';
                                        } ?>"><?= Lang::get('tools', 'database_slow') ?>:</td>
                <td class="Table-cell"><?= number_format($DBStats['Slow_queries']['Value']) ?></td>
            </tr>
            <tr class="Table-row">
                <td class="Table-cell" colspan="2"></td>
            </tr>
            <tr class="Table-row">
                <td class="Table-cell" colspan="2"><strong><?= Lang::get('tools', 'data_read') ?></strong></td>
            </tr>
            <tr class="Table-row">
                <td class="Table-cell"><?= Lang::get('tools', 'cache') ?>:</td>
                <td class="Table-cell"><?= Format::get_size($MemStats['bytes_read']) ?></td>
            </tr>
            <tr class="Table-row">
                <td class="Table-cell"><?= Lang::get('tools', 'database') ?>:</td>
                <td class="Table-cell"><?= Format::get_size($DBStats['Bytes_received']['Value']) ?></td>
            </tr>
            <tr class="Table-row">
                <td class="Table-cell" colspan="2"><strong><?= Lang::get('tools', 'data_write') ?></strong></td>
            </tr>
            <tr class="Table-row">
                <td class="Table-cell"><?= Lang::get('tools', 'cache') ?>:</td>
                <td class="Table-cell"><?= Format::get_size($MemStats['bytes_written']) ?></td>
            </tr>
            <tr class="Table-row">
                <td class="Table-cell"><?= Lang::get('tools', 'database') ?>:</td>
                <td class="Table-cell"><?= Format::get_size($DBStats['Bytes_sent']['Value']) ?></td>
            </tr>
        </table>
    </div>
    <div class="TableContainer">
        <table class="TablePermission Table">
            <tr class="Table-rowHeader">
                <td class="Table-cell" colspan="2"><?= Lang::get('tools', 'concurrency') ?></td>
            </tr>
            <tr>
                <td class="Table-cell" colspan="2"><strong><?= Lang::get('tools', 'total_reads') ?></strong></td>
            </tr>
            <tr class="Table-row">
                <td class="Table-cell <? if (($MemStats['cmd_get'] / $MemStats['uptime']) * 5 < $DBStats['Com_select']['Value'] / $DBStats['Uptime']['Value']) {
                                            echo 'invalid';
                                        } ?>"><?= Lang::get('tools', 'cache') ?>:</td>
                <td class="Table-cell"><?= number_format($MemStats['cmd_get'] / $MemStats['uptime'], 5) ?>/s</td>
            </tr>
            <tr class="Table-row">
                <td class="Table-cell"><?= Lang::get('tools', 'database') ?>:</td>
                <td class="Table-cell"><?= number_format($DBStats['Com_select']['Value'] / $DBStats['Uptime']['Value'], 5) ?>/s</td>
            </tr>
            <tr class="Table-row">
                <td class="Table-cell" colspan="2"><strong><?= Lang::get('tools', 'total_writes') ?></strong></td>
            </tr>
            <tr class="Table-row">
                <td class="Table-cell <? if (($MemStats['cmd_set'] / $MemStats['uptime']) * 5 < ($DBStats['Com_insert']['Value'] + $DBStats['Com_update']['Value']) / $DBStats['Uptime']['Value']) {
                                            echo 'invalid';
                                        } ?>"><?= Lang::get('tools', 'cache') ?>:</td>
                <td class="Table-cell"><?= number_format($MemStats['cmd_set'] / $MemStats['uptime'], 5) ?>/s</td>
            </tr>
            <tr class="Table-row">
                <td class="Table-cell"><?= Lang::get('tools', 'database') ?>:</td>
                <td class="Table-cell"><?= number_format(($DBStats['Com_insert']['Value'] + $DBStats['Com_update']['Value']) / $DBStats['Uptime']['Value'], 5) ?>/s</td>
            </tr>
            <tr class="Table-row">
                <td class="Table-cell" colspan="2"></td>
            </tr>
            <tr class="Table-row">
                <td class="Table-cell" colspan="2"><strong><?= Lang::get('tools', 'get_select') ?></strong></td>
            </tr>
            <tr class="Table-row">
                <td class="Table-cell"><?= Lang::get('tools', 'cache') ?>:</td>
                <td class="Table-cell"><?= number_format($MemStats['get_hits'] / $MemStats['uptime'], 5) ?>/s</td>
            </tr>
            <tr class="Table-row">
                <td class="Table-cell"><?= Lang::get('tools', 'database') ?>:</td>
                <td class="Table-cell"><?= number_format($DBStats['Com_select']['Value'] / $DBStats['Uptime']['Value'], 5) ?>/s</td>
            </tr>
            <tr class="Table-row">
                <td class="Table-cell" colspan="2"><strong><?= Lang::get('tools', 'set_insert') ?></strong></td>
            </tr>
            <tr class="Table-row">
                <td class="Table-cell"><?= Lang::get('tools', 'cache') ?>:</td>
                <td class="Table-cell"><?= number_format($MemStats['cmd_set'] / $MemStats['uptime'], 5) ?>/s</td>
            </tr>
            <tr class="Table-row">
                <td class="Table-cell"><?= Lang::get('tools', 'database') ?>:</td>
                <td class="Table-cell"><?= number_format($DBStats['Com_insert']['Value'] / $DBStats['Uptime']['Value'], 5) ?>/s</td>
            </tr>
            <tr class="Table-row">
                <td class="Table-cell" colspan="2"><strong><?= Lang::get('tools', 'increment_decrement') ?></strong></td>
            </tr>
            <tr class="Table-row">
                <td class="Table-cell"><?= Lang::get('tools', 'cache_increment') ?>:</td>
                <td class="Table-cell"><?= number_format($MemStats['incr_hits'] / $MemStats['uptime'], 5) ?>/s</td>
            </tr>
            <tr class="Table-row">
                <td class="Table-cell"><?= Lang::get('tools', 'cache_decrement') ?>:</td>
                <td class="Table-cell"><?= number_format($MemStats['decr_hits'] / $MemStats['uptime'], 5) ?>/s</td>
            </tr>
            <tr class="Table-row">
                <td class="Table-cell" colspan="2"><strong><?= Lang::get('tools', 'cas_updates') ?></strong></td>
            </tr>
            <tr class="Table-row">
                <td class="Table-cell"><?= Lang::get('tools', 'cache') ?>:</td>
                <td class="Table-cell"><?= number_format($MemStats['cas_hits'] / $MemStats['uptime'], 5) ?>/s</td>
            </tr>
            <tr class="Table-row">
                <td class="Table-cell"><?= Lang::get('tools', 'database') ?>:</td>
                <td class="Table-cell"><?= number_format($DBStats['Com_update']['Value'] / $DBStats['Uptime']['Value'], 5) ?>/s</td>
            </tr>
            <tr class="Table-row">
                <td class="Table-cell" colspan="2"><strong><?= Lang::get('tools', 'deletes') ?></strong></td>
            </tr>
            <tr class="Table-row">
                <td class="Table-cell"><?= Lang::get('tools', 'cache') ?>:</td>
                <td class="Table-cell"><?= number_format($MemStats['delete_hits'] / $MemStats['uptime'], 5) ?>/s</td>
            </tr>
            <tr class="Table-row">
                <td class="Table-cell"><?= Lang::get('tools', 'database') ?>:</td>
                <td class="Table-cell"><?= number_format($DBStats['Com_delete']['Value'] / $DBStats['Uptime']['Value'], 5) ?>/s</td>
            </tr>
            <tr class="Table-row">
                <td class="Table-cell" colspan="2"></td>
            </tr>
            <tr class="Table-row">
                <td class="Table-cell" colspan="2"><strong><?= Lang::get('tools', 'special') ?></strong></td>
            </tr>
            <tr class="Table-row">
                <td class="Table-cell"><?= Lang::get('tools', 'cache_flushes') ?>:</td>
                <td class="Table-cell"><?= number_format($MemStats['cmd_flush'] / $MemStats['uptime'], 5) ?>/s</td>
            </tr>
            <tr class="Table-row">
                <td class="Table-cell"><?= Lang::get('tools', 'cache_evicted') ?>:</td>
                <td class="Table-cell"><?= number_format($MemStats['evictions'] / $MemStats['uptime'], 5) ?>/s</td>
            </tr>
            <tr class="Table-row">
                <td class="Table-cell"><?= Lang::get('tools', 'database_slow') ?>:</td>
                <td class="Table-cell"><?= number_format($DBStats['Slow_queries']['Value'] / $DBStats['Uptime']['Value'], 5) ?>/s</td>
            </tr>
            <tr class="Table-row">
                <td class="Table-cell" colspan="2"></td>
            </tr>
            <tr class="Table-row">
                <td class="Table-cell" colspan="2"><strong><?= Lang::get('tools', 'data_read') ?></strong></td>
            </tr>
            <tr class="Table-row">
                <td class="Table-cell"><?= Lang::get('tools', 'cache') ?>:</td>
                <td class="Table-cell"><?= Format::get_size($MemStats['bytes_read'] / $MemStats['uptime']) ?>/s</td>
            </tr>
            <tr class="Table-row">
                <td class="Table-cell"><?= Lang::get('tools', 'database') ?>:</td>
                <td class="Table-cell"><?= Format::get_size($DBStats['Bytes_received']['Value'] / $DBStats['Uptime']['Value']) ?>/s</td>
            </tr>
            <tr class="Table-row">
                <td class="Table-cell" colspan="2"><strong><?= Lang::get('tools', 'data_write') ?></strong></td>
            </tr>
            <tr class="Table-row">
                <td class="Table-cell"><?= Lang::get('tools', 'cache') ?>:</td>
                <td class="Table-cell"><?= Format::get_size($MemStats['bytes_written'] / $MemStats['uptime']) ?>/s</td>
            </tr>
            <tr class="Table-row">
                <td class="Table-cell"><?= Lang::get('tools', 'database') ?>:</td>
                <td><?= Format::get_size($DBStats['Bytes_sent']['Value'] / $DBStats['Uptime']['Value']) ?>/s</td>
            </tr>
        </table>
    </div>
</div>
<? View::show_footer(); ?>