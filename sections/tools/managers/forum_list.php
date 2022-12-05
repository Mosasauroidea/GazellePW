<?
function class_list($Selected = 0) {
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
        $Return .= '>' . Format::cut_string($Name, 20, 1) . "</option>\n";
    }
    reset($Classes);
    return $Return;
}
function ed_class_list($Second) {
    global $Classes;
    $Return = "<div class='u-hstack'>";
    $SecondArray = unserialize(base64_decode($Second));
    foreach ($Classes as $Class) {
        if ($Class['Secondary']) {
            $Name = $Class['Name'];
            $ID = $Class['ID'];
            $Return .= "<label class='Checkbox'><input class='Input' type='checkbox' name='second[]' value='$ID' " .
                (in_array($ID, $SecondArray) ? "checked='checked'" : "") .
                "/>$Name</label>";
        }
    }
    $Return .= "</div>";
    reset($Classes);
    return $Return;
}
if (!check_perms('admin_manage_forums')) {
    error(403);
}

View::show_header(t('server.tools.forum_management'));
$DB->query('
	SELECT ID, Name
	FROM forums
	ORDER BY Sort');
$ForumArray = $DB->to_array(); // used for generating the 'parent' drop down list

// Replace the old hard-coded forum categories
unset($ForumCats);
$ForumCats = $Cache->get_value('forums_categories');
if ($ForumCats === false) {
    $DB->query('
		SELECT ID, Name
		FROM forums_categories
		ORDER BY Sort, Name');
    $ForumCats = array();
    while (list($ID, $Name) = $DB->next_record()) {
        $ForumCats[$ID] = $Name;
    }
    $Cache->cache_value('forums_categories', $ForumCats, 0); //Inf cache.
}

$DB->query('
	SELECT
		f.ID,
		CategoryID,
		f.Sort,
		f.Name,
		Description,
		MinClassRead,
		MinClassWrite,
		MinClassCreate,
		AutoLock,
		AutoLockWeeks,
		Second
	FROM forums AS f
	LEFT JOIN forums_categories AS fc ON fc.ID = f.CategoryID
	ORDER BY fc.Sort, fc.Name, f.CategoryID, f.Sort, f.Name');
?>
<div class="LayoutBody">
    <div class="BodyHeader">
        <div class="BodyHeader-nav"><?= t('server.tools.forum_management') ?></div>
        <div class="BodyHeader-subNav"><?= t('server.tools.forum_control_panel') ?></div>
        <div class="BodyNavLinks">
            <a href="tools.php?action=categories" class="brackets"><?= t('server.tools.category_manager') ?></a>
        </div>

    </div>
    <div class="TableContainer">
        <table class="TableForumControlPanel Table">
            <tr class="Table-rowHeader">
                <td class="Table-cell is-category"><?= t('server.tools.category') ?></td>
                <td class="Table-cell is-sort"><?= t('server.tools.sort') ?></td>
                <td class="Table-cell is-name"><?= t('server.tools.name') ?></td>
                <td class="Table-cell is-description"><?= t('server.tools.description') ?></td>
                <td class="Table-cell is-minClassRead"><?= t('server.tools.min_class_read') ?></td>
                <td class="Table-cell is-minClassWrite"><?= t('server.tools.min_class_write') ?></td>
                <td class="Table-cell is-minClassCrate"><?= t('server.tools.min_class_create') ?></td>
                <td class="Table-cell is-autoLock"><?= t('server.tools.auto_lock') ?></td>
                <td class="Table-cell is-autoLockWeeks"><?= t('server.tools.auto_lock_weeks') ?></td>
                <td class="Table-cell is-operation"><?= t('server.tools.operation') ?></td>
            </tr>
            <?
            while (list($ID, $CategoryID, $Sort, $Name, $Description, $MinClassRead, $MinClassWrite, $MinClassCreate, $AutoLock, $AutoLockWeeks, $Second) = $DB->next_record()) {
            ?>
                <form class="manage_form" name="forums" action="" method="post">
                    <tr class="Table-row">
                        <input type="hidden" name="id" value="<?= $ID ?>" />
                        <input type="hidden" name="action" value="forum_alter" />
                        <input type="hidden" name="auth" value="<?= $LoggedUser['AuthKey'] ?>" />
                        <td class="Table-cell is-category">
                            <a href="javascript:$('#forum_' + <?= $ID ?>).toggle();">+ </a><select class="Input" name="categoryid">
                                <? reset($ForumCats);
                                foreach ($ForumCats as $CurCat => $CatName) {
                                ?>
                                    <option class="Select-option" value="<?= $CurCat ?>" <? if ($CurCat == $CategoryID) {
                                                                                                echo ' selected="selected"';
                                                                                            } ?>><?= $CatName ?></option>
                                <? } ?>
                            </select>
                        </td>
                        <td class="Table-cell is-sort">
                            <input class="Input" type="text" size="3" name="sort" value="<?= $Sort ?>" />
                        </td>
                        <td class="Table-cell is-name">
                            <input class="Input" type="text" size="10" name="name" value="<?= $Name ?>" />
                        </td>
                        <td class="Table-cell is-description">
                            <input class="Input" type="text" size="20" name="description" value="<?= $Description ?>" />
                        </td>
                        <td class="Table-cell is-minClassRead">
                            <select class="Input" name="minclassread">
                                <?= class_list($MinClassRead) ?>
                            </select>
                        </td>
                        <td class="Table-cell is-minClassWrite">
                            <select class="Input" name="minclasswrite">
                                <?= class_list($MinClassWrite) ?>
                            </select>
                        </td>
                        <td class="Table-cell is-minClassCreate">
                            <select class="Input" name="minclasscreate">
                                <?= class_list($MinClassCreate) ?>
                            </select>
                        </td>
                        <td class="Table-cell is-autoLock">
                            <input type="checkbox" name="autolock" <?= ($AutoLock == '1') ? ' checked="checked"' : '' ?> />
                        </td>
                        <td class="Table-cell is-autoLockWeeks">
                            <input class="Input" type="text" name="autolockweeks" value="<?= $AutoLockWeeks ?>" />
                        </td>
                        <td class="Table-cell is-operatioin">
                            <button class="Button" type="submit" name="submit" value="Edit"><?= t('server.common.edit') ?></button>
                            <button class="Button" type="submit" name="submit" value="Delete" onclick="return confirm('<?= t('client.common.are_you_sure_cannot_undone') ?>')"><?= t('server.common.delete') ?></button>
                        </td>
                    </tr>
                    <tr class="Table-row is-classList" id="forum_<?= $ID ?>" style="display:none;">
                        <td class="Table-cell is-classList" colspan="10">
                            <?= ed_class_list($Second) ?>
                        </td>
                    </tr>
                </form>
            <? } ?>
        </table>

    </div>
    <form class="create_form" name="forum" action="" method="post">
        <input type="hidden" name="action" value="forum_alter" />
        <input type="hidden" name="auth" value="<?= $LoggedUser['AuthKey'] ?>" />

        <table class="Form-rowList" variant="header">
            <tr class="Form-rowHeader">
                <td>
                    <?= t('server.tools.create_forum') ?>
                </td>
            </tr>
            <tr class="Form-row">
                <td class="Form-label">
                    <?= t('server.tools.category') ?>
                </td>
                <td class="Form-inputs">
                    <select class="Input" name="categoryid">
                        <? reset($ForumCats);
                        while (list($CurCat, $CatName) = each($ForumCats)) { ?>
                            <option class="Select-option" value="<?= $CurCat ?>" <? if ($CurCat == $CategoryID) {
                                                                                        echo ' selected="selected"';
                                                                                    } ?>><?= $CatName ?></option>
                        <?  } ?>
                    </select>
                </td>
            </tr>
            <tr class="Form-row">
                <td class="Form-label">
                    <?= t('server.tools.sort') ?>
                </td>
                <td class="Form-inputs">
                    <input class="Input" type="text" size="3" name="sort" />
                </td>
            </tr>
            <tr class="Form-row">
                <td class="Form-label">
                    <?= t('server.tools.name') ?>
                </td>
                <td class="Form-inputs">
                    <input class="Input" type="text" size="10" name="name" />
                </td>
            </tr>
            <tr class="Form-row">
                <td class="Form-label">
                    <?= t('server.tools.description') ?>
                </td>
                <td class="Form-inputs">
                    <input class="Input" type="text" size="20" name="description" />
                </td>
            </tr>
            <tr class="Form-row">
                <td class="Form-label">
                    <?= t('server.tools.min_class_read') ?>
                </td>
                <td class="Form-inputs">
                    <select class="Input" name="minclassread">
                        <?= class_list() ?>
                    </select>
                </td>
            </tr>
            <tr class="Form-row">
                <td class="Form-label">
                    <?= t('server.tools.min_class_write') ?>
                </td>
                <td class="Form-inputs">
                    <select class="Input" name="minclasswrite">
                        <?= class_list() ?>
                    </select>
                </td>
            </tr>
            <tr class="Form-row">
                <td class="Form-label">
                    <?= t('server.tools.min_class_create') ?>

                </td>
                <td class="Form-inputs">
                    <select class="Input" name="minclasscreate">
                        <?= class_list() ?>
                    </select>
                </td>
            </tr>
            <tr class="Form-row">
                <td class="Form-label">
                    <?= t('server.tools.auto_lock') ?>
                </td>
                <td class="Form-inputs">
                    <input type="checkbox" name="autolock" checked="checked" />
                </td>
            </tr>
            <tr class="Form-row">
                <td class="Form-label">
                    <?= t('server.tools.auto_lock_weeks') ?>
                </td>
                <td class="Form-inputs">
                    <input class="Input is-small" type="number" name="autolockweeks" value="4" />
                </td>
            </tr>
            <tr class="Form-row">
                <td>
                    <button class="Button" type="submit" value="Create"><?= t('server.common.new') ?></button>
                </td>

            </tr>
        </table>
    </form>
</div>

<? View::show_footer(); ?>