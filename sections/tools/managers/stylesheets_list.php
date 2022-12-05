<?php
if (!check_perms('admin_manage_stylesheets')) {
    error(403);
}
View::show_header(t('server.tools.manage_stylesheets'));
?>
<div class="LayoutBody">
    <div class="BodyHeader">
        <h2 class="BodyHeader-nav"><?= t('server.tools.manage_stylesheets') ?></h2>
    </div>
    <?php
    $DB->prepared_query("
	SELECT
		s.ID,
		s.Name,
		s.Description,
		s.`Default`,
		IFNULL(ui.`Count`, 0),
		IFNULL(ud.`Count`, 0)
	FROM stylesheets AS s
	LEFT JOIN (
		SELECT StyleID, COUNT(*) AS Count FROM users_info AS ui JOIN users_main AS um ON ui.UserID = um.ID WHERE um.Enabled='1' GROUP BY StyleID
	) AS ui ON s.ID=ui.StyleID
	LEFT JOIN (
		SELECT StyleID, COUNT(*) AS Count FROM users_info AS ui JOIN users_main AS um ON ui.UserID = um.ID GROUP BY StyleID
	) AS ud ON s.ID = ud.StyleID
	ORDER BY s.ID");
    if ($DB->has_results()) {
    ?>
        <table class="Table">
            <tr class="Table-rowHeader">
                <td class="Table-cell"><?= t('server.tools.name') ?></td>
                <td class="Table-cell"><?= t('server.tools.description') ?></td>
                <td class="Table-cell"><?= t('server.tools.default') ?></td>
                <td class="Table-cell"><?= t('server.tools.count') ?></td>
            </tr>
            <?php
            while (list($ID, $Name, $Description, $Default, $EnabledCount, $TotalCount) = $DB->next_record(MYSQLI_NUM, array(1, 2))) { ?>
                <tr class="Table-row">
                    <td class="Table-cell"><?= $Name ?></td>
                    <td class="Table-cell"><?= $Description ?></td>
                    <td class="Table-cell"><?= ($Default == '1') ? t('server.tools.default') : '' ?></td>
                    <td class="Table-cell"><?= number_format($EnabledCount) ?> (<?= number_format($TotalCount) ?>)</td>
                </tr>
            <?php    } ?>
        </table>
    <?php
    } else {
        View::line(t('server.tools.there_are_no_stylesheets'));
    } ?>
</div>
<?php
View::show_footer();
