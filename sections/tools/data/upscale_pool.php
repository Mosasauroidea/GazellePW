<?php
if (!check_perms('site_view_flow')) {
    error(403);
}
View::show_header(t('server.tools.h2_upscale_pool'), '', 'PageToolUpscalePool');
define('USERS_PER_PAGE', 50);
list($Page, $Limit) = Format::page_limit(USERS_PER_PAGE);

$RS = $DB->query("
	SELECT
		SQL_CALC_FOUND_ROWS
		m.ID,
		m.Username,
		m.Uploaded,
		m.Downloaded,
		m.PermissionID,
		m.Enabled,
		i.Donor,
		i.Warned,
		i.JoinDate,
		i.RatioWatchEnds,
		i.RatioWatchDownload,
		m.RequiredRatio
	FROM users_main AS m
		LEFT JOIN users_info AS i ON i.UserID = m.ID
	WHERE i.RatioWatchEnds != '0000-00-00 00:00:00'
		AND m.Enabled = '1'
	ORDER BY i.RatioWatchEnds ASC
	LIMIT $Limit");
$DB->query('SELECT FOUND_ROWS()');
list($Results) = $DB->next_record();
$DB->query("
	SELECT COUNT(UserID)
	FROM users_info
	WHERE BanDate != '0000-00-00 00:00:00'
		AND BanReason = '2'");
list($TotalDisabled) = $DB->next_record();
$DB->set_query_id($RS);
$Pages = Format::get_pages($Page, $Results, USERS_PER_PAGE, 11);
?>
<div class="LayoutPage">
    <div class="BodyHeader">
        <h2 class="BodyHeader-nav"><?= t('server.tools.h2_upscale_pool') ?></h2>
    </div>
    <?
    if ($DB->has_results()) {
    ?>
        <div id="users_on_ratio_watch_number">
            <?= t('server.tools.there_are_currently_enabled_users_on_ratio_watch', ['Values' => [
                number_format($Results),
                number_format($TotalDisabled)
            ]]) ?>
        </div>
        <? View::pages($Pages); ?>
        <table class="Table">
            <tr class="Table-rowHeader">
                <td class="Table-cell"><?= t('server.tools.user') ?></td>
                <td class="Table-cell Table-cellRight"><?= t('server.tools.uploaded') ?></td>
                <td class="Table-cell Table-cellRight"><?= t('server.tools.downloaded') ?></td>
                <td class="Table-cell Table-cellRight"><?= t('server.tools.ratio') ?></td>
                <td class="Table-cell Table-cellRight"><?= t('server.tools.required_ratio') ?></td>
                <td class="Table-cell Table-cellRight" data-tooltip="<?= t('server.tools.deficit_title') ?>"><?= t('server.tools.deficit') ?></td>
                <td class="Table-cell Table-cellRight" data-tooltip="<?= t('server.tools.gamble_title') ?>"><?= t('server.tools.gamble') ?></td>
                <td class="Table-cell Table-cellRight"><?= t('server.tools.registration_date') ?></td>
                <td class="Table-cell Table-cellRight" data-tooltip="<?= t('server.tools.ratio_watch_ended_ends_title') ?>"><?= t('server.tools.ratio_watch_ended_ends') ?></td>
                <td class="Table-cell Table-cellRight"><?= t('server.tools.life_span') ?></td>
            </tr>
            <?
            while (list($UserID, $Username, $Uploaded, $Downloaded, $PermissionID, $Enabled, $Donor, $Warned, $Joined, $RatioWatchEnds, $RatioWatchDownload, $RequiredRatio) = $DB->next_record()) {
            ?>
                <tr class="Table-row">
                    <td class="Table-cell"><?= Users::format_username($UserID, true, true, true, true) ?></td>
                    <td class="Table-cell Table-cellRight"><?= Format::get_size($Uploaded) ?></td>
                    <td class="Table-cell Table-cellRight"><?= Format::get_size($Downloaded) ?></td>
                    <td class="Table-cell Table-cellRight"><?= Format::get_ratio_html($Uploaded, $Downloaded) ?></td>
                    <td class="Table-cell Table-cellRight"><?= number_format($RequiredRatio, 2) ?></td>
                    <td class="Table-cell Table-cellRight"><? if (($Downloaded * $RequiredRatio) > $Uploaded) {
                                                                echo Format::get_size(($Downloaded * $RequiredRatio) - $Uploaded);
                                                            } ?></td>
                    <td class="Table-cell Table-cellRight"><?= Format::get_size($Downloaded - $RatioWatchDownload) ?></td>
                    <td class="Table-cell Table-cellRight"><?= time_diff($Joined, 2) ?></td>
                    <td class="Table-cell Table-cellRight"><?= time_diff($RatioWatchEnds) ?></td>
                    <td class="Table-cell Table-cellRight"><?= time_diff(strtotime($Joined), strtotime($RatioWatchEnds)) ?></td>
                </tr>
            <?  } ?>
        </table>
        <? View::pages($Pages); ?>
    <?
    } else {
        View::line(t('server.tools.there_are_currently_no_users_on_ratio_watch'));
    }
    ?>
</div>
<?

View::show_footer();
?>