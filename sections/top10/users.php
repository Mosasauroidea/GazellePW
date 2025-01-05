<?
// error out on invalid requests (before caching)
if (isset($_GET['details'])) {
    if (in_array($_GET['details'], array('ul', 'dl', 'numul', 'bonus_points', 'seeding_size'))) {
        $Details = $_GET['details'];
    } else {
        error(404);
    }
} else {
    $Details = 'all';
}

View::show_header(t('server.top10.top_10_users'), '', 'PageTop10User');
?>
<div class="LayoutBody">
    <div class="BodyHeader">
        <h2 class="BodyHeader-nav"><?= t('server.top10.top_10_users') ?></h2>
        <? Top10View::render_linkbox("users", 'BodyNavLinks'); ?>

    </div>
    <?
    // defaults to 10 (duh)
    $Limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
    $Limit = in_array($Limit, array(10, 100, 250)) ? $Limit : 10;
    $BaseQuery =
        "SELECT
            u.ID,
            ui.JoinDate,
            u.Uploaded,
            u.Downloaded,
            COUNT(t.ID) AS NumUploads,
            u.Paranoia,
            u.BonusPoints,
            temp.SeedingSize as SeedingSize
        FROM
            users_main AS u
        JOIN users_info AS ui
        ON
            ui.UserID = u.ID
        LEFT JOIN torrents AS t
        ON
            t.UserID = u.ID
        LEFT JOIN(
            SELECT
                xfu.uid,
                SUM(seedingt.Size) AS SeedingSize
            FROM
                (
                SELECT DISTINCT
                    uid,
                    fid
                FROM
                    xbt_files_users
                WHERE
                    active = 1 AND remaining = 0 AND mtime > UNIX_TIMESTAMP(NOW() - INTERVAL 1 HOUR)) AS xfu
                LEFT JOIN torrents AS seedingt
                ON
                    seedingt.ID = xfu.fid  group by uid
            ) AS temp
        ON
            temp.uid = u.id
        WHERE
            u.Enabled = '1' And Uploaded>" . CONFIG['STARTING_UPLOAD'] . " AND(
                u.Uploaded > '" . 10 * 1024 * 1024 * 1024 . "' OR u.Downloaded > '" . 5 * 1024 * 1024 * 1024 . "'
            )
        GROUP BY
            u.ID";
    /* upload count */
    if ($Details == 'all' || $Details == 'numul') {
        if (!$TopUserNumUploads = $Cache->get_value('topuser_numul')) {
            $DB->query("$BaseQuery ORDER BY NumUploads DESC LIMIT $Limit;");
            $TopUserNumUploads = $DB->to_array();
            $Cache->cache_value('topuser_numul' . $Limit, $TopUserNumUploads, 3600 * 12);
        }
        generate_user_table(t('server.top10.torrents_uploaded'), 'numul', $TopUserNumUploads, $Limit);
    }


    /* upload size */
    if ($Details == 'all' || $Details == 'ul') {
        if (!$TopUserUploads = $Cache->get_value('topuser_ul')) {
            $DB->query("$BaseQuery ORDER BY u.Uploaded DESC LIMIT 250;");
            $TopUserUploads = $DB->to_array();
            $Cache->cache_value('topuser_ul', $TopUserUploads, 3600 * 12);
        }
        generate_user_table(t('server.top10.uploaders'), 'ul', $TopUserUploads, $Limit);
    }

    /* download size */
    if ($Details == 'all' || $Details == 'dl') {
        if (!$TopUserDownloads = $Cache->get_value('topuser_dl')) {
            $DB->query("$BaseQuery ORDER BY u.Downloaded DESC LIMIT $Limit;");
            $TopUserDownloads = $DB->to_array();
            $Cache->cache_value('topuser_dl', $TopUserDownloads, 3600 * 12);
        }
        generate_user_table(t('server.top10.downloaders'), 'dl', $TopUserDownloads, $Limit);
    }

    /* bonus points */
    if ($Details == 'all' || $Details == 'bonus_points') {
        if (!$TopUserBonusPoints = $Cache->get_value('topuser_bonus_points')) {
            $DB->query("$BaseQuery ORDER BY BonusPoints DESC LIMIT $Limit;");
            $TopUserBonusPoints = $DB->to_array();
            $Cache->cache_value('topuser_bonus_points' . $Limit, $TopUserBonusPoints, 3600 * 12);
        }
        generate_user_table(t('server.user.bonus_points'), 'bonus_points', $TopUserBonusPoints, $Limit);
    }

    if ($Details == 'all' || $Details == 'seeding_size') {
        if (!$TopUserSeedingSize = $Cache->get_value('topuser_seeding_size_' . $Limit)) {
            $DB->query("$BaseQuery ORDER BY SeedingSize DESC LIMIT $Limit;");
            $TopUserSeedingSize = $DB->to_array();
            $Cache->cache_value('topuser_seeding_size_' . $Limit, $TopUserSeedingSize, 3600 * 12);
        }
        generate_user_table(t('server.user.seeding_size'), 'seeding_size', $TopUserSeedingSize, $Limit);
    }

    echo '</div>';
    View::show_footer();
    exit;

    function create_items($Items, $Item) {
        $Result = array_diff($Items, [$Item]);
        array_unshift($Result, $Item);
        return $Result;
    }

    // generate a table based on data from most recent query to $DB
    function generate_user_table($Caption, $Tag, $Details, $Limit) {
        $DefaultItems = ['ul', 'dl', 'numul', 'bonus_points', 'ratio', 'seeding_size'];
        $Items = create_items($DefaultItems, $Tag);
        $Details = array_slice($Details, 0, $Limit);
    ?>
        <div class="Group">
            <div class="Group-header">
                <div class="Group-headerTitle">
                    <?= t('server.top10.top') ?> <?= $Limit . ' ' . $Caption; ?>
                </div>
                <small class="Group-headerActions top10_quantity_links">
                    <?
                    switch ($Limit) {
                        case 100: ?>
                            <a href="top10.php?type=users&amp;details=<?= $Tag ?>" class="brackets"><?= t('server.top10.top') ?> 10</a>
                            - <span class="brackets"><?= t('server.top10.top') ?> 100</span>
                            - <a href="top10.php?type=users&amp;limit=250&amp;details=<?= $Tag ?>" class="brackets"><?= t('server.top10.top') ?> 250</a>
                        <? break;
                        case 250: ?>
                            <a href="top10.php?type=users&amp;details=<?= $Tag ?>" class="brackets"><?= t('server.top10.top') ?> 10</a>
                            - <a href="top10.php?type=users&amp;limit=100&amp;details=<?= $Tag ?>" class="brackets"><?= t('server.top10.top') ?> 100</a>
                            - <span class="brackets"><?= t('server.top10.top') ?> 250</span>
                        <? break;
                        default: ?>
                            <span class="brackets"><?= t('server.top10.top') ?> 10</span>
                            - <a href="top10.php?type=users&amp;limit=100&amp;details=<?= $Tag ?>" class="brackets"><?= t('server.top10.top') ?> 100</a>
                            - <a href="top10.php?type=users&amp;limit=250&amp;details=<?= $Tag ?>" class="brackets"><?= t('server.top10.top') ?> 250</a>
                    <? } ?>
                </small>
            </div>
            <div class="Group-body">
                <div class="TableContainer">
                    <?
                    if (empty($Details)) {
                        echo '<table>
		<tr class="Table-row">
			<td class="center Table-cell Table-cellCenter" colspan="9">' . t('server.top10.found_no_users_matching_the_criteria') . '</td>
		</tr>
		</table></div></div></div>';
                        return;
                    }
                    ?>
                    <table class="TableTop10User Table">
                        <tr class="Table-rowHeader">
                            <td class="Table-cell"><?= t('server.top10.rank') ?></td>
                            <td class="Table-cell"><?= t('server.top10.user') ?></td>
                            <? foreach ($Items as $Item) { ?>
                                <? if ($Item === 'numul') { ?>
                                    <td class="Table-cell Table-cellRight"><?= t('server.top10.uploads') ?></td>
                                <? } else if ($Item === 'bonus_points') { ?>
                                    <td class="Table-cell Table-cellRight"><?= t('server.user.bonus_points') ?></td>
                                <? } else if ($Item === 'ul') { ?>
                                    <td class="Table-cell Table-cellRight"><?= t('server.user.uploaded') ?></td>
                                <? } else if ($Item === 'dl') { ?>
                                    <td class="Table-cell Table-cellRight"><?= t('server.user.downloaded') ?></td>
                                <? } else if ($Item === 'ratio') { ?>
                                    <td class="Table-cell Table-cellRight"><?= t('server.user.ratio') ?></td>
                                <? } else if ($Item == "seeding_size") { ?>
                                    <td class="Table-cell Table-cellRight"><?= t('server.user.seeding_size') ?></td>
                                <? } ?>
                            <? } ?>
                            <td class="Table-cell Table-cellRight"><?= t('server.top10.joined') ?></td>
                        </tr>
                        <?
                        // in the unlikely event that query finds 0 rows...

                        $Rank = 0;
                        $IsMod = check_perms('users_mod');
                        foreach ($Details as $Detail) {
                            $UserName = Users::format_username($Detail['ID'], false, false, false);
                            $Rank++;
                            $IsAnonymous = preg_match("/&quot;(uploaded|downloaded|ratio|uploads\+|bonuspoints)&quot;/", $Detail['Paranoia']);
                            if ($IsMod) {
                                if ($IsAnonymous) {
                                    $NameItem = t('server.user.anonymous') . '(' . $UserName . ')';
                                } else {
                                    $NameItem = $UserName;
                                }
                            } else if ($IsAnonymous) {
                                $NameItem = t('server.user.anonymous');
                            } else {
                                $NameItem = $UserName;
                            }
                        ?>
                            <tr class="Table-row">
                                <td class="Table-cell"><?= $Rank ?></td>
                                <td class="Table-cell"><?= $NameItem ?></td>
                                <? foreach ($Items as $Item) { ?>
                                    <? if ($Item === 'numul') { ?>
                                        <td class="Table-cell Table-cellRight"><?= number_format($Detail['NumUploads']) ?></td>
                                    <? } else if ($Item === 'bonus_points') { ?>
                                        <td class="Table-cell Table-cellRight"><?= Format::human_format($Detail['BonusPoints'], ['Percision' => 0]) ?></td>
                                    <? } else if ($Item === 'ul') { ?>
                                        <td class="Table-cell Table-cellRight"><?= Format::get_size($Detail['Uploaded'], 0) ?></td>
                                    <? } else if ($Item === 'dl') { ?>
                                        <td class="Table-cell Table-cellRight"><?= Format::get_size($Detail['Downloaded'], 0) ?></td>
                                    <? } else if ($Item === 'ratio') { ?>
                                        <td class="Table-cell Table-cellRight"><?= Format::get_ratio_html($Detail['Uploaded'], $Detail['Downloaded']) ?></td>
                                    <? } else if ($Item == 'seeding_size') { ?>
                                        <td class="Table-cell Table-cellRight"><?= Format::get_size($Detail['SeedingSize']) ?></td>
                                    <? } ?>
                                <? } ?>
                                <td class="Table-cell Table-cellRight"><?= $IsAnonymous ? '--' : (new DateTime($Detail['JoinDate']))->format('Y'); ?></td>
                            </tr>
                        <? } ?>
                    </table>
                </div>
            </div>
        </div>
    <? } ?>