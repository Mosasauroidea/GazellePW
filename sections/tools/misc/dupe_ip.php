<?php
if (!check_perms('users_view_ips')) {
    error(403);
}
View::show_header(t('server.tools.dupe_ips'));
define('USERS_PER_PAGE', 50);
define('IP_OVERLAPS', 5);
list($Page, $Limit) = Format::page_limit(USERS_PER_PAGE);


$RS = $DB->query("
		SELECT
			SQL_CALC_FOUND_ROWS
			m.ID,
			m.IP,
			m.Username,
			m.PermissionID,
			m.Enabled,
			i.Donor,
			i.Warned,
			i.JoinDate,
			(
				SELECT COUNT(DISTINCT h.UserID)
				FROM users_history_ips AS h
				WHERE h.IP = m.IP
			) AS Uses
		FROM users_main AS m
			LEFT JOIN users_info AS i ON i.UserID = m.ID
		WHERE
			(
				SELECT COUNT(DISTINCT h.UserID)
				FROM users_history_ips AS h
				WHERE h.IP = m.IP
			) >= " . IP_OVERLAPS . "
			AND m.Enabled = '1'
			AND m.IP != '127.0.0.1'
		ORDER BY Uses DESC
		LIMIT $Limit");
$DB->query('SELECT FOUND_ROWS()');
list($Results) = $DB->next_record();
$DB->set_query_id($RS);

$Pages = Format::get_pages($Page, $Results, USERS_PER_PAGE, 11);
?>
<div class="LayoutPage">
    <div class="BodyHeader">
        <h2 class="BodyHeader-nav"><?= t('server.tools.dupe_ips') ?></h2>
    </div>
    <?
    if ($DB->has_results()) {
    ?>
        <? View::pages($Pages) ?>
        <table class="Table">
            <tr class="Table-rowHeader">
                <td class="Table-cell"><?= t('server.tools.user') ?></td>
                <td class="Table-cell"><?= t('server.tools.td_ip_address') ?></td>
                <td class="Table-cell"><?= t('server.tools.dupes') ?></td>
                <td class="Table-cell"><?= t('server.tools.registered') ?></td>
            </tr>
            <?
            while (list($UserID, $IP, $Username, $PermissionID, $Enabled, $Donor, $Warned, $Joined, $Uses) = $DB->next_record()) {
            ?>
                <tr class="Table-row">
                    <td class="Table-cell"><?= Users::format_username($UserID, true, true, true, true) ?></td>
                    <td class="Table-cell">
                        <?= Tools::get_host_by_ajax($IP) . " ($IP)" ?>
                        <a href="userhistory.php?action=ips&amp;userid=<?= $UserID ?>" class="brackets">H</a>
                        <a href="user.php?action=search&amp;ip_history=on&amp;ip=<?= display_str($IP) ?>" data-tooltip="" class="brackets">S</a>
                    </td>
                    <td class="Table-cell"><?= display_str($Uses) ?></td>
                    <td class="Table-cell"><?= time_diff($Joined) ?></td>
                </tr>
            <?  } ?>
        </table>

        <? View::pages($Pages) ?>
    <?  } else {
        View::line('server.tools.there_are_no_users_with_more_than_n_ip_overlaps', ['Values' => [IP_OVERLAPS]]);
    }
    ?>
</div>
<?
View::show_footer();
?>