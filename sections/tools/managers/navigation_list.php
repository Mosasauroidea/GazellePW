<?php
if (!check_perms('admin_manage_forums')) {
    error(403);
}

$Items = Users::get_nav_items();

View::show_header('Navigation Links');
?>
<div class="BodyHeader">
    <h2 class="BodyHeader-nav">Navigation link manager</h2>
</div>
<table class="Table">
    <tr class="Table-rowHeader">
        <td class="Table-cell">Tag</td>
        <td class="Table-cell">Title</td>
        <td class="Table-cell">Target</td>
        <td class="Table-cell">Tests</td>
        <td class="Table-cell">Test User</td>
        <td class="Table-cell">Mandatory</td>
        <td class="Table-cell">Default</td>
        <td class="Table-cell">Submit</td>
    </tr>
    <?php
    $Row = 'b';
    foreach ($Items as $i) {
        list($ID, $Tag, $Title, $Target, $Tests, $TestUser, $Mandatory, $Initial) = array_values($i);
    ?>
        <tr class="Table-row">
            <form class="manage_form" name="navitems" action="" method="post">
                <input type="hidden" name="id" value="<?= $ID ?>" />
                <input type="hidden" name="action" value="navigation_alter" />
                <input type="hidden" name="auth" value="<?= $LoggedUser['AuthKey'] ?>" />
                <td class="Table-cell">
                    <input class="Input" type="text" name="tag" value="<?= $Tag ?>" />
                </td>
                <td class="Table-cell">
                    <input class="Input" type="text" name="title" value="<?= $Title ?>" />
                </td>
                <td class="Table-cell">
                    <input class="Input" type="text" name="target" value="<?= $Target ?>" />
                </td>
                <td class="Table-cell">
                    <input class="Input" type="text" name="tests" value="<?= $Tests ?>" />
                </td>
                <td class="Table-cell">
                    <input type="checkbox" name="testuser" <?= ($TestUser == '1') ? ' checked="checked"' : '' ?> />
                </td>
                <td class="Table-cell">
                    <input type="checkbox" name="mandatory" <?= ($Mandatory == '1') ? ' checked="checked"' : '' ?> />
                </td>
                <td class="Table-cell">
                    <input type="checkbox" name="default" <?= ($Initial == '1') ? ' checked="checked"' : '' ?> />
                </td>
                <td class="Table-cell">
                    <input class="Button" type="submit" name="submit" value="Edit" />
                    <input class="Button" type="submit" name="submit" value="Delete" onclick="return confirm('Are you sure you want to delete this link? This is an irreversible action!')" />
                </td>
            </form>
        </tr>
    <?php
    } ?>
    <tr class="Table-rowHeader">
        <td class="Table-cell" colspan="5">Create Link</td>
    </tr>
    <tr class="Table-row">
        <form class="manage_form" name="navitems" action="" method="post">
            <input type="hidden" name="action" value="navigation_alter" />
            <input type="hidden" name="auth" value="<?= $LoggedUser['AuthKey'] ?>" />
            <td class="Table-cell">
                <input class="Input" type="text" name="tag" />
            </td>
            <td class="Table-cell">
                <input class="Input" type="text" name="title" />
            </td>
            <td class="Table-cell">
                <input class="Input" type="text" name="target" />
            </td>
            <td class="Table-cell">
                <input class="Input" type="text" name="tests" />
            </td>
            <td class="Table-cell">
                <input type="checkbox" name="testuser" />
            </td>
            <td class="Table-cell">
                <input type="checkbox" name="mandatory" />
            </td>
            <td class="Table-cell">
                <input type="checkbox" name="default" />
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