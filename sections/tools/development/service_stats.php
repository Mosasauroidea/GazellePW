<?
if (!check_perms('site_debug') || !check_perms('admin_clear_cache')) {
    error(403);
}


$DB->query('SHOW GLOBAL STATUS');
$DBStats = G::$DB->to_array('Variable_name');
$MemStats = G::$Cache->getStats();

// index 0
$MemStats = $MemStats[array_keys($MemStats)[0]];

View::show_header(t('server.tools.service_stats'), '', 'PageToolSericeStat');
?>
<div class="LayoutPage">
    <div class="BodyHeader">
        <div class="BodyHeader-nav"><?= t('server.tools.service_stats') ?></div>
        <div class="BodyNavLinks">
            <a href="tools.php?action=database_specifics" class="brackets"><?= t('server.tools.database_specifics') ?></a>
        </div>
    </div>
    <div class="Permissions">
        <div class="Box">
            <div class="Box-header">
                <?= t('server.tools.service') ?>
            </div>
            <div class="Box-body">
                <ul class="MenuList">
                    <li><strong><?= t('server.tools.threads_active') ?></strong>
                        <ul class="MenuList SubMenu">
                            <li>
                                <?= t('server.tools.cache') ?>: <?= number_format($MemStats['threads']) ?> <span>(100.000%)</span>
                            </li>
                            <li>
                                <?= t('server.tools.database') ?>:
                                <span class="<? if ($DBStats['Threads_connected']['Value'] / $DBStats['Threads_created']['Value'] > 0.7) {
                                                    echo 'u-colorWarning';
                                                } ?>"><?= number_format($DBStats['Threads_created']['Value']) ?> <span>(<?= number_format(($DBStats['Threads_connected']['Value'] / $DBStats['Threads_created']['Value']) * 100, 3) ?>%)</span></span>
                            </li>
                        </ul>

                    </li>
                    <li>
                        <strong><?= t('server.tools.connections') ?></strong>
                        <ul class="MenuList SubMenu">
                            <li>
                                <?= t('server.tools.cache') ?>:
                                <?= number_format($MemStats['total_connections']) ?>
                            </li>
                            <li>
                                <?= t('server.tools.database') ?>:
                                <?= number_format($DBStats['Connections']['Value']) ?>
                            </li>
                        </ul>
                    </li>
                    <li>
                        <strong><?= t('server.tools.cache_usage') ?></strong>
                        <ul class="MenuList SubMenu">
                            <li>
                                <?= t('server.tools.cashe_current_index') ?>:
                                <?= number_format($MemStats['curr_items']) ?></span>
                            </li>
                            <li>
                                <?= t('server.tools.cashe_total_index') ?>:
                                <?= number_format($MemStats['total_items']) ?></span>
                            </li>
                            <li>
                                <?= t('server.tools.cashe_storage') ?>:
                                <span class="<?= ($MemStats['bytes'] / $MemStats['limit_maxbytes'] > 0.85 ? 'u-colorWarning' : '') ?>" data-tooltip="<?= ($MemStats['bytes'] / $MemStats['limit_maxbytes'] > 0.85 ? t('server.tools.cashe_storage_title') : '') ?>">
                                    <?= Format::get_size($MemStats['bytes']) ?> <span>(<?= number_format(($MemStats['bytes'] / $MemStats['limit_maxbytes']) * 100, 3); ?>%)</span>
                                </span>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
        <div class="Box">
            <div class="Box-header">
                <?= t('server.tools.activity') ?>
            </div>
            <div class="Box-body">
                <ul class="MenuList">
                    <li><strong><?= t('server.tools.total_reads') ?></strong>
                        <ul class="MenuList SubMenu">
                            <li>
                                <?= t('server.tools.cache') ?>:
                                <?= number_format($MemStats['cmd_get']) ?>
                            </li>
                            <li>
                                <?= t('server.tools.database') ?>:
                                <?= number_format($DBStats['Com_select']['Value']) ?>
                            </li>
                        </ul>
                    </li>
                    <li><strong><?= t('server.tools.total_writes') ?></strong>
                        <ul class="MenuList SubMenu">
                            <li>
                                <?= t('server.tools.cache') ?>:
                                <?= number_format($MemStats['cmd_set']) ?>
                            </li>
                            <li>
                                <?= t('server.tools.database') ?>:
                                <?= number_format($DBStats['Com_insert']['Value'] + $DBStats['Com_update']['Value']) ?>
                            </li>
                        </ul>
                    </li>
                    <li><strong><?= t('server.tools.get_select_success') ?></strong>
                        <ul class="MenuList SubMenu">
                            <li>
                                <span data-tooltip="<?= t('server.tools.cache_hit_rate') ?>"><?= t('server.tools.cache') ?>:</span>
                                <span class="<?= $MemStats['get_hits'] / $MemStats['cmd_get'] < 0.7 ? "u-colorWarning" : "" ?>"><?= number_format($MemStats['get_hits']) ?> <span>(<?= number_format(($MemStats['get_hits'] / $MemStats['cmd_get']) * 100, 3); ?>%)</span></span>
                            </li>
                            <li>
                                <?= t('server.tools.database') ?>:
                                <?= number_format($DBStats['Com_select']['Value']) ?> <span>(100.000%)</span>
                            </li>
                        </ul>
                    </li>
                    <li><strong><?= t('server.tools.set_insert_success') ?></strong>
                        <ul class="MenuList SubMenu">
                            <li>
                                <span><?= t('server.tools.cache') ?>:</span>
                                <?= number_format($MemStats['cmd_set']) ?> <span>(100.000%)</span>
                            </li>
                            <li>
                                <?= t('server.tools.database') ?>:
                                <?= number_format($DBStats['Com_insert']['Value']) ?> <span>(100.000%)</span>
                            </li>
                        </ul>
                    </li>
                    <li><strong><?= t('server.tools.increment_decrement_success') ?></strong>
                        <ul class="MenuList SubMenu">
                            <li>
                                <span><?= t('server.tools.cache_increment') ?>:</span>
                                <span class="<?= $MemStats['incr_hits'] / ($MemStats['incr_hits'] + $MemStats['incr_misses']) < 0.7 ? "u-colorWarning" : "" ?>"><?= number_format($MemStats['incr_hits']) ?> <span>(<?= number_format(($MemStats['incr_hits'] / ($MemStats['incr_hits'] + $MemStats['incr_misses'])) * 100, 3); ?>%)</span></span>
                            </li>
                            <li>
                                <?= t('server.tools.cache_decrement') ?>:
                                <span class="<?= $MemStats['decr_hits'] / ($MemStats['decr_hits'] + $MemStats['decr_misses']) < 0.7 ? "u-colorWarning" : "" ?>"><?= number_format($MemStats['decr_hits']) ?> <span>(<?= number_format(($MemStats['decr_hits'] / ($MemStats['decr_hits'] + $MemStats['decr_misses'])) * 100, 3); ?>%)</span><span>
                            </li>
                        </ul>
                    </li>
                    <li><strong><?= t('server.tools.cas_update_success') ?></strong>:
                        <ul class="MenuList SubMenu">
                            <li>
                                <span><?= t('server.tools.cache') ?>:</span>
                                <span class="<? if ($MemStats['cas_hits'] > 0 && $MemStats['cas_hits'] / ($MemStats['cas_hits'] + $MemStats['cas_misses']) < 0.7) {
                                                    echo 'u-colorWarning" data-tooltip="' . t('server.tools.cas_update_success_title_1') . '" ';
                                                } elseif ($MemStats['cas_hits'] == 0) {
                                                    echo 'u-colorWarning" data-tooltip="' . t('server.tools.cas_update_success_title_2') . '" ';
                                                } ?>"><?= number_format($MemStats['cas_hits']) ?> <span>(<? if ($MemStats['cas_hits'] > 0) {
                                                                                                                echo number_format(($MemStats['cas_hits'] / ($MemStats['cas_hits'] + $MemStats['cas_misses'])) * 100, 3);
                                                                                                            } else {
                                                                                                                echo '0.000';
                                                                                                            } ?>%)</span><span>
                            </li>
                            <li>
                                <span><?= t('server.tools.database') ?>:</span>
                                <span><?= number_format($DBStats['Com_update']['Value']) ?> <span>(100.000%)</span> </span>
                            </li>
                        </ul>
                    </li>
                    <li><strong><?= t('server.tools.delete_success') ?></strong>
                        <ul class="MenuList SubMenu">
                            <li>
                                <span><?= t('server.tools.cache') ?>:</span>
                                <span class="<? if ($MemStats['delete_hits'] / ($MemStats['delete_hits'] + $MemStats['delete_misses']) < 0.7) {
                                                    echo 'u-colorWarning" data-tooltip="' . t('server.tools.delete_success_title') . '"';
                                                } ?>"><?= number_format($MemStats['delete_hits']) ?> <span>(<?= number_format(($MemStats['delete_hits'] / ($MemStats['delete_hits'] + $MemStats['delete_misses'])) * 100, 3); ?>%)</span></span>
                            </li>
                            <li>
                                <span><?= t('server.tools.database') ?>:</span>
                                <?= number_format($DBStats['Com_delete']['Value']) ?> <span>(100.000%)</span>
                            </li>
                        </ul>
                    </li>

                    <li><strong><?= t('server.tools.data_read') ?></strong>
                        <ul class="MenuList SubMenu">
                            <li>
                                <span><?= t('server.tools.cache') ?>:</span>
                                <span><?= Format::get_size($MemStats['bytes_read']) ?> </span>
                            </li>
                            <li>
                                <span><?= t('server.tools.database') ?>:</span>
                                <span><?= Format::get_size($DBStats['Bytes_received']['Value']) ?></span>
                            </li>
                        </ul>
                    </li>
                    <li><strong><?= t('server.tools.data_write') ?></strong>
                        <ul class="MenuList SubMenu">
                            <li>
                                <span><?= t('server.tools.cache') ?>:</span>
                                <span><?= Format::get_size($MemStats['bytes_written']) ?> </span>
                            </li>
                            <li>
                                <span><?= t('server.tools.database') ?>:</span>
                                <span><?= Format::get_size($DBStats['Bytes_sent']['Value']) ?></span>
                            </li>
                        </ul>
                    </li>
                    <li><strong><?= t('server.tools.others') ?></strong>
                        <ul class="MenuList SubMenu">
                            <li>
                                <span><?= t('server.tools.cache_flushes') ?>:</span>
                                <span class="Table-cell <? if ($MemStats['cmd_flush'] > $MemStats['uptime'] / 7 * 24 * 3600) {
                                                            echo 'u-colorWarning" data-tooltip="' . t('server.tools.cache_flushes_title') . '" ';
                                                        } ?>"><?= number_format($MemStats['cmd_flush']) ?></span>
                            </li>
                            <li>
                                <span><?= t('server.tools.cache_evicted') ?>:</span>
                                <span class="<?= $MemStats['evictions'] > 0 ? "u-colorWarning" : "" ?>"><?= number_format($MemStats['evictions']) ?></span>
                            </li>
                            <li>
                                <?= t('server.tools.database_slow') ?>:
                                <span class="<? if ($DBStats['Slow_queries']['Value'] > $DBStats['Questions']['Value'] / 7500) {
                                                    echo 'u-colorWarning" data-tooltip="' . t('server.tools.database_slow_title') . '" ';
                                                } ?>"><?= number_format($DBStats['Slow_queries']['Value']) ?></span>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
        <div class="Box">
            <div class="Box-header">
                <?= t('server.tools.concurrency') ?>
            </div>
            <div class="Box-body">
                <ul class="MenuList">
                    <li><strong><?= t('server.tools.total_reads') ?></strong>
                        <ul class="MenuList SubMenu">
                            <li>
                                <span><?= t('server.tools.cache') ?>:</span>
                                <span class="<? if (($MemStats['cmd_get'] / $MemStats['uptime']) * 5 < $DBStats['Com_select']['Value'] / $DBStats['Uptime']['Value']) {
                                                    echo 'u-colorWarning';
                                                } ?>"><?= number_format($MemStats['cmd_get'] / $MemStats['uptime'], 5) ?>/s </span>
                            </li>
                            <li>
                                <span><?= t('server.tools.database') ?>:</span>
                                <span><?= number_format($DBStats['Com_select']['Value'] / $DBStats['Uptime']['Value'], 5) ?>/s</span>
                            </li>
                        </ul>
                    </li>
                    <li><strong><?= t('server.tools.total_writes') ?></strong>
                        <ul class="MenuList SubMenu">
                            <li>
                                <span><?= t('server.tools.cache') ?>:</span>
                                <span class="<? if (($MemStats['cmd_set'] / $MemStats['uptime']) * 5 < ($DBStats['Com_insert']['Value'] + $DBStats['Com_update']['Value']) / $DBStats['Uptime']['Value']) {
                                                    echo 'u-colorWarning';
                                                } ?>"><?= number_format($MemStats['cmd_set'] / $MemStats['uptime'], 5) ?>/s </span>
                            </li>
                            <li>
                                <span><?= t('server.tools.database') ?>:</span>
                                <span><?= number_format(($DBStats['Com_insert']['Value'] + $DBStats['Com_update']['Value']) / $DBStats['Uptime']['Value'], 5) ?>/s</span>
                            </li>
                        </ul>
                    </li>
                    <li><strong><?= t('server.tools.get_select') ?></strong>
                        <ul class="MenuList SubMenu">
                            <li>
                                <span><?= t('server.tools.cache') ?>:</span>
                                <span><?= number_format($MemStats['get_hits'] / $MemStats['uptime'], 5) ?>/s </span>
                            </li>
                            <li>
                                <span><?= t('server.tools.database') ?>:</span>
                                <span><?= number_format($DBStats['Com_select']['Value'] / $DBStats['Uptime']['Value'], 5) ?>/s</span>
                            </li>
                        </ul>
                    </li>
                    <li><strong><?= t('server.tools.set_insert') ?></strong>
                        <ul class="MenuList SubMenu">
                            <li>
                                <span><?= t('server.tools.cache') ?>:</span>
                                <span><?= number_format($MemStats['cmd_set'] / $MemStats['uptime'], 5) ?>/s </span>
                            </li>
                            <li>
                                <span><?= t('server.tools.database') ?>:</span>
                                <span><?= number_format($DBStats['Com_insert']['Value'] / $DBStats['Uptime']['Value'], 5) ?>/s</span>
                            </li>
                        </ul>
                    </li>
                    <li><strong><?= t('server.tools.increment_decrement_success') ?></strong>
                        <ul class="MenuList SubMenu">
                            <li>
                                <span><?= t('server.tools.cache_increment') ?>:</span>
                                <span><?= number_format($MemStats['incr_hits'] / $MemStats['uptime'], 5) ?>/s</span>
                            </li>
                            <li>
                                <span><?= t('server.tools.cache_decrement') ?>:</span>
                                <span><?= number_format($MemStats['decr_hits'] / $MemStats['uptime'], 5) ?>/s</span>
                            </li>
                        </ul>
                    </li>
                    <li><strong><?= t('server.tools.cas_updates') ?></strong>
                        <ul class="MenuList SubMenu">
                            <li>
                                <span><?= t('server.tools.cache') ?>:</span>
                                <span><?= number_format($MemStats['cas_hits'] / $MemStats['uptime'], 5) ?>/s</span>
                            </li>
                            <li>
                                <span><?= t('server.tools.database') ?>:</span>
                                <span><?= number_format($DBStats['Com_update']['Value'] / $DBStats['Uptime']['Value'], 5) ?>/s</span>
                            </li>
                        </ul>
                    </li>
                    <li><strong><?= t('server.tools.delete_success') ?></strong>
                        <ul class="MenuList SubMenu">
                            <li>
                                <span><?= t('server.tools.cache') ?>:</span>
                                <span><?= number_format($MemStats['delete_hits'] / $MemStats['uptime'], 5) ?>/s</span>
                            </li>
                            <li>
                                <span><?= t('server.tools.database') ?>:</span>
                                <span><?= number_format($DBStats['Com_delete']['Value'] / $DBStats['Uptime']['Value'], 5) ?>/s</span>
                            </li>
                        </ul>
                    </li>
                    <li><strong><?= t('server.tools.others') ?></strong>
                        <ul class="MenuList SubMenu">
                            <li>
                                <span><?= t('server.tools.cache_flushes') ?>:</span>
                                <span><?= number_format($MemStats['cmd_flush'] / $MemStats['uptime'], 5) ?>/s</span>
                            </li>
                            <li>
                                <span><?= t('server.tools.cache_evicted') ?>:</span>
                                <span><?= number_format($MemStats['evictions'] / $MemStats['uptime'], 5) ?>/s</span>
                            </li>
                            <li>
                                <span><?= t('server.tools.database_slow') ?>:</span>
                                <span><?= number_format($DBStats['Slow_queries']['Value'] / $DBStats['Uptime']['Value'], 5) ?>/s</span>
                            </li>
                        </ul>
                    </li>
                    <li><strong><?= t('server.tools.data_read') ?></strong>
                        <ul class="MenuList SubMenu">
                            <li>
                                <span><?= t('server.tools.cache') ?>:</span>
                                <span><?= Format::get_size($MemStats['bytes_read'] / $MemStats['uptime']) ?>/s</span>
                            </li>
                            <li>
                                <span><?= t('server.tools.database') ?>:</span>
                                <span><?= Format::get_size($DBStats['Bytes_received']['Value'] / $DBStats['Uptime']['Value']) ?>/s</span>
                            </li>
                        </ul>
                    </li>
                    <li><strong><?= t('server.tools.data_write') ?></strong>
                        <ul class="MenuList SubMenu">
                            <li>
                                <span><?= t('server.tools.cache') ?>:</span>
                                <span><?= Format::get_size($MemStats['bytes_written'] / $MemStats['uptime']) ?>/s</span>
                            </li>
                            <li>
                                <span><?= t('server.tools.database') ?>:</span>
                                <span><?= Format::get_size($DBStats['Bytes_sent']['Value'] / $DBStats['Uptime']['Value']) ?>/s</span>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
<? View::show_footer(); ?>