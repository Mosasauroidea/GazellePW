<?
// error out on invalid requests (before caching)
if (isset($_GET['details'])) {
    if (in_array($_GET['details'], array('ul', 'dl', 'numul', 'bonus_points'))) {
        $Details = $_GET['details'];
    } else {
        error(404);
    }
} else {
    $Details = 'all';
}

View::show_header(Lang::get('top10', 'top_10_users'), '', 'PageTop10User');
?>
<div class="LayoutBody">
    <div class="BodyHeader">
        <h2 class="BodyHeader-nav"><?= Lang::get('top10', 'top_10_users') ?></h2>
        <? Top10View::render_linkbox("users", 'BodyNavLinks'); ?>

    </div>
    <?
    // defaults to 10 (duh)
    $Limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
    $Limit = in_array($Limit, array(10, 100, 250)) ? $Limit : 10;
    $BaseQuery = "
	SELECT
		u.ID,
		ui.JoinDate,
		u.Uploaded,
		u.Downloaded,
		COUNT(t.ID) AS NumUploads,
		u.Paranoia,
        u.BonusPoints
	FROM users_main AS u
		JOIN users_info AS ui ON ui.UserID = u.ID
		LEFT JOIN torrents AS t ON t.UserID=u.ID
	WHERE u.Enabled='1'
		And Uploaded>" . STARTING_UPLOAD . "
		AND (Uploaded>'" . 10 * 1024 * 1024 * 1024 . "'
		or Downloaded>'" . 5 * 1024 * 1024 * 1024 . "')
	GROUP BY u.ID";

    /* upload count */
    if ($Details == 'all' || $Details == 'numul') {
        if (!$TopUserNumUploads = $Cache->get_value('topuser_numul')) {
            $DB->query("$BaseQuery ORDER BY NumUploads DESC LIMIT $Limit;");
            $TopUserNumUploads = $DB->to_array();
            $Cache->cache_value('topuser_numul' . $Limit, $TopUserNumUploads, 3600 * 12);
        }
        generate_user_table(Lang::get('top10', 'torrents_uploaded'), 'numul', $TopUserNumUploads, $Limit);
    }


    /* upload size */
    if ($Details == 'all' || $Details == 'ul') {
        if (!$TopUserUploads = $Cache->get_value('topuser_ul')) {
            $DB->query("$BaseQuery ORDER BY u.Uploaded DESC LIMIT 250;");
            $TopUserUploads = $DB->to_array();
            $Cache->cache_value('topuser_ul', $TopUserUploads, 3600 * 12);
        }
        generate_user_table(Lang::get('top10', 'uploaders'), 'ul', $TopUserUploads, $Limit);
    }

    /* download size */
    if ($Details == 'all' || $Details == 'dl') {
        if (!$TopUserDownloads = $Cache->get_value('topuser_dl')) {
            $DB->query("$BaseQuery ORDER BY u.Downloaded DESC LIMIT $Limit;");
            $TopUserDownloads = $DB->to_array();
            $Cache->cache_value('topuser_dl', $TopUserDownloads, 3600 * 12);
        }
        generate_user_table(Lang::get('top10', 'downloaders'), 'dl', $TopUserDownloads, $Limit);
    }

    /* bonus points */
    if ($Details == 'all' || $Details == 'bonus_points') {
        if (!$TopUserBonusPoints = $Cache->get_value('topuser_bonus_points')) {
            $DB->query("$BaseQuery ORDER BY BonusPoints DESC LIMIT $Limit;");
            $TopUserBonusPoints = $DB->to_array();
            $Cache->cache_value('topuser_bonus_points' . $Limit, $TopUserBonusPoints, 3600 * 12);
        }
        generate_user_table(Lang::get('user', 'bonus_points'), 'bonus_points', $TopUserBonusPoints, $Limit);
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
        $DefaultItems = ['ul', 'dl', 'numul', 'bonus_points', 'ratio'];
        $Items = create_items($DefaultItems, $Tag);
        $Details = array_slice($Details, 0, $Limit);
    ?>
        <h3>
            <?= Lang::get('top10', 'top') ?> <?= $Limit . ' ' . $Caption; ?>
            <small class="top10_quantity_links">
                <?
                switch ($Limit) {
                    case 100: ?>
                        - <a href="top10.php?type=users&amp;details=<?= $Tag ?>" class="brackets"><?= Lang::get('top10', 'top') ?> 10</a>
                        - <span class="brackets"><?= Lang::get('top10', 'top') ?> 100</span>
                        - <a href="top10.php?type=users&amp;limit=250&amp;details=<?= $Tag ?>" class="brackets"><?= Lang::get('top10', 'top') ?> 250</a>
                    <? break;
                    case 250: ?>
                        - <a href="top10.php?type=users&amp;details=<?= $Tag ?>" class="brackets"><?= Lang::get('top10', 'top') ?> 10</a>
                        - <a href="top10.php?type=users&amp;limit=100&amp;details=<?= $Tag ?>" class="brackets"><?= Lang::get('top10', 'top') ?> 100</a>
                        - <span class="brackets"><?= Lang::get('top10', 'top') ?> 250</span>
                    <? break;
                    default: ?>
                        - <span class="brackets"><?= Lang::get('top10', 'top') ?> 10</span>
                        - <a href="top10.php?type=users&amp;limit=100&amp;details=<?= $Tag ?>" class="brackets"><?= Lang::get('top10', 'top') ?> 100</a>
                        - <a href="top10.php?type=users&amp;limit=250&amp;details=<?= $Tag ?>" class="brackets"><?= Lang::get('top10', 'top') ?> 250</a>
                <? } ?>
            </small>
        </h3>
        <div class="TableContainer">
            <table class="TableTop10User Table">
                <tr class="Table-rowHeader">
                    <td class="Table-cell"><?= Lang::get('top10', 'rank') ?></td>
                    <td class="Table-cell"><?= Lang::get('top10', 'user') ?></td>
                    <? foreach ($Items as $Item) { ?>
                        <? if ($Item === 'numul') { ?>
                            <td class="Table-cell Table-cellRight"><?= Lang::get('top10', 'uploads') ?></td>
                        <? } else if ($Item === 'bonus_points') { ?>
                            <td class="Table-cell Table-cellRight"><?= Lang::get('user', 'bonus_points') ?></td>
                        <? } else if ($Item === 'ul') { ?>
                            <td class="Table-cell Table-cellRight"><?= Lang::get('user', 'uploaded') ?></td>
                        <? } else if ($Item === 'dl') { ?>
                            <td class="Table-cell Table-cellRight"><?= Lang::get('user', 'downloaded') ?></td>
                        <? } else if ($Item === 'ratio') { ?>
                            <td class="Table-cell Table-cellRight"><?= Lang::get('user', 'ratio') ?></td>
                        <? } ?>
                    <? } ?>
                    <td class="Table-cell Table-cellRight"><?= Lang::get('top10', 'joined') ?></td>
                </tr>
                <?
                // in the unlikely event that query finds 0 rows...
                if (empty($Details)) {
                    echo '
		<tr class="Table-row">
			<td class="Table-cell Table-cellCenter" colspan="9">' . Lang::get('top10', 'found_no_users_matching_the_criteria') . '</td>
		</tr>
		</table><br />';
                    return;
                }
                $Rank = 0;
                foreach ($Details as $Detail) {
                    $Rank++;
                    $IsAnonymous = preg_match("/&quot;(uploaded|downloaded|ratio|uploads\+|bonuspoints)&quot;/", $Detail['Paranoia']);
                ?>
                    <tr class="Table-row">
                        <td class="Table-cell"><?= $Rank ?></td>
                        <td class="Table-cell"><?= $IsAnonymous ?  Lang::get('user', 'anonymous') : Users::format_username($Detail['ID'], false, false, false) ?></td>
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
                            <? } ?>
                        <? } ?>
                        <td class="Table-cell Table-cellRight"><?= $IsAnonymous ? '--' : (new DateTime($Detail['JoinDate']))->format('Y'); ?></td>
                    </tr>
                <? } ?>
            </table>
        </div>
    <? } ?>