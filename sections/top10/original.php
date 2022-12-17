<?
// error out on invalid requests (before caching)
if (isset($_GET['details'])) {
    if (in_array($_GET['details'], array('original', 'diy', 'buy'))) {
        $Details = $_GET['details'];
    } else {
        error(404);
    }
} else {
    $Details = 'all';
}
$OverrideParanoia = isset($_GET['override']) && check_perms('users_override_paranoia');
View::show_header(t('server.top10.top_10_original_uploaders'));
?>
<div class="LayoutBody">
    <div class="BodyHeader">
        <h2 class="BodyHeader-nav"><?= t('server.top10.top_10_original_uploaders') ?></h2>
        <? Top10View::render_linkbox("original", "BodyNavLinks"); ?>
    </div>
    <?

    // defaults to 10 (duh)
    $Limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
    $Limit = in_array($Limit, array(10, 100, 250)) ? $Limit : 10;
    //self_rip_uploads self_purchased_uploads
    $BaseQuery = "
	SELECT
		u.ID UserID,
		sum(if(t.Diy='1' or t.Buy='1',1,0)) DiyOrBuy,
		sum(if(t.Diy='1',1,0)) Diy,
		sum(if(t.Buy='1',1,0)) Buy,
		count(t.ID) Count
	FROM users_main as u
	" . ($OverrideParanoia ? "" : "JOIN users_info AS ui ON ui.UserID = u.ID") . "
	left join torrents AS t ON t.UserID=u.ID
	WHERE u.Enabled='1' 
		" . ($OverrideParanoia ? "" : "AND (Paranoia IS NULL OR Paranoia NOT LIKE '%\"originals+\"%')") . "
		and (t.Diy='1' or t.Buy='1')
	GROUP BY u.ID";
    //list($NumUploads) = $DB->next_record();
    /*
    if ($Details == 'all' || $Details == 'original') {
        if ($OverrideParanoia || !$TopUserNumUploads = $Cache->get_value('topuser_original_'.$Limit)) {
            $DB->query("$BaseQuery ORDER BY DiyOrBuy DESC LIMIT $Limit;");
            $TopUserNumUploads = $DB->to_array();
            if (!$OverrideParanoia) {
                $Cache->cache_value('topuser_original_'.$Limit,$TopUserNumUploads, 3600 * 12);
            }
        }
        generate_user_table(t('server.top10.original_uploaders'), t('server.top10.original_uploads'), 'original', $TopUserNumUploads, $Limit);
    }
*/
    if ($Details == 'all' || $Details == 'diy') {
        if ($OverrideParanoia || !$TopUserNumUploads = $Cache->get_value('topuser_diy_' . $Limit)) {
            $DB->query("$BaseQuery ORDER BY Diy DESC LIMIT $Limit;");
            $TopUserNumUploads = $DB->to_array();
            if (!$OverrideParanoia) {
                $Cache->cache_value('topuser_diy_' . $Limit, $TopUserNumUploads, 3600 * 12);
            }
        }
        generate_user_table(t('server.top10.self_rip_uploaders'), t('server.top10.self_rip_uploads'), 'diy', $TopUserNumUploads, $Limit);
    }
    if ($Details == 'all' || $Details == 'buy') {
        if ($OverrideParanoia || !$TopUserNumUploads = $Cache->get_value('topuser_buy_' . $Limit)) {
            $DB->query("$BaseQuery ORDER BY Buy DESC LIMIT $Limit;");
            $TopUserNumUploads = $DB->to_array();
            if (!$OverrideParanoia) {
                $Cache->cache_value('topuser_buy_' . $Limit, $TopUserNumUploads, 3600 * 12);
            }
        }
        generate_user_table(t('server.top10.self_purchased_uploaders'), t('server.top10.self_purchased_uploads'), 'buy', $TopUserNumUploads, $Limit);
    }

    echo '</div>';
    View::show_footer();
    exit;

    // generate a table based on data from most recent query to $DB
    function generate_user_table($Caption, $TH, $Tag, $Details, $Limit) {
        global $Time, $OverrideParanoia;
    ?>
        <div class="Group">
            <div class="Group-header">
                <div class="Group-headerTitle"><?= t('server.top10.top') ?> <?= $Limit . ' ' . $Caption; ?></div>
                <small class="Group-headerActions top10_quantity_links">
                    <?
                    switch ($Limit) {
                        case 100: ?>
                            <a href="top10.php?type=original&amp;details=<?= $Tag ?><?= $OverrideParanoia ? "&override=1" : "" ?>" class="brackets"><?= t('server.top10.top') ?> 10</a>
                            - <span class="brackets"><?= t('server.top10.top') ?> 100</span>
                            - <a href="top10.php?type=original&amp;limit=250&amp;details=<?= $Tag ?><?= $OverrideParanoia ? "&override=1" : "" ?>" class="brackets"><?= t('server.top10.top') ?> 250</a>
                        <? break;
                        case 250: ?>
                            <a href="top10.php?type=original&amp;details=<?= $Tag ?><?= $OverrideParanoia ? "&override=1" : "" ?>" class="brackets"><?= t('server.top10.top') ?> 10</a>
                            - <a href="top10.php?type=original&amp;limit=100&amp;details=<?= $Tag ?><?= $OverrideParanoia ? "&override=1" : "" ?>" class="brackets"><?= t('server.top10.top') ?> 100</a>
                            - <span class="brackets"><?= t('server.top10.top') ?> 250</span>
                        <? break;
                        default: ?>
                            <span class="brackets"><?= t('server.top10.top') ?> 10</span>
                            - <a href="top10.php?type=original&amp;limit=100&amp;details=<?= $Tag ?><?= $OverrideParanoia ? "&override=1" : "" ?>" class="brackets"><?= t('server.top10.top') ?> 100</a>
                            - <a href="top10.php?type=original&amp;limit=250&amp;details=<?= $Tag ?><?= $OverrideParanoia ? "&override=1" : "" ?>" class="brackets"><?= t('server.top10.top') ?> 250</a>
                    <?    } ?>
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
                    <table class="TableUser Table">
                        <tr class="Table-rowHeader">
                            <td class="Table-cell" width="10%"><?= t('server.top10.rank') ?></td>
                            <td class="Table-cell"><?= t('server.top10.user') ?></td>
                            <td class="Table-cell Table-cellRight"><?= $TH ?></td>
                            <td class="Table-cell Table-cellRight"><?= t('server.top10.uploads') ?></td>
                        </tr>
                        <?
                        $Rank = 0;
                        foreach ($Details as $Detail) {
                            switch ($Tag) {
                                case 'buy':
                                    $count = $Detail['Buy'];
                                    break;
                                case 'diy':
                                    $count = $Detail['Diy'];
                                    break;
                                default:
                                    $count = $Detail['DiyOrBuy'];
                                    break;
                            }
                            if (!$count) {
                                break;
                            }
                            $Rank++;
                            $Highlight = ($Rank % 2 ? 'a' : 'b');
                        ?>
                            <tr class="Table-row">
                                <td class="Table-cell" width="10%"><?= $Rank ?></td>
                                <td class="Table-cell"><?= Users::format_username($Detail['UserID'], false, false, false) ?></td>
                                <td class="Table-cell Table-cellRight"><?= number_format($count) ?></td>
                                <td class="Table-cell Table-cellRight"><?= number_format($Detail['Count']) ?></td>
                            </tr>
                        <?    } ?>
                    </table>
                </div>
            </div>
        </div>
    <?
    }
    ?>