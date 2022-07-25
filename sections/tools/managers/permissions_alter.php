<?
function display_perm($Key) {
    global $Values;
    $Perm = "<input type=\"checkbox\" name=\"perm_$Key\" id=\"$Key\" value=\"1\"";
    if (!empty($Values[$Key])) {
        $Perm .= ' checked="checked"';
    }
    $Perm .= " /> <label for=\"$Key\">" . t("server.permissions.$Key") . "</label><br />";
    echo "$Perm\n";
}

$DB->prepared_query("
	SELECT ID, Name
	FROM staff_groups
	ORDER BY Sort");
$Groups = $DB->to_array(false, MYSQLI_ASSOC);

View::show_header(t('server.tools.manage_permissions'), 'validate');

echo $Val->GenerateJS('permissionsform');
?>
<form class="manage_form" name="permissions" id="permissionsform" method="post" action="" onsubmit="return formVal();">
    <input type="hidden" name="action" value="permissions" />
    <input type="hidden" name="auth" value="<?= $LoggedUser['AuthKey'] ?>" />
    <input type="hidden" name="id" value="<?= display_str($_REQUEST['id']); ?>" />
    <div class="BodyNavLinks">
        <a href="tools.php?action=permissions" class="brackets"><?= t('server.tools.back_to_permission_list') ?></a>
        <a href="tools.php" class="brackets"><?= t('server.tools.back_to_tools') ?></a>
    </div>
    <table class="permission_head layout">
        <tr>
            <td class="label"><?= t('server.tools.permission_name') ?></td>
            <td><input class="Input" type="text" name="name" id="name" value="<?= !empty($Name) ? display_str($Name) : '' ?>" /></td>
        </tr>
        <tr>
            <td class="label"><?= t('server.tools.class_level') ?></td>
            <td><input class="Input" type="text" name="level" id="level" value="<?= !empty($Level) ? display_str($Level) : '' ?>" /></td>
        </tr>
        <tr>
            <td class="label"><?= t('server.tools.secondary_class') ?></td>
            <td><input type="checkbox" name="secondary" value="1" <?= !empty($Secondary) ? ' checked="checked"' : '' ?> /></td>
        </tr>
        <tr>
            <td class="label"><?= t('server.tools.show_on_staff_page') ?></td>
            <td><input type="checkbox" name="displaystaff" value="1" <?= !empty($DisplayStaff) ? ' checked="checked"' : '' ?> /></td>
        </tr>
        <tr>
            <td class="label"><?= t('server.tools.staff_page_group') ?></td>
            <td>
                <select class="Input" name="staffgroup" id="staffgroup">
                    <?php foreach ($Groups as $Group) { ?>
                        <option class="Select-option" value="<?= $Group['ID'] ?>" <?= $Group['ID'] == $StaffGroup ? ' selected="selected"' : '' ?>><?= $Group['Name'] ?></option>
                    <?php } ?>
                </select>
            </td>
        </tr>
        <tr>
            <td class="label"><?= t('server.tools.max_collages') ?></td>
            <td><input class="Input" type="text" name="maxcollages" size="5" value="<?= $Values['MaxCollages'] ?>" /></td>
        </tr>
        <tr>
            <td class="label"><?= t('server.tools.additional_forums') ?></td>
            <td><input class="Input" type="text" size="30" name="forums" value="<?= display_str($Forums) ?>" /></td>
        </tr>
        <? if (is_numeric($_REQUEST['id'])) { ?>
            <tr>
                <td class="label"><?= t('server.tools.current_users_in_this_class') ?></td>
                <td><?= number_format($UserCount) ?></td>
            </tr>
        <? } ?>
    </table>
    <?
    include(CONFIG['SERVER_ROOT'] . "/classes/permissions_form.php");
    permissions_form();
    ?>
</form>
<? View::show_footer(); ?>