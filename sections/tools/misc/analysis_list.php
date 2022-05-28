<?php

if (!check_perms('site_analysis')) {
    error(403);
}

View::show_header('Analysis List');

$keys = array_filter(G::$Cache->getAllKeys(), function ($key) {
    return strpos($key, 'analysis_') === 0;
});
$items = array_map(function ($key) {
    $value = G::$Cache->get_value($key);
    $value['time'] = $value['time'] ?? 0;
    $value['key'] = substr($key, strlen('analysis_'));
    return $value;
}, $keys);
usort($items, function ($a, $b) {
    return $a['time'] > $b['time'] ? -1 : ($a['time'] === $b['time'] ? 0 : 1);
});
?>
<div class="BodyHeader">
    <h2 class="BodyHeader-nav">Site Analysis List</h2>
</div>
<table class="Table">
    <tr class="Table-rowHeader">
        <td class="Table-cell">Case</td>
        <td class="Table-cell">Errors</td>
        <td class="Table-cell">Queries</td>
        <td class="Table-cell">Cache</td>
        <td class="Table-cell">Elapsed</td>
        <td class="Table-cell">Date</td>
        <td class="Table-cell">Message</td>
    </tr>
    <?php
    foreach ($items as $item) {
    ?>
        <tr class="Table-row">
            <td class="Table-cell"><a href="tools.php?action=analysis&amp;case=<?= $item['key'] ?>"><?= $item['key'] ?></a></td>
            <td class="Table-cell"><?= count($item['errors']) ?></td>
            <td class="Table-cell"><?= count($item['queries']) ?></td>
            <td class="Table-cell"><?= count($item['cache']) ?></td>
            <td class="Table-cell"><?= display_str($item['perf']['Page process time'] ?? '?') ?></td>
            <td class="Table-cell"><?= date('Y-m-d H:i:s', $item['time'] ?? 0) ?></td>
            <td class="Table-cell">
                <pre><?= display_str($item['message']) ?></pre>
            </td>
        </tr>
    <?php
    }
    ?>
</table>
<?php
View::show_footer();
