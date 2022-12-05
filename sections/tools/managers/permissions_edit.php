<?php

$DB->prepared_query("
    SELECT ID, Name
    FROM staff_groups
    ORDER BY Sort");
$groups = $DB->to_array(false, MYSQLI_ASSOC);

View::show_header(t('server.tools.manage_permissions'), 'validate');

echo $Val->GenerateJS('permissionsform');
?>
<form class="manage_form" name="permissions" id="permissionsform" method="post" action="" onsubmit="return formVal();">
    <input type="hidden" name="action" value="permissions" />
    <input type="hidden" name="auth" value="<?= $LoggedUser['AuthKey'] ?>" />
    <input type="hidden" name="id" value="<?= display_str($_REQUEST['id']); ?>" />
    <div class="BodyNavLinks">
        <a href="tools.php?action=permissions" class="brackets"><?= t('server.tools.back_to_permission_list') ?></a>
        <a href="tools.php?action=privilege_matrix" class="brackets">Privilege Matrix</a>
    </div>
    <table class="permission_head layout">
        <tr>
            <td class="label"><?= t('server.tools.permission_name') ?></td>
            <td><input class="Input" type="text" name="name" id="name" value="<?= !empty($name) ? display_str($name) : '' ?>" /></td>
        </tr>
        <tr>
            <td class="label"><?= t('server.tools.class_level') ?></td>
            <td><input class="Input is-small" type="text" name="level" id="level" value="<?= !empty($level) ? display_str($level) : '' ?>" /></td>
        </tr>
        <tr>
            <td class="label"><?= t('server.tools.secondary_class') ?></td>
            <td><input type="checkbox" name="secondary" value="1" <?= !empty($secondary) ? ' checked="checked"' : '' ?> /></td>
        </tr>
        <tr>
            <td class="label"><?= t('server.tools.show_on_staff_page') ?></td>
            <td><input type="checkbox" name="displaystaff" value="1" <?= !empty($displayStaff) ? ' checked="checked"' : '' ?> /></td>
        </tr>
        <tr>
            <td class="label"><?= t('server.tools.staff_page_group') ?></td>
            <td>
                <select class="Input" name="staffgroup" id="staffgroup">
                    <?php foreach ($groups as $group) { ?>
                        <option class="Select-option" value="<?= $group['ID'] ?>" <?= $group['ID'] == $staffGroup ? ' selected="selected"' : '' ?>><?= $group['Name'] ?></option>
                    <?php } ?>
                </select>
            </td>
        </tr>
        <tr>
            <td class="label"><?= t('server.tools.additional_forums') ?></td>
            <td><input class="Input" type="text" size="30" name="forums" value="<?= display_str($forums) ?>" /></td>
        </tr>
        <?php if ($secondary) { ?>
            <tr>
                <td class="label">Badge</td>
                <td><input class="Input" type="text" size="30" name="badge" value="<?= $badge ?>" /></td>
            </tr>
        <?php
        }
        if (is_numeric($id)) {
        ?>
            <tr>
                <td class="label">Current users in this class</td>
                <td><?= number_format($userCount) ?>&nbsp;<a href="/user.php?action=search&class[]=<?= $id ?>" class="brackets">View</a></td>
            </tr>
        <?php } ?>
    </table>
    <?= G::$Twig->render('admin/privilege-list.twig', ['default' => null, 'user' => $values]); ?>
</form>
<?php
View::show_footer();
