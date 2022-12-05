<?
function display_perm($Key) {
    global $Values;
    $Perm = "<div class=\"Checkbox\"> <input class=\"Input\" type=\"checkbox\" name=\"perm_$Key\" id=\"$Key\" value=\"1\"";
    if (!empty($Values[$Key])) {
        $Perm .= ' checked="checked"';
    }
    $Perm .= " /> <label class=\"Checkbox-label\" for=\"$Key\">" . t("server.permissions.$Key") . "</label></div>";
    echo "$Perm\n";
}

$DB->prepared_query("
	SELECT ID, Name
	FROM staff_groups
	ORDER BY Sort");
$Groups = $DB->to_array(false, MYSQLI_ASSOC);

View::show_header(t('server.tools.permissions_manager'), 'validate');

echo $Val->GenerateJS('permissionsform');
?>
<div class="LayoutBody">
    <div class="BodyHeader">
        <div class="BodyHeader-nav"><?= t('server.tools.permissions_manager') ?></div>
        <div class="BodyNavLinks">
            <a href="tools.php?action=permissions" class="brackets"><?= t('server.tools.back_to_permission_list') ?></a>
        </div>
    </div>
    <form class="manage_form" name="permissions" id="permissionsform" method="post" action="" onsubmit="return formVal();">
        <input type="hidden" name="action" value="permissions" />
        <input type="hidden" name="auth" value="<?= $LoggedUser['AuthKey'] ?>" />
        <input type="hidden" name="id" value="<?= display_str($_REQUEST['id']); ?>" />
        <table class="Form-rowList permission_head layout" variant="header">
            <tr class="Form-rowHeader">
                <td>
                    <?= t('server.tools.create_a_new_permission_set') ?>
                </td>
            </tr>
            <tr class="Form-row">
                <td class="Form-label"><?= t('server.tools.permission_name') ?></td>
                <td class="Form-inputs"><input class="Input" type="text" name="name" id="name" value="<?= !empty($Name) ? display_str($Name) : '' ?>" /></td>
            </tr>
            <tr class="Form-row">
                <td class="Form-label"><?= t('server.tools.class_level') ?></td>
                <td class="Form-inputs"><input class="Input is-small" type="text" name="level" id="level" value="<?= !empty($Level) ? display_str($Level) : '' ?>" /></td>
            </tr>
            <tr class="Form-row">
                <td class="Form-label"><?= t('server.tools.secondary_class') ?></td>
                <td class="Form-inputs"><input type="checkbox" name="secondary" value="1" <?= !empty($Secondary) ? ' checked="checked"' : '' ?> /></td>
            </tr>
            <tr class="Form-row">
                <td class="Form-label"><?= t('server.tools.show_on_staff_page') ?></td>
                <td class="Form-inputs"><input type="checkbox" name="displaystaff" value="1" <?= !empty($DisplayStaff) ? ' checked="checked"' : '' ?> /></td>
            </tr>
            <tr class="Form-row">
                <td class="Form-label"><?= t('server.tools.staff_page_group') ?></td>
                <td class="Form-inputs">
                    <select class="Input" name="staffgroup" id="staffgroup">
                        <?php foreach ($Groups as $Group) { ?>
                            <option class="Select-option" value="<?= $Group['ID'] ?>" <?= $Group['ID'] == $StaffGroup ? ' selected="selected"' : '' ?>><?= $Group['Name'] ?></option>
                        <?php } ?>
                    </select>
                </td>
            </tr>
            <tr class="Form-row">
                <td class="Form-label"><?= t('server.tools.max_collages') ?></td>
                <td class="Form-inputs"><input class="Input is-small" type="text" name="maxcollages" size="5" value="<?= $Values['MaxCollages'] ?>" /></td>
            </tr>
            <tr class="Form-row">
                <td class="Form-label"><?= t('server.tools.additional_forums') ?></td>
                <td class="Form-inputs"><input class="Input is-small" type="text" size="30" name="forums" value="<?= display_str($Forums) ?>" /></td>
            </tr>
            <? if (is_numeric($_REQUEST['id'])) { ?>
                <tr class="Form-row">
                    <td class="Form-label"><?= t('server.tools.current_users_in_this_class') ?></td>
                    <td class="Form-inputs"><?= number_format($UserCount) ?></td>
                </tr>
            <? } ?>
            <?
            include(CONFIG['SERVER_ROOT'] . "/classes/permissions_form.php");
            permissions_form();
            ?>
        </table>
    </form>
</div>
<? View::show_footer(); ?>