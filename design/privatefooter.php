</div>
<?php TEXTAREA_PREVIEW::JavaScript(); ?>
<footer class="LayoutPage-footer Footer">
    <? if (!empty($Options['disclaimer'])) { ?>
        <div id="disclaimer_container" class="thin" style="text-align: center; margin-bottom: 20px;">
            <?= t('server.pub.note') ?>
        </div>
    <?
    }
    if (count($UserSessions) > 1) {
        foreach ($UserSessions as $ThisSessionID => $Session) {
            if ($ThisSessionID != $SessionID) {
                $LastActive = $Session;
                break;
            }
        }
    }

    $Load = sys_getloadavg();
    ?>
    <p>Site and design &copy; <?= date('Y') ?> <?= CONFIG['SITE_NAME'] ?> | Powered by <a target="blank" href="https://github.com/Mosasauroidea/GazellePW">GazellePW</a></p>
    <? if (!empty($LastActive)) { ?>
        <p>
            <a href="user.php?action=sessions">
                <span data-tooltip="Manage sessions">Last activity: </span><?= time_diff($LastActive['LastUpdate']) ?><span data-tooltip="Manage sessions"> from <?= $LastActive['IP'] ?>.</span>
            </a>
        </p>
    <?  } ?>
    <p>
        <strong>Time:</strong> <span><?= number_format(((microtime(true) - $ScriptStartTime) * 1000), 5) ?> ms</span>
        <strong>Used:</strong> <span><?= Format::get_size(memory_get_usage(true)) ?></span>
        <strong>Load:</strong> <span><?= number_format($Load[0], 2) . ' ' . number_format($Load[1], 2) . ' ' . number_format($Load[2], 2) ?></span>
        <strong>Date:</strong> <span id="site_date"><?= date('M d Y') ?></span>, <span id="site_time"><?= date('H:i') ?></span>
        <? if (VERSION) { ?>
            <strong>Version:</strong> <span><?= VERSION ?></span>
        <? } ?>
    </p>
</footer>
<? if (CONFIG['DEBUG_MODE'] || check_perms('site_debug')) { ?>
    <div class="LayoutPage-siteDebug">
        <?
        $Debug->perf_table();
        $Debug->flag_table();
        $Debug->error_table();
        $Debug->sphinx_table();
        $Debug->query_table();
        $Debug->cache_table();
        $Debug->vars_table();
        $Debug->ocelot_table();
        ?>
    </div>
<? } ?>

</div>
<div id="lightbox" class="lightbox hidden"></div>
<div id="lightbox__shroud" class="lightbox__shroud hidden"></div>
<div id="curtain" class="curtain hidden"></div>
<?
global $NotificationSpans;
if (!empty($NotificationSpans)) {
    foreach ($NotificationSpans as $Notification) {
        echo "$Notification\n";
    }
}
?>
<!-- Extra divs, for stylesheet developers to add imagery -->
<div id="extra1"><span></span></div>
<div id="extra2"><span></span></div>
<div id="extra3"><span></span></div>
<div id="extra4"><span></span></div>
<div id="extra5"><span></span></div>
<div id="extra6"><span></span></div>

<script>
    Object.assign(window.DATA, <?= json_encode($GLOBALS['WINDOW_DATA']) ?>)
</script>

<script src="/deps/tooltipster.bundle.min.js"></script>
<script src="/deps/tooltipster-discovery.min.js"></script>
<script src="/deps/load-image.all.min.js"></script>
<script src="/deps/jquery-fileupload/jquery.ui.widget.js"></script>
<script src="/deps/jquery-fileupload/jquery.fileupload.js"></script>
<script src="/deps/jquery-fileupload/jquery.fileupload-process.js"></script>
<script src="/deps/jquery-fileupload/jquery.fileupload-validate.js"></script>
<script src="/deps/jquery-fileupload/jquery.fileupload-image.js"></script>

<script type="module" src="<?= vite("src/js/globalapp/index.js") ?>"></script>
<!-- The Canvas to Blob plugin is included for image resizing functionality -->
<? if ($PageJS) : ?>
    <script type="module" src="<?= vite("src/js/pages/$PageJS") ?>"></script>
<? endif; ?>

<? if (CONFIG['IS_DEV']) : ?>
    <script type="module" src="<?= vite("@vite/client") ?>"></script>
<? endif; ?>
<script type="module" src="<?= vite("src/js/app/app.jsx") ?>"></script>


</body>

</html>