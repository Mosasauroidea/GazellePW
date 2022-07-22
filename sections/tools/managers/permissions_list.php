<?
View::show_header(Lang::get('tools.manage_permissions'));
?>
<script type="text/javascript">
    //<![CDATA[
    function confirmDelete(id) {
        if (confirm("<?= Lang::get('tools.are_you_sure_remove_class') ?>")) {
            location.href = "tools.php?action=permissions&removeid=" + id;
        }
        return false;
    }
    //]]>
</script>
<div class="LayoutBody">
    <div class="header">
        <div class="BodyNavLinks">
            <a href="tools.php?action=permissions&amp;id=new" class="brackets"><?= Lang::get('tools.create_a_new_permission_set') ?></a>
            <a href="tools.php" class="brackets"><?= Lang::get('tools.back_to_tools') ?></a>
        </div>
    </div>
    <?
    $DB->query("
	SELECT
		p.ID,
		p.Name,
		p.Level,
		p.Secondary,
		COUNT(u.ID) + COUNT(DISTINCT l.UserID)
	FROM permissions AS p
		LEFT JOIN users_main AS u ON u.PermissionID = p.ID
		LEFT JOIN users_levels AS l ON l.PermissionID = p.ID
	GROUP BY p.ID
	ORDER BY p.Secondary ASC, p.Level ASC");
    if ($DB->has_results()) {
    ?>
        <div class="TableContainer">
            <table class="TableUserPermission Table">
                <tr class="Table-row">
                    <td class="Table-cell"><?= Lang::get('tools.name') ?></td>
                    <td class="Table-cell"><?= Lang::get('tools.level') ?></td>
                    <td class="Table-cell"><?= Lang::get('tools.user_count') ?></td>
                    <td class="Table-cell Table-cellCenter"><?= Lang::get('tools.actions') ?></td>
                </tr>
                <? while (list($ID, $Name, $Level, $Secondary, $UserCount) = $DB->next_record()) {
                    $part = $Secondary ? 'secclass' : 'class';
                    $link = "user.php?action=search&{$part}={$ID}";
                ?>
                    <tr class="Table-row">
                        <td class="Table-cell"><?= display_str($Name); ?></td>
                        <td class="Table-cell"><?= ($Secondary ? 'Secondary' : $Level) ?></td>
                        <td class="Table-cell"><a href="<?= $link; ?>"><?= number_format($UserCount); ?></a></td>
                        <td class="Table-cell Table-cellCenter">
                            <a href="tools.php?action=permissions&amp;id=<?= $ID ?>" class="brackets"><?= Lang::get('global.edit') ?></a>
                            &nbsp;
                            <a href="#" onclick="return confirmDelete(<?= $ID ?>);" class="brackets floatright"><?= Lang::get('tools.remove') ?></a>
                        </td>
                    </tr>
                <?  } ?>
            </table>
        </div>
    <?
    } else { ?>
        <h2 align="center"><?= Lang::get('tools.there_are_no_permission_classes') ?></h2>
    <?
    } ?>
</div>
<?
View::show_footer();
?>