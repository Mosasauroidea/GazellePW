<?php

// Note: The shell execs are operating from the root of the gazelle repo

function composer_exec($CMD) {
    // Composer won't work well through shell_exec if xdebug is enabled
    // which we might expect if CONFIG['DEBUG_MODE'] is enabled (as neither
    // xdebug or CONFIG['DEBUG_MODE'] should happen on production)
    if (isset(CONFIG['DEBUG_MODE']) && CONFIG['DEBUG_MODE'] === true) {
        $CMD = 'COMPOSER_ALLOW_XDEBUG=1 ' . $CMD;
    }
    return shell_exec($CMD);
}

if (!isset(CONFIG['DEBUG_MODE']) || CONFIG['DEBUG_MODE'] !== true) {
    if (!check_perms('site_debug')) {
        error(403);
    }
}

$Debug->set_flag('Start Git');

$GitBranch = shell_exec('git rev-parse --abbrev-ref HEAD');
$GitHash = shell_exec('git rev-parse HEAD');
$RemoteHash = shell_exec("git rev-parse origin/{$GitBranch}");
$Tag = shell_exec("git describe --tags");

$Debug->set_flag('Start Composer');

$ComposerVersion = substr(composer_exec('composer --version'), 16);

$Packages = [];

$Composer = json_decode(file_get_contents(CONFIG['SERVER_ROOT'] . '/composer.json'), true);
foreach ($Composer['require'] as $Package => $Version) {
    $Packages[$Package] = ['Name' => $Package, 'Version' => $Version];
}
$ComposerLock = json_decode(file_get_contents(CONFIG['SERVER_ROOT'] . '/composer.lock'), true);
foreach ($ComposerLock['packages'] as $Package) {
    if (isset($Packages[$Package['name']])) {
        $Packages[$Package['name']]['Locked'] = $Package['version'];
    }
}

$ComposerPackages = json_decode(composer_exec('composer info --format=json'), true);
foreach ($ComposerPackages['installed'] as $Package) {
    if (isset($Packages[$Package['name']])) {
        $Packages[$Package['name']]['Installed'] = $Package['version'];
    }
}

$Debug->set_flag('Start Phinx');
$PhinxVersion = shell_exec(CONFIG['SERVER_ROOT'] . '/vendor/bin/phinx --version');
$PhinxMigrations = array_filter(json_decode(shell_exec(CONFIG['SERVER_ROOT'] . '/vendor/bin/phinx status -c ' . CONFIG['SERVER_ROOT'] . '/phinx.php --format=json | tail -n 1'), true)['migrations'], function ($value) {
    return count($value) > 0;
});
$PHPTimeStamp = date('Y-m-d H:i:s');
$DB->query('SELECT NOW() as now;');
$DBTimeStamp = $DB->fetch_record()['now'];

$Debug->set_flag('Start phpinfo');
ob_start();
phpinfo();
$Data = ob_get_contents();
ob_end_clean();
$Data = substr($Data, strpos($Data, '<body>') + 6, strpos($Data, '</body>'));

View::show_header('Site Information', '', 'PageToolSiteInfo');
?>
<style type="text/css">
    div#phpinfo {
        color: #222;
        font-family: sans-serif;
    }

    div#phpinfo pre {
        margin: 0;
        font-family: monospace;
    }

    div#phpinfo a:link {
        color: #009;
        text-decoration: none;
        background-color: #fff;
    }

    div#phpinfo a:hover {
        text-decoration: underline;
    }

    div#phpinfo table {
        border-collapse: collapse;
        border: 0;
        width: 934px;
        box-shadow: 1px 2px 3px #ccc;
    }

    div#phpinfo .center {
        text-align: center;
    }

    div#phpinfo .center table {
        margin: 1em auto;
        text-align: left;
    }

    div#phpinfo .center th {
        text-align: center !important;
    }

    div#phpinfo td,
    th {
        border: 1px solid #666;
        font-size: 75%;
        vertical-align: baseline;
        padding: 4px 5px;
    }

    div#phpinfo h1 {
        font-size: 150%;
    }

    div#phpinfo h2 {
        font-size: 125%;
    }

    div#phpinfo .p {
        text-align: left;
    }

    div#phpinfo .e {
        background-color: #ccf;
        width: 300px;
        font-weight: bold;
    }

    div#phpinfo .h {
        background-color: #99c;
        font-weight: bold;
    }

    div#phpinfo .v {
        background-color: #ddd;
        max-width: 300px;
        overflow-x: auto;
        word-wrap: break-word;
    }

    div#phpinfo .v i {
        color: #999;
    }

    div#phpinfo img {
        float: right;
        border: 0;
    }

    div#phpinfo hr {
        width: 934px;
        background-color: #ccc;
        border: 0;
        height: 1px;
    }
</style>
<div class="LayoutBody">
    <div class="BodyHeader">
        <div class="BodyHeader-nav">
            <?= t('server.tools.site_info') ?>
        </div>
    </div>
    <div class="Group">
        <div class="Group-header">
            <div class="Group-headerTitle">
                <?= t('server.tools.timestamps') ?>
            </div>
        </div>
        <div class="Gruop-body BoxBody">
            <span style="width: 50px; display: inline-block">PHP:</span> <?= $PHPTimeStamp ?><br />
            <span style="width: 50px; display: inline-block">DB:</span> <?= $DBTimeStamp ?>
        </div>
    </div>
    <div class="Group">
        <div class="Group-header">
            <div class="Group-headerTitle">
                PHP
            </div>
        </div>
        <div class="BoxBody Group-body">
            <div>PHP Version: <?= phpversion(); ?></div>
            <div>PHP Info:
                <a href="#" onclick="globalapp.toggleAny(event, '#phpinfo');return false;">
                    <span class="u-toggleAny-show"><?= t('server.common.show') ?></span>
                    <span class="u-toggleAny-hide u-hidden"><?= t('server.common.hide') ?></span>
                </a>
            </div>
            <div class="u-hidden" id="phpinfo"><?= $Data ?></div>
        </div>
    </div>

    <div class="Group">
        <div class="Group-header">
            <div class="Group-headerTitle">
                Git
            </div>
        </div>
        <div class="Group-body BoxBody">
            <strong style="width: 150px; display: inline-block;">Branch</strong> <?= $GitBranch ?>
            <strong style="width: 150px; display: inline-block;">Local Hash</strong> <?= $GitHash ?>
            <strong style="width: 150px; display: inline-block;">Remote Hash</strong> <?= $RemoteHash ?>
            <strong style="width: 150px; display: inline-block;">Tag</strong> <?= $Tag ?>
        </div>
    </div>
    <div class="Group">
        <div class="Group-header">
            <div class="Group-headerTitle">
                <div data-tooltip="Composer Version: <?= $ComposerVersion ?>">Composer</div>
            </div>
        </div>
        <div class="Group-body">
            <table class="Table">
                <tr class="Table-rowHeader">
                    <td class="Table-cell">Package</td>
                    <td class="Table-cell">Version</td>
                    <td class="Table-cell">Installed</td>
                    <td class="Table-cell">Locked</td>
                </tr>
                <?php
                foreach ($Packages as $Package) {
                    $Installed = $Package['Installed'] ?? '';
                    $Locked = $Package['Locked'] ?? '';
                ?>
                    <tr class="Table-row">
                        <td class="Table-cell"><?= $Package['Name'] ?></td>
                        <td class="Table-cell"><?= $Package['Version'] ?></td>
                        <td class="Table-cell"><?= $Installed ?></td>
                        <td class="Table-cell"><?= $Locked ?></td>
                    </tr>
                <?php
                }
                ?>
            </table>
        </div>
    </div>
    <div class="Group">
        <div class="Group-header">
            <div class="Group-headerTitle">
                <div data-tooltip="<?= $PhinxVersion ?>">Phinx</div>
            </div>
        </div>
        <div class="Group-body">
            <table class="Table">
                <tr class='Table-rowHeader'>
                    <td class="Table-cell">Status</td>
                    <td class="Table-cell">Migration ID</td>
                    <td class="Table-cell">Migration Name</td>
                </tr>
                <?php
                foreach ($PhinxMigrations as $Migration) {
                ?>
                    <tr class="Table-row">
                        <td class="Table-cell"><?= $Migration['migration_status'] ?></td>
                        <td class="Table-cell"><?= $Migration['migration_id'] ?></td>
                        <td class="Table-cell"><?= $Migration['migration_name'] ?></td>
                    </tr>
                <?php
                }
                ?>
            </table>
        </div>
    </div>
</div>
<?php

View::show_footer();
