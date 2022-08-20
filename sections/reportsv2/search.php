<?
if (!check_perms('admin_reports')) {
    error(403);
}

View::show_header(t('server.reportsv2.reports_v2'), 'reportsv2', 'PageReportV2Search');

$report_name_cache = [];
foreach ($ReportCategories as $label => $key) {
    foreach (array_keys($Types[$label]) as $type) {
        $report_name_cache[$type] = $Types[$label][$type]['title'] . " ($key)";
    }
}
$ReportType = [];

if (isset($_GET['report-type'])) {
    foreach ($_GET['report-type'] as $t) {
        if (array_key_exists($t, $report_name_cache)) {
            $filter['report-type'][] = $t;
        }
    }
    $ReportType = $_GET['report-type'];
}

foreach (['reporter', 'handler', 'uploader'] as $role) {
    if (isset($_GET[$role]) && preg_match('/([\w.-]+)/', $_GET[$role], $match)) {
        $filter[$role] = $match[1];
    }
}
if (isset($_GET['torrent'])) {
    if (preg_match('/^\s*(\d+)\s*$/', $_GET['torrent'], $match)) {
        $filter['torrent'] = $match[1];
    } elseif (preg_match('#^https?://[^/]+/torrents\.php.*torrentid=(\d+)#', $_GET['torrent'], $match)) {
        $filter['torrent'] = $match[1];
    }
}
if (isset($_GET['group'])) {
    if (preg_match('/^\s*(\d+)\s*$/', $_GET['group'], $match)) {
        $filter['group'] = $match[1];
    } elseif (preg_match('#^https?://[^/]+/torrents\.php.*[?&]id=(\d+)#', $_GET['group'], $match)) {
        $filter['group'] = $match[1];
    }
}
if (isset($_GET['dt-from']) && preg_match('/(\d\d\d\d-\d\d-\d\d)/', $_GET['dt-from'], $match)) {
    $filter['dt-from'] = $match[1];
    $dt_from = $match[1];
}
if (isset($_GET['dt-until']) && preg_match('/(\d\d\d\d-\d\d-\d\d)/', $_GET['dt-until'], $match)) {
    $filter['dt-until'] = $match[1];
    $dt_until = $match[1];
}
if (isset($filter)) {
    $filter['page'] = (isset($_GET['page']) && preg_match('/(\d+)/', $_GET['page'], $match))
        ? $match[1] : 1;
    list($Results, $Total) = \Gazelle\Report::search(G::$DB, $filter);
}

if (!isset($dt_from)) {
    $dt_from  = date('Y-m-d', strtotime(date('Y-m-d', strtotime(date('Y-m-d'))) . '-1 month'));
}
if (!isset($dt_until)) {
    $dt_until = date('Y-m-d');
}
?>

