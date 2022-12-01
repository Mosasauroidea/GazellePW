<?
//TODO: Redo HTML
if (!isset($_REQUEST['userid']) || !is_number($_REQUEST['userid'])) {
    error(404);
}

// Get the user class of the user being edited to ensure that the logged in user has permission
$DB->query("SELECT p.Level 
            FROM permissions p
            JOIN users_main AS um ON um.PermissionID = p.ID
            WHERE um.ID = '" . $_REQUEST['userid'] . "'");
list($UserClass) = $DB->next_record();

if (!check_perms('admin_manage_permissions', $UserClass)) {
    error(403);
}

include(CONFIG['SERVER_ROOT'] . "/classes/permissions_form.php");

list($UserID, $Username, $PermissionID) = array_values(Users::user_info($_REQUEST['userid']));

$DB->query("
	SELECT CustomPermissions
	FROM users_main
	WHERE ID = '$UserID'");

list($Customs) = $DB->next_record(MYSQLI_NUM, false);


$Defaults = Permissions::get_permissions_for_user($UserID, array());

$Delta = array();
if (isset($_POST['action'])) {
    authorize();

    foreach ($PermissionsArray as $Perm) {
        $Setting = isset($_POST["perm_$Perm"]) ? 1 : 0;
        $Default = isset($Defaults[$Perm]) ? 1 : 0;
        if ($Setting != $Default) {
            $Delta[$Perm] = $Setting;
        }
    }
    if (!is_number($_POST['maxcollages']) && !empty($_POST['maxcollages'])) {
        error("Please enter a valid number of extra personal collages");
    }
    $Delta['MaxCollages'] = $_POST['maxcollages'];

    $Cache->begin_transaction("user_info_heavy_$UserID");
    $Cache->update_row(false, array('CustomPermissions' => $Delta));
    $Cache->commit_transaction(0);
    $DB->query("
		UPDATE users_main
		SET CustomPermissions = '" . db_string(serialize($Delta)) . "'
		WHERE ID = '$UserID'");
} elseif (!empty($Customs)) {
    $Delta = unserialize($Customs);
}

$Permissions = array_merge($Defaults, $Delta);
$MaxCollages = $Customs['MaxCollages'] + $Delta['MaxCollages'];

function display_perm($Key) {
    global $Defaults, $Permissions;
    $Perm = "<div class=\"Checkbox\"> <input id=\"default_$Key\" type=\"checkbox\" disabled=\"disabled\"";
    if (isset($Defaults[$Key]) && $Defaults[$Key]) {
        $Perm .= ' checked="checked"';
    }
    $Perm .= " /><input class=\"Input\" type=\"checkbox\" name=\"perm_$Key\" id=\"$Key\" value=\"1\"";
    if (isset($Permissions[$Key]) && $Permissions[$Key]) {
        $Perm .= ' checked="checked"';
    }
    $Perm .= " /> <label class=\"Checkbox-label\" for=\"$Key\">" . t("server.permissions.$Key")  . "</label></div>";
    echo "$Perm\n";
}

View::show_header("$Username &gt; " .  t('server.tools.user_permission_manage'), '', 'PageUserPermission');
?>
<script type="text/javascript">
    //<![CDATA[
    function reset() {
        for (i = 0; i < $('#permissionsform').raw().elements.length; i++) {
            element = $('#permissionsform').raw().elements[i];
            if (element.id.substr(0, 8) == 'default_') {
                $('#' + element.id.substr(8)).raw().checked = element.checked;
            }
        }
    }
    //]]>
</script>
<div class="LayoutBody">
    <div class="BodyHeader">
        <h2 class="BodyHeader-nav"><?= Users::format_username($UserID, false, false, false) ?> &gt; <?= t('server.tools.user_permission_manage') ?></h2>
    </div>
    <form class="manage_form" name="permissions" id="permissionsform" method="post" action="">
        <input type="hidden" name="action" value="permissions" />
        <input type="hidden" name="auth" value="<?= $LoggedUser['AuthKey'] ?>" />
        <input type="hidden" name="id" value="<?= $_REQUEST['userid'] ?>" />

        <table class="Form-rowList layout permission_head" variant="header">
            <tr class="Form-rowHeader">
                <td>
                    <?= t('server.tools.user_permission_manage') ?>
                </td>
                <td class="Form-actions">
                    <a href="#" onclick="reset(); return false;" class="brackets"><?= t('server.user.reset') ?></a>
                </td>
            </tr>
            <tr class="Form-row">
                <td>
                    <i> <?= t('server.tools.user_permission_note') ?></i>
                </td>
            </tr>
            <tr class="Form-row">
                <td class="Form-label"><?= t('server.tools.max_collages') ?></td>
                <td class="Form-inputs"><input class="is-small Input" type="text" name="maxcollages" size="5" value="<?= ($MaxCollages ? $MaxCollages : '0') ?>" /></td>
            </tr>
            <?
            permissions_form();
            ?>
        </table>

    </form>
</div>
<? View::show_footer(); ?>