<?php
function forumList($forums, $selected = 0) {
    $return = '';
    $cat = '';
    foreach ($forums as $id => $forum) {
        if ($cat !== $forum['Category']) {
            if ($cat !== '') {
                $return .= '</optgroup>';
            }
            $cat = $forum['Category'];
            $return .= sprintf('<optgroup class="Select-group" label="%s">', $cat);
        }

        $return .= sprintf('<option class="Select-option" value="%s"%s>%s</option>', $id, $id == $selected ? ' selected="selected"' : '', $forum['Name']);
    }
    if ($cat !== '') {
        $return .= '</optgroup>';
    }

    return $return;
}

function classList($Selected = 0) {
    global $Classes;
    $Return = '';
    foreach ($Classes as $ID => $Class) {
        if ($Class['Secondary']) {
            continue;
        }

        $Name = $Class['Name'];
        $Level = $Class['Level'];
        $Return .= "<option value=\"$Level\"";
        if ($Selected == $Level) {
            $Return .= ' selected="selected"';
        }
        $Return .= '>' . shortenString($Name, 20, true) . "</option>\n";
    }
    reset($Classes);
    return $Return;
}

if (!check_perms('admin_manage_forums')) {
    error(403);
}

if (isset($_GET['userid'])) {
    $user = $_GET['userid'];
} else {
    $user = $LoggedUser['ID'];
}

$DB->prepared_query('
    SELECT f.ID, f.Name, fc.Name AS Category
    FROM forums f
    INNER JOIN forums_categories fc ON f.CategoryID = fc.ID
    ORDER BY fc.Sort, f.Sort');
$forums = $DB->to_array('ID', MYSQLI_ASSOC);

$items = Forums::get_transitions($user);

View::show_header('Forum Transitions');
?>
<div class="BodyHeader">
    <h2 class="BodyHeader-nav">Forum transition manager</h2>
</div>
<div class="thin box">
    <h4>Preview transitions</h4>
    <form class="manage_form" name="preview" action="" method="get">
        <input type="hidden" name="action" value="forum_transitions" />
        <table class="layout">
            <tr>
                <td class="label"><label for="userid">User ID</label></td>
                <td><input class="Input" type="text" name="userid" value="<?= $user ?>" /></td>
                <td><input class="Button" type="submit" name="submit" value="<?= t('server.common.submit') ?>" /></td>
            <tr>
        </table>
    </form>
</div>
<h4 class="center">All permission columns are ORed to check for access, any value with a minus (-) in front will invalidate the transition for the user regardless of other permissions</h4>
<table class="Table">
    <tr class="Table-rowHeader"">
        <td class=" Table-cell">Source</td>
        <td class="Table-cell">Destination</td>
        <td class="Table-cell">Label</td>
        <td class="Table-cell">Secondary Classes</td>
        <td class="Table-cell">User Class</td>
        <td class="Table-cell">Permissions</td>
        <td class="Table-cell">User IDs</td>
        <td class="Table-cell">Submit</td>
    </tr>
    <?php
    $row = 'b';
    foreach ($items as $i) {
        list($id, $source, $destination, $label, $secondaryClasses, $userClass, $permissions, $userIds) = array_values($i);
    ?>
        <tr class="Table-row">
            <form class="manage_form" name="navitems" action="" method="post">
                <input type="hidden" name="id" value="<?= $id ?>" />
                <input type="hidden" name="action" value="forum_transitions_alter" />
                <input type="hidden" name="auth" value="<?= $LoggedUser['AuthKey'] ?>" />
                <td class="Table-cell">
                    <select class="Input" name="source">
                        <?= forumList($forums, $source) ?>
                    </select>
                </td>
                <td class="Table-cell">
                    <select class="Input" name="destination">
                        <?= forumList($forums, $destination) ?>
                    </select>
                </td>
                <td class="Table-cell">
                    <input class="Input" type="text" name="label" value="<?= $label ?>" />
                </td>
                <td class="Table-cell">
                    <input class="Input" type="text" value="<?= $secondaryClasses ?>" />
                </td>
                <td class="Table-cell">
                    <select class="Input" name="permission_class">
                        <?= classList($userClass) ?>
                    </select>
                </td>
                <td class="Table-cell">
                    <input class="Input" type="text" name="permissions" value="<?= $permissions ?>" />
                </td>
                <td class="Table-cell">
                    <input class="Input" type="text" name="user_ids" value="<?= $userIds ?>" />
                </td>
                <td class="Table-cell">
                    <input class="Button" type="submit" name="submit" value="Edit" />
                    <input class="Button" type="submit" name="submit" value="Delete" onclick="return confirm('Are you sure you want to delete this transition? This is an irreversible action!')" />
                </td>
            </form>
        </tr>
    <?php } ?>
    <tr class="Table-row">
        <td class="Table-cell" colspan="5">Create Transition</td>
    </tr>
    <tr class="Table-row">
        <form class="manage_form" name="navitems" action="" method="post">
            <input type="hidden" name="action" value="forum_transitions_alter" />
            <input type="hidden" name="auth" value="<?= $LoggedUser['AuthKey'] ?>" />
            <td class="Table-cell">
                <select class="Input" name="source">
                    <?= forumList($forums) ?>
                </select>
            </td>
            <td class="Table-cell">
                <select class="Input" name="destination">
                    <?= forumList($forums) ?>
                </select>
            </td>
            <td class="Table-cell">
                <input class="Input" type="text" name="label" />
            </td>
            <td class="Table-cell">
                <input class="Input" type="text" />
            </td>
            <td class="Table-cell">
                <select class="Input" name="permission_class">
                    <?= classList() ?>
                </select>
            </td>
            <td class="Table-cell">
                <input class="Input" type="text" name="permissions" />
            </td>
            <td class="Table-cell">
                <input class="Input" type="text" name="user_ids" />
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