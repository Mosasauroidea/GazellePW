<?
if (!check_perms('admin_manage_permissions')) {
    error(403);
}
View::show_header(t('server.tools.special_users'), '', 'PageToolSpecialUser');
?>
<div class="LayoutBody">
    <div class="BodyHeader">
        <div class="BodyHeader-nav"><?= t('server.tools.special_users') ?></div>
    </div>
    <?
    $DB->query("
	SELECT ID
	FROM users_main
	WHERE CustomPermissions != ''
		AND CustomPermissions != 'a:0:{}'");
    if ($DB->has_results()) {
    ?> <table class="Table">
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
    } else {
        View::line(t('server.tools.no_special_users'));
    } ?>
</div>
<? View::show_footer(); ?>