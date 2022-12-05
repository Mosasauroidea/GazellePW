<?
if (!check_perms('admin_manage_applicants')) {
    error(403);
}
View::show_header(t('server.apply.applicant_administration'), 'bbcode', 'PageApplyAdmin');
$EDIT_ID = 0;
$Saved   = '';
if (isset($_POST['auth'])) {
    authorize();
    $edit = array_filter($_POST, function ($x) {
        return preg_match('/^edit-\d+$/', $x);
    }, ARRAY_FILTER_USE_KEY);
    if (is_array($edit) && count($edit) == 1) {
        $EDIT_ID = trim(array_keys($edit)[0], 'edit-');
        $AppRole = ApplicantRole::factory($EDIT_ID);
    } elseif (isset($_POST['edit']) && is_numeric($_POST['edit'])) {
        $EDIT_ID = intval($_POST['edit']);
        $AppRole = ApplicantRole::factory($EDIT_ID);
        if (isset($_POST['user_id']) && is_numeric($_POST['user_id'])) {
            $user_id = intval($_POST['user_id']);
            if ($user_id == $LoggedUser['ID']) {
                $AppRole->update(
                    $_POST['title'],
                    $_POST['description'],
                    (isset($_POST['status']) && is_numeric($_POST['status']) && $_POST['status'] == 1)
                );
            }
            $EDIT_ID = 0; /* return to list */
            $Saved = t('server.apply.updated');
        }
    } else {
        $AppRole = new ApplicantRole(
            $_POST['title'],
            $_POST['description'],
            (isset($_POST['status']) && is_numeric($_POST['status']) && $_POST['status'] == 1),
            $LoggedUser['ID']
        );
        $Saved = t('server.apply.saved');
    }
}
?>

<div class="LayoutBody">
    <div class="BodyHeader">
        <h2 class="BodyHeader-nav"><?= t('server.apply.manage_roles_at') ?></h2>
        <div class="BodyNavLinks">
            <a href="/apply.php" class="brackets"><?= t('server.apply.apply') ?></a>
            <a href="/apply.php?action=view" class="brackets"><?= t('server.apply.current_applications') ?></a>
            <a href="/apply.php?action=view&status=resolved" class="brackets"><?= t('server.apply.resolved_applications') ?></a>
        </div>
    </div>

    <? if (!$EDIT_ID) {
        $Roles = ApplicantRole::get_list(true);
        if (count($Roles)) {
    ?>
            <div class="Group">
                <div class="Group-header">
                    <div class="Group-headerTitle">
                        <?= t('server.apply.current_roles') ?></div>
                </div>
                <div class="Group-body">
                    <? foreach ($Roles as $title => $info) { ?>
                        <form id="role_edit<?= $info['id'] ?>" method="post" class="LayoutBody" action="/apply.php?action=admin">
                            <div class="Box">
                                <div class="Box-header">
                                    <div class="Box-headerLeft">
                                        <div class="Box-headerTitle">
                                            <?= display_str($title) ?>
                                        </div>
                                        - <?= $info['published'] ? t('server.apply.published') : t('server.apply.archived') ?>
                                        - <?= $info['modified'] == $info['created'] ? time_diff($info['created'], 2) :  time_diff($info['modified'], 2) ?>
                                        - <?= t('server.apply.role_created_by', ['Values' => [Users::format_username($info['user_id'])]]) ?>
                                        <input type="hidden" name="edit-<?= $info['id'] ?>" value="Edit" />
                                    </div>
                                    <div class="Box-headerActions">
                                        <input type="hidden" name="auth" value="<?= $LoggedUser['AuthKey'] ?>" />
                                        <? if ($EDIT_ID) { ?>
                                            <input type="hidden" name="edit" value="<?= $EDIT_ID ?>" />
                                        <?  } ?>
                                        <input type="hidden" name="user_id" value="<?= $LoggedUser['ID'] ?>" />
                                        <a href="javascript:{}" onclick="document.getElementById('role_edit<?= $info['id'] ?>').submit();"><?= t('server.common.edit') ?></a>

                                    </div>
                                </div>
                                <div class="Box-body HtmlText PostArticle">
                                    <?= Text::full_format($info['description']) ?>
                                </div>
                            </div>
                        </form>
                    <? } /* foreach */ ?>
                </div>
            </div>
        <?
        } else {
        ?>
            <p><?= t('server.apply.no_current_roles') ?></p>
    <?  }
    } /* !$EDIT_ID */ ?>

    <form method="post" class="LayoutBody" action="/apply.php?action=admin">
        <div class="Form-rowList" variant="header">

            <?
            if (isset($AppRole)) {
                $checked_published = $AppRole->is_published() ? ' checked' : '';
                $checked_archived  = $AppRole->is_published() ? '' : ' checked';
            } else {
                $checked_published = '';
                $checked_archived  = ' checked';
            }
            ?>
            <table>
                <tr class="Form-rowHeader">
                    <td><?= $EDIT_ID ? t('server.apply.edit_role') : t('server.apply.new_role') ?><?= t('server.apply.space_role') ?></td>
                </tr>
                <tr class="Form-row">
                    <td class="Form-label"><?= t('server.apply.role_title') ?></td>
                    <td class="Form-inputs"><input class="Input" type="text" width="100" name="title" value="<?= $EDIT_ID ? $AppRole->title() : '' ?>" /></td>
                </tr>
                <tr class="Form-row">
                    <td class="Form-label"><?= t('server.apply.visibility') ?></td>
                    <td class="Form-inputs">
                        <input type="radio" name="status" value="1" id="status-pub" <?= $checked_published ?> /><label for="status-pub"><?= t('server.apply.published') ?></label><br />
                        <input type="radio" name="status" value="0" id="status-arch" <?= $checked_archived ?> /><label for="status-arch"><?= t('server.apply.archived') ?></label>
                    </td>
                </tr>
                <tr class="Form-row">
                    <td class="Form-label"><?= t('server.apply.description') ?></td>
                    <td class="Form-items">
                        <?
                        new TEXTAREA_PREVIEW('description', 'description', $EDIT_ID ? $AppRole->description() : '', 60, 8, true, true);
                        ?>
                    </td>
                <tr class="Form-row">
                    <td colspan="2">
                        <input type="hidden" name="auth" value="<?= $LoggedUser['AuthKey'] ?>" />
                        <? if ($EDIT_ID) { ?>
                            <input type="hidden" name="edit" value="<?= $EDIT_ID ?>" />
                        <?  } ?>
                        <input type="hidden" name="user_id" value="<?= $LoggedUser['ID'] ?>" />
                        <button class="Button" type="submit" id="submit" value="Save Role"><?= t('client.common.save') ?></button>
                    </td>
                </tr>
                </tr>
            </table>
        </div>
    </form>
</div>
<?
View::show_footer();