<div class="LayoutBody">
    <div class="BodyHeader">
        <h2 class="BodyHeader-nav"><?= t('server.reportsv2.search_reports') ?></h2>
        <? include('header.php'); ?>
    </div>
    <div class="BodyContent Box">
        <form class="Form SearchPage" method="get" action="/reportsv2.php">
            <table>
                <tr class="Form-row">
                    <td class="Form-label" width="150px"><?= t('server.reportsv2.reported_by') ?></td>
                    <td class="Form-inputs"><input class="Input is-small" type="text" name="reporter" size="20" value="<?= $_GET['reporter'] ?: '' ?>" /></td>
                </tr>
                <tr class="Form-row">
                    <td class="Form-label" width="150px"><?= t('server.reportsv2.handled_by') ?></td>
                    <td class="Form-inputs"><input class="Input is-small" type="text" name="handler" size="20" value="<?= $_GET['handler'] ?: '' ?>" /></td>
                </tr>
                <tr class="Form-row">
                    <td class="Form-label" width="150px"><?= t('server.reportsv2.uploaded_by') ?></td>
                    <td class="Form-inputs"><input class="Input is-small" type="text" name="uploader" size="20" value="<?= $_GET['uploader'] ?: '' ?>" /></td>
                </tr>
                <tr class="Form-row">
                    <td class="Form-label" width="150px"><?= t('server.reportsv2.single_torrent') ?></td>
                    <td class="Form-inputs"><input class="Input" type="text" name="torrent" size="80" value="<?= $_GET['torrent'] ?: '' ?>" /></td>
                </tr>
                <tr class="Form-row">
                    <td class="Form-label" width="150px"><?= t('server.reportsv2.torrent_group') ?></td>
                    <td class="Form-inputs"><input class="Input" type="text" name="group" size="80" value="<?= $_GET['group'] ?: '' ?>" /></td>
                </tr>
                <tr class="Form-row">
                    <td class="Form-label" width="150px"><?= t('server.reportsv2.report_type') ?></td>
                    <td class="Form-inputs">
                        <select class="Input" multiple="multiple" size="8" name="report-type[]">
                            <option class="Select-option" value="0"><?= t('server.reportsv2.don_t_care') ?></option>
                            <?
                            foreach ($report_name_cache as $key => $label) {
                                $selected = in_array($key, $ReportType) ? ' selected="selected"' : '';
                            ?>
                                <option class="Select-option" value="<?= $key ?>" <?= $selected ?>><?= $label ?></option>
                            <?  } ?>
                        </select>
                    </td>
                </tr>
                <tr class="Form-row">
                    <td class="Form-label" width="150px"><?= t('server.reportsv2.report_created') ?></td>
                    <td class="Form-inputs">
                        <?= t('server.reportsv2.from') ?> <input class="Input is-small" type="text" name="dt-from" size="10" value="<?= $dt_from ?>" /> <?= t('server.reportsv2.and_until') ?>
                        <input class="Input is-small" type="text" name="dt-until" size="10" value="<?= $dt_until ?>" />
                    </td>
                </tr>
                <tr class="Form-row">
                    <td colspan="2">
                        <input type="hidden" name="action" value="search" />
                        <input class="Button" type="submit" value="<?= t('server.reportsv2.search_reports') ?>" />
                    </td>
                </tr>
            </table>
        </form>
    </div>


    <?
    if (isset($Results)) {
        $Page  = max(1, isset($_GET['page']) ? intval($_GET['page']) : 1);
        $Pages = Format::get_pages($Page, $Total, CONFIG['TORRENTS_PER_PAGE']);
    ?>
        <div class="BodyNavLinks">
            <?= $Pages ?>
        </div>
        <div class="TableContainer">
            <table class="Table">
                <thead>
                    <tr class="Table-rowHeader">
                        <td class="Table-cell"><?= t('server.reportsv2.report') ?></td>
                        <td class="Table-cell"><?= t('server.reportsv2.uploaded_by') ?></td>
                        <td class="Table-cell"><?= t('server.reportsv2.reported_by') ?></td>
                        <td class="Table-cell"><?= t('server.reportsv2.handled_by') ?></td>
                        <td class="Table-cell"><?= t('server.common.torrent') ?></td>
                        <td class="Table-cell"><?= t('server.reportsv2.report_type') ?></td>
                        <td class="Table-cell" width="120px"><?= t('server.reportsv2.date_reported') ?></td>
                    </tr>
                </thead>
                <tbody>
                    <?
                    $user_cache = [];

                    foreach ($Results as $r) {
                        if (!array_key_exists($r['UserID'], $user_cache)) {
                            $user_cache[$r['UserID']] = Users::format_username($r['UserID']);
                        }
                        if (!array_key_exists($r['ReporterID'], $user_cache)) {
                            $user_cache[$r['ReporterID']] = Users::format_username($r['ReporterID']);
                        }
                        if (!array_key_exists($r['ResolverID'], $user_cache)) {
                            $user_cache[$r['ResolverID']] = $r['ResolverID']
                                ? Users::format_username($r['ResolverID'])
                                : '<i>unclaimed</i>';
                        }
                        if ($r['GroupID']) {
                            $Torrent = Torrents::get_torrent($r['TorrentID']);
                            $name = Torrents::torrent_simple_view($Torrent['Group'], $Torrent, true, [
                                'SettingTorrentTitle' => G::$LoggedUser['SettingTorrentTitle'],
                            ]);
                        } else {
                            $name = $r['Name'];
                        }
                    ?>
                        <tr class="Table-row">
                            <td class="Table-cell"><a href="/reportsv2.php?view=report&id=<?= $r['ID'] ?>"><?= $r['ID'] ?></a></td>
                            <td class="Table-cell"><?= $r['UserID'] ? $user_cache[$r['UserID']] : '<i>unknown</i>' ?></td>
                            <td class="Table-cell"><?= $user_cache[$r['ReporterID']] ?></td>
                            <td class="Table-cell"><?= $user_cache[$r['ResolverID']] ?></td>
                            <td class="Table-cell"><?= $name ?></td>
                            <td class="Table-cell"><?= $report_name_cache[$r['Type']] ?></td>
                            <td class="Table-cell"><?= time_diff($r['ReportedTime']) ?></td>
                        </tr>
                    <?  } ?>
                </tbody>
            </table>
        </div>
        <div class="BodyNavLinks">
            <?= $Pages ?>
        </div>
        <br />
    <? } ?>


</div>
<?
View::show_footer();
