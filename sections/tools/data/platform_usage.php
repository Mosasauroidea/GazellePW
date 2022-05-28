<?php

if (!check_perms('site_view_flow')) {
    error(403);
}

View::show_header(Lang::get('tools', 'h2_os_and_browser_usage'), '', 'PageToolPlatformUsage');

?>
<div class="BodyHeader">
    <h2 class="BodyHeader-nav"><?= Lang::get('tools', 'os_usage') ?></h2>
</div>
<table class="Table">
    <tr class="Table-rowHeader">
        <td class="Table-cell"><?= Lang::get('tools', 'os') ?></td>
        <td class="Table-cell"><?= Lang::get('tools', 'count') ?></td>
    </tr>

    <?php
    G::$DB->prepared_query("SELECT OperatingSystem, OperatingSystemVersion, COUNT(*) FROM users_sessions GROUP BY OperatingSystem, OperatingSystemVersion ORDER BY COUNT(*) DESC");
    while (list($OperatingSystem, $OperatingSystemVersion, $Count) = G::$DB->fetch_record(0, 'OperatingSystem', 1, 'OperatingSystemVersion')) {
    ?>
        <tr class="Table-row">
            <td class="Table-cell"><?= $OperatingSystem ?> <?= $OperatingSystemVersion ?></td>
            <td class="Table-cell"><?= $Count ?></td>
        </tr>
    <?php
    }
    ?>
</table>
<div class="BodyHeader">
    <h2 class="BodyHeader-nav"><?= Lang::get('tools', 'browser_usage') ?></h2>
</div>
<table class="Table">
    <tr class="Table-rowHeader">
        <td class="Table-cell"><?= Lang::get('tools', 'browser') ?></td>
        <td class="Table-cell"><?= Lang::get('tools', 'count') ?></td>
    </tr>

    <?php
    G::$DB->prepared_query("SELECT Browser, BrowserVersion, COUNT(*) FROM users_sessions GROUP BY Browser, BrowserVersion ORDER BY COUNT(*) DESC");
    while (list($Browser, $BrowserVersion, $Count) = G::$DB->fetch_record(0, 'Browser', 1, 'BrowserVersion')) {
    ?>
        <tr class="Table-row">
            <td class="Table-cell"><?= $Browser ?> <?= $BrowserVersion ?></td>
            <td class="Table-cell"><?= $Count ?></td>
        </tr>
    <?php
    }
    ?>
</table>
<?php

View::show_footer();
