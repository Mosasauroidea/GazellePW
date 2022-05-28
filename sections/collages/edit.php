<?
if (!empty($_GET['collageid']) && is_number($_GET['collageid'])) {
    $CollageID = $_GET['collageid'];
}
if (!is_number($CollageID)) {
    error(0);
}

$DB->query("
	SELECT Name, Description, TagList, UserID, CategoryID, Locked, MaxGroups, MaxGroupsPerUser, Featured
	FROM collages
	WHERE ID = '$CollageID'");
list($Name, $Description, $TagList, $UserID, $CategoryID, $Locked, $MaxGroups, $MaxGroupsPerUser, $Featured) = $DB->next_record();
$TagList = implode(', ', explode(' ', $TagList));

if ($CategoryID == $PersonalCollageCategoryCat && $UserID != $LoggedUser['ID'] && !check_perms('site_collages_delete')) {
    error(403);
}
if ($CategoryID != $PersonalCollageCategoryCat && !check_perms('site_collages_manage') && $UserID != $LoggedUser['ID']) {
    error(403);
}

View::show_header(Lang::get('collages', 'edit_collage'), '', 'PageCollageEdit');

if (!empty($Err)) {
    if (isset($ErrNoEscape)) {
        echo '<div class="save_message u-colorWarning">' . $Err . '</div>';
    } else {
        echo '<div class="save_message u-colorWarning">' . display_str($Err) . '</div>';
    }
}


?>
<div class="LayoutBody">
    <div class="BodyHeader">
        <h2 class="BodyHeader-nav"><?= Lang::get('collages', 'collage') ?> </h2>
    </div>
    <form class="edit_form" name="collage" action="collages.php" method="post">
        <input type="hidden" name="action" value="edit_handle" />
        <input type="hidden" name="auth" value="<?= $LoggedUser['AuthKey'] ?>" />
        <input type="hidden" name="collageid" value="<?= $CollageID ?>" />
        <table id="edit_collage" class="Form-rowList" variant="header">
            <tr class="Form-rowHeader">
                <td class="Form-title">
                    <?= Lang::get('collages', 'edit_collage') ?> > <a href="collages.php?id=<?= $CollageID ?>"><?= $Name ?></a>
                </td>
            </tr>
            <tr class="Form-row">
                <td class="Form-label"><?= Lang::get('collages', 'name') ?></td>
                <td class="Form-inputs"><input class="Input" type="text" name="name" size="60" value="<?= $Name ?>" /></td>
            </tr>
            <?
            if ($CategoryID  != $PersonalCollageCategoryCat || check_perms('site_collages_delete')) { ?>
                <tr class="Form-row">
                    <td class="Form-label"><strong><?= Lang::get('collages', 'category') ?></strong></td>
                    <td class="Form-inputs">
                        <select class="Input" name="category">
                            <?
                            foreach ($CollageCats as $CatID) {
                                if (!check_perms('site_collages_delete') && $CatID == $PersonalCollageCategoryCat) {
                                    // Only mod-type get to make things personal
                                    continue;
                                }
                            ?>
                                <option class="Select-option" value="<?= $CatID ?>" <?= $CatID == $CategoryID ? ' selected="selected"' : '' ?>><?= Lang::get('collages', 'collagecats')[$CatID] ?></option>
                            <?  } ?>
                        </select>
                    </td>
                </tr>
            <?  } ?>

            <tr class="Form-row">
                <td class="Form-label"><?= Lang::get('collages', 'description') ?></td>
                <td class="Form-inputs">
                    <textarea class="Input" name="description" id="description" cols="60" rows="10"><?= $Description ?></textarea>
                </td>
            </tr>
            <tr class="Form-row">
                <td class="Form-label"><?= Lang::get('collages', 'edit_tags') ?></td>
                <td class="Form-items"><input class="Input" type="text" name="tags" size="60" value="<?= $TagList ?>" /></td>
            </tr>
            <? if ($CategoryID == $PersonalCollageCategoryCat) { /* CategoryID == 0 is for "personal" collages */ ?>
                <tr class="Form-row">
                    <td class="Form-label"><span data-tooltip="<?= Lang::get('collages', 'featured_title') ?>"><?= Lang::get('collages', 'featured') ?></span></td>
                    <td class="Form-inputs"><input type="checkbox" name="featured" <?= ($Featured ? ' checked="checked"' : '') ?> /></td>
                </tr>
            <?
            }
            if (check_perms('site_collages_delete')) { ?>
                <tr class="Form-row">
                    <td class="Form-label"><?= Lang::get('collages', 'locked') ?></td>
                    <td class="Form-inputs"><input type="checkbox" name="locked" <?= $Locked ? 'checked="checked" ' : '' ?> /></td>
                </tr>
                <tr class="Form-row">
                    <td class="Form-label"><?= Lang::get('collages', 'max_groups') ?></td>
                    <td class="Form-inputs"><input class="Input is-small" type="text" name="maxgroups" size="5" value="<?= $MaxGroups ?>" /></td>
                </tr>
                <tr class="Form-row">
                    <td class="Form-label"><?= Lang::get('collages', 'max_groups_per_user') ?></td>
                    <td class="Form-inputs"><input class="Input is-small" type="text" name="maxgroupsperuser" size="5" value="<?= $MaxGroupsPerUser ?>" />
                    </td>
                </tr>

            <? } ?>
            <tr class="Form-row">
                <td colspan="2" class="center"><input class="Button" type="submit" value="<?= Lang::get('global', 'edit') ?>" /></td>
            </tr>
        </table>
    </form>
</div>
<? View::show_footer(); ?>