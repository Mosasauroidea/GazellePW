<?
View::show_header(t('server.collages.new_create'), '', 'PageCollageNew');

if (!check_perms('site_collages_renamepersonal')) {
    $ChangeJS = " onchange=\"if ( this.options[this.selectedIndex].value == '0') { $('#namebox').ghide(); $('#personal').gshow(); } else { $('#namebox').gshow(); $('#personal').ghide(); }\"";
}

if (!check_perms('site_collages_renamepersonal') && $Category === '0') {
    $NoName = true;
}
?>
<div class="LayoutBody">
    <div class="BodyHeader">
        <h2 class="BodyHeader-nav"><?= t('server.collages.collage') ?> </h2>
    </div>
    <?
    if (isset($Err)) { ?>
        <div class="save_message u-colorWarning"><?= $Err ?></div>
        <br />
    <?
    } ?>
    <div class="Box">
        <div class="Box-header"><?= t('server.collages.selected_collage_category') ?>
        </div>
        <div class="Box-body">
            <?= t('server.collages.new_note') ?>
            <ul>
                <?= t('server.collages.new_category_note') ?>
                <?
                if (($CollageCount < $LoggedUser['Permissions']['MaxCollages']) && check_perms('site_collages_personal')) { ?>
                    <?= t('server.collages.new_category_note2') ?>
                <?  } ?>
            </ul>
        </div>

    </div>
    <form class="create_form" name="collage" action="collages.php" method="post">
        <input type="hidden" name="action" value="new_handle" />
        <input type="hidden" name="auth" value="<?= $LoggedUser['AuthKey'] ?>" />
        <table class="Form-rowList layout" variant="header">
            <tr class="Form-rowHeader">
                <td><?= t('server.collages.create_collages') ?></td>
            </tr>
            <tr class="Form-row" id="collagename">
                <td class="Form-label"><strong><?= t('server.collages.new_name') ?>:</strong></td>
                <td class="Form-inputs">
                    <input class="Input" type="text" <?= $NoName ? ' class="hidden"' : ''; ?> name="name" size="60" id="namebox" value="<?= display_str($Name) ?>" />
                    <span id="personal" <?= $NoName ? '' : ' class="hidden"'; ?> style="font-style: oblique;"><strong><?= $LoggedUser['Username'] ?><?= t('server.collages.user_s_personal_collage') ?></strong></span>
                </td>
            </tr>
            <tr class="Form-row">
                <td class="Form-label"><strong><?= t('server.collages.new_category') ?>:</strong></td>
                <td class="Form-inputs">
                    <select class="Input" name="category" <?= $ChangeJS ?>>
                        <?
                        foreach ($CollageCats as $CatID) { ?>
                            <option class="Select-option" value="<?= $CatID ?>" <?= (($CatID == $Category) ? ' selected="selected"' : '') ?>><?= t('server.collages.collagecats')[$CatID] ?></option>
                        <?
                        }
                        ?>
                    </select>

                </td>
            </tr>
            <tr class="Form-row">
                <td class="Form-label"><?= t('server.collages.new_description') ?>:</td>
                <td class="Form-items">
                    <? new TEXTAREA_PREVIEW("description", "description", display_str($Description)) ?>
                </td>
            </tr>
            <tr class="Form-row">
                <td class="Form-label"><strong><?= t('server.collages.tags') ?>:</strong></td>
                <td class="Form-inputs">
                    <input class="Input" type="text" id="tags" name="tags" size="60" value="<?= display_str($Tags) ?>" />
                </td>
            </tr>
            <tr class="Form-row">
                <td colspan="2" class="center"><input class="Button" type="submit" value="<?= t('server.collages.new_create') ?>" /></td>
            </tr>
        </table>
    </form>
</div>
<? View::show_footer(); ?>