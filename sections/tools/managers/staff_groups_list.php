<?php
if (!check_perms('admin_manage_permissions')) {
    error(403);
}

$DB->prepared_query("
		SELECT ID, Sort, Name
		FROM staff_groups
		ORDER BY Sort");
$Groups = $DB->to_array(false, MYSQLI_ASSOC);

View::show_header(t('server.tools.staff_group_management'));
?>
<div class="LayoutBody">
    <div class="BodyHeader">
        <h2 class="BodyHeader-nav"><?= t('server.tools.staff_group_management') ?></h2>
    </div>
    <form class="create_form" name="groups" action="" method="post">
        <input type="hidden" name="action" value="staff_groups_alter" />
        <input type="hidden" name="auth" value="<?= $LoggedUser['AuthKey'] ?>" />
        <table class="Form-rowList" variant="header">
            <tr class="Form-rowHeader">
                <td>
                    <?= t('server.tools.create_group') ?>
                </td>
            </tr>
            <tr class="Form-row">
                <td class="Form-label">
                    <?= t('server.tools.sort') ?>
                </td>
                <td class="Form-inputs">
                    <input class="Input is-small" type="text" size="10" name="sort" />
                </td>
            </tr>
            <tr class="Form-row">
                <td class="Form-label">
                    <?= t('server.tools.name') ?>
                </td>
                <td class="Form-inputs">
                    <input class="Input" type="text" size="30" name="name" />
                </td>
            </tr>
            <tr class="Form-row">
                <td>
                    <button class="Button" type="submit" name="submit" value="Create"><?= t('server.common.new') ?></button>
                </td>
            </tr>
        </table>
    </form>
    <table class="Table">
        <tr class="Table-rowHeader">
            <td class="Table-cell"><?= t('server.tools.sort') ?></td>
            <td class="Table-cell"><?= t('server.tools.name') ?></td>
            <td class="Table-cell "><?= t('server.tools.submit') ?></td>
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
                    <td class="Table-cell ">
                        <button class="Button" type="submit" name="submit" value="Edit"><?= t('server.common.edit') ?></button>
                        <button class="Button" type="submit" name="submit" value="Delete" onclick="return confirm('<?= t('server.tools.sure_delete_staff_group_title') ?>')"><?= t('server.common.delete') ?></button>
                    </td>
                </form>
            </tr>
        <?php
        }
        ?>

    </table>

</div>
<?php
View::show_footer();
?>