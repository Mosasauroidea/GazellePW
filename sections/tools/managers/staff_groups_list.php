<?php
if (!check_perms('admin_manage_permissions')) {
    error(403);
}

$DB->prepared_query("
		SELECT ID, Sort, Name
		FROM staff_groups
		ORDER BY Sort");
$Groups = $DB->to_array(false, MYSQLI_ASSOC);

View::show_header('Staff Group Management');
?>
<div class="BodyHeader">
    <h2 class="BodyHeader-nav"><?= Lang::get('tools', 'staff_group_management') ?></h2>
</div>
<table class="Table">
    <tr class="Table-rowHeader">
        <td class="Table-cell"><?= Lang::get('tools', 'sort') ?></td>
        <td class="Table-cell"><?= Lang::get('tools', 'name') ?></td>
        <td class="Table-cell"><?= Lang::get('tools', 'submit') ?></td>
    </tr>
    <?php
    $Row = 'b';
    foreach ($Groups as $g) {
        list($ID, $Sort, $Name) = array_values($g);
    ?>
        <tr class="Table-row">
            <form class="manage_form" name="accounts" action="" method="post">
                <input type="hidden" name="id" value="<?= $ID ?>" />
                <input type="hidden" name="action" value="staff_groups_alter" />
                <input type="hidden" name="auth" value="<?= $LoggedUser['AuthKey'] ?>" />
                <td class="Table-cell">
                    <input class="Input" type="text" size="10" name="sort" value="<?= $Sort ?>" />
                </td>
                <td class="Table-cell">
                    <input class="Input" type="text" size="30" name="name" value="<?= $Name ?>" />
                </td>
                <td class="Table-cell">
                    <input class="Button" type="submit" name="submit" value="Edit" />
                    <input class="Button" type="submit" name="submit" value="Delete" onclick="return confirm('<?= Lang::get('tools', 'sure_delete_staff_group_title') ?>')" />
                </td>
            </form>
        </tr>
    <?php
    }
    ?>
    <tr class="Table-rowHeader">
        <td class="Table-cell" colspan="3"><?= Lang::get('tools', 'create_group') ?></td>
    </tr>
    <tr class="Table-row">
        <form class="create_form" name="groups" action="" method="post">
            <input type="hidden" name="action" value="staff_groups_alter" />
            <input type="hidden" name="auth" value="<?= $LoggedUser['AuthKey'] ?>" />
            <td class="Table-cell">
                <input class="Input" type="text" size="10" name="sort" />
            </td>
            <td class="Table-cell">
                <input class="Input" type="text" size="30" name="name" />
            </td>
            <td class="Table-cell">
                <input class="Button" type="submit" name="submit" value="Create" />
            </td>
        </form>
    </tr>
</table>
<?php
View::show_footer();
?>