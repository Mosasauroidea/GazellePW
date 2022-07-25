<?
if (!check_perms('admin_manage_permissions')) {
    error(403);
}
View::show_header('Special Users List', '', 'PageToolSpecialUser');
?>
<div class="LayoutBody">
    <?
    $DB->query("
	SELECT ID
	FROM users_main
	WHERE CustomPermissions != ''
		AND CustomPermissions != 'a:0:{}'");
    if ($DB->has_results()) {
    ?>
        <table class="Table">
            <tr class="Table-rowHeader">
                <td class="Table-cell"><?= t('server.tools.user') ?></td>
                <td class="Table-cell"><?= t('server.tools.access') ?></td>
            </tr>
            <?
            while (list($UserID) = $DB->next_record()) {
            ?>
                <tr class="Table-row">
                    <td class="Table-cell"><?= Users::format_username($UserID, true, true, true, true) ?></td>
                    <td class="Table-cell"><a href="user.php?action=permissions&amp;userid=<?= $UserID ?>"><?= t('server.tools.manage') ?></a></td>
                </tr>
            <?  } ?>
        </table>
    <?
    } else { ?>
        <h2 align="center"><?= t('server.tools.no_special_users') ?></h2>
    <?
    } ?>
</div>
<? View::show_footer(); ?>