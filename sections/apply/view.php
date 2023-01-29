<?
View::show_header(t('server.apply.view_applications'), 'apply', 'PageApplyView');
$IS_STAFF = check_perms('admin_manage_applicants'); /* important for viewing the full story and full applicant list */
if (isset($_POST['id']) && is_number($_POST['id'])) {
    authorize();
    $ID = intval($_POST['id']);
    $App = Applicant::factory($ID);
    if (!$IS_STAFF && $App->user_id() != $LoggedUser['ID']) {
        error(403);
    }
    $note_delete = array_filter($_POST, function ($x) {
        return preg_match('/^note-delete-\d+$/', $x);
    }, ARRAY_FILTER_USE_KEY);
    if (is_array($note_delete) && count($note_delete) == 1) {
        $App->delete_note(
            trim(array_keys($note_delete)[0], 'note-delete-')
        );
    } elseif (isset($_POST['resolve'])) {
        if ($_POST['resolve'] === 'Resolve') {
            $App->resolve(true);
        } elseif ($_POST['resolve'] === 'Reopen') {
            $App->resolve(false);
        }
    } elseif (isset($_POST['note_reply'])) {
        $App->save_note(
            $LoggedUser['ID'],
            $_POST['note_reply'],
            $IS_STAFF && $_POST['visibility'] == 'staff' ? 'staff' : 'public'
        );
    }
    $Location = (empty($_SERVER['HTTP_REFERER'])) ? "/index.php" : $_SERVER['HTTP_REFERER']; // redirect back
    header("Location: /apply.php?action=view&id=$ID");
} elseif (isset($_GET['id']) && is_number($_GET['id'])) {
    $ID = intval($_GET['id']);
    $App = Applicant::factory($ID);
    if (!$IS_STAFF && $App->user_id() != $LoggedUser['ID']) {
        error(403);
    }
}
$Resolved = (isset($_GET['status']) && $_GET['status'] === 'resolved');
?>

<div class="LayoutBody">
    <div class="BodyHeader">
        <? if ($App) { ?>
            <div class="BodyHeader-nav head">
                <?= $App->role_title() ?>
            </div>
        <? } ?>
        <div class="BodyNavLinks">
            <a href="/apply.php" class="brackets"><?= t('server.apply.apply') ?></a>
            <? if (!$IS_STAFF && isset($ID)) { ?>
                <a href="/apply.php?action=view" class="brackets"><?= t('server.apply.view_your_application') ?></a>
                <?
            }
            if ($IS_STAFF) {
                if ($Resolved || (!$Resolved and isset($ID))) {
                ?>
                    <a href="/apply.php?action=view" class="brackets"><?= t('server.apply.current_applications') ?></a>
                <?
                }
                if (!$Resolved) {
                ?>
                    <a href="/apply.php?action=view&status=resolved" class="brackets"><?= t('server.apply.resolved_applications') ?></a>
                <?  } ?>
                <a href="/apply.php?action=admin" class="brackets"><?= t('server.apply.manage_roles') ?></a>
            <?
            }
            ?>
        </div>
    </div>

    <? if (isset($ID)) { ?>

        <? if ($IS_STAFF) { ?>
            <div class="Box" id="user_application_reply_box">
                <div class="Box-header">
                    <div class="Box-headerLeft">
                        <div class="Box-headerTitle">
                            <?= Users::format_username($App->user_id(), true, true, true, true, true, false) ?>
                        </div>
                        - <?= time_diff($App->created()) ?>
                    </div>
                    <div class="Box-headerActions">
                        <form name="role_resolve" id="form<?= $ID ?>" method="POST" action="/apply.php?action=view&amp;id=<?= $ID ?>">
                            <a href="javascript:{}" onclick="document.getElementById('form<?= $ID ?>').submit();"><?= $App->is_resolved() ? t('server.apply.reopen') : t('server.apply.resolve') ?></a>
                            <input type="hidden" name="resolve" value="<?= $App->is_resolved() ? 'Reopen' : 'Resolve' ?>" />
                            <input type="hidden" name="id" value="<?= $ID ?>" />
                            <input type="hidden" name="auth" value="<?= $LoggedUser['AuthKey'] ?>" />
                        </form>
                    </div>
                </div>
                <div class="Box-body HtmlText PostArticle" id="user_application_reply_text">
                    <p><?= Text::full_format($App->body()) ?></p>
                </div>
            </div>
        <?  } ?>
        <?
        foreach ($App->get_story() as $note) {
            if (!$IS_STAFF && $note['visibility'] == 'staff') {
                continue;
            }
            $UserName = (!$IS_STAFF && $note['user_id'] != $LoggedUser['ID']) ? "Staff" : Users::format_username($note['user_id'], true, true, true, true, true, false)
        ?>
            <div class="Box" style="border-color: <?= $IS_STAFF ? ($note['visibility'] == 'staff' ? '#FF8017' : '#347235') : '#808080' ?>;">
                <div class="Box-header u-flex">
                    <div class="Box-headerTitle">
                        <?= $UserName ?>
                    </div>
                    <div class="Box-headerActions">
                        <form id="note<?= $note['id'] ?>" name="thread_note_replay" method="POST" action="/apply.php?action=view&id=<?= $ID ?>">
                            <input type="hidden" name="id" value="<?= $ID ?>" />
                            <input type="hidden" name="auth" value="<?= $LoggedUser['AuthKey'] ?>" />
                            <?= time_diff($note['created'], 2) ?>
                            - <a href="javascript:{}" onclick="document.getElementById('note<?= $note['id'] ?>').submit();"><?= t('server.common.delete') ?></a>
                            <input type="hidden" name="note-delete-<?= $note['id'] ?>" value="delete" />
                        </form>
                    </div>
                </div>
                <div class="Box-body HtmlText PostArticle">
                    <?= Text::full_format($note['body']) ?>
                </div>
            </div>
        <? } /* foreach */ ?>
        <? if (!$App->is_resolved()) { ?>
            <form id="thread_note_reply" name="thread_note_replay" method="POST" action="/apply.php?action=view&amp;id=<?= $ID ?>">
                <table class="Form-rowList" variant="header">
                    <tr class="Form-rowHeader">
                        <td><?= t('server.inbox.reply') ?></td>
                    </tr>
                    <? if ($IS_STAFF) { ?>
                        <tr class="Form-row">
                            <td class="Form-label"><?= t('server.apply.visibility') ?>:</td>
                            <td class="Form-inputs">
                                <div>
                                    <input type="radio" name="visibility" value="public" /><label for="public"><?= t('server.apply.public') ?> <span style="color: #347235">(<?= t('server.apply.member_will_see_this_reply') ?>)</span></label>
                                </div>
                                <div>
                                    <input type="radio" name="visibility" value="staff" checked /><label for="staff"><?= t('server.apply.staff') ?> <span style="color: #FF8017">(<?= t('server.apply.only_staff_will_see_this_reply') ?>)</span></label>
                                </div>
                            </td>
                        </tr>
                    <? } /* $IS_STAFF */ ?>
                    <tr class="Form-row">
                        <td class="Form-label"><?= t('server.apply.reply') ?></td>
                        <td class="Form-items">
                            <? new TEXTAREA_PREVIEW('note_reply', 'note_reply', '', 60, 8, true, true) ?>
                        </td>
                    </tr>
                    <tr class="Form-row">
                        <td colspan="2">
                            <div style="text-align: center;">
                                <input type="hidden" name="id" value="<?= $ID ?>" />
                                <input type="hidden" name="auth" value="<?= $LoggedUser['AuthKey'] ?>" />
                                <button class="Button" type="submit" id="submit" value="Save"><?= t('server.apply.reply') ?></button>
                            </div>
                        </td>
                    </tr>
                <?  } /* !$App->is_resolved() */ ?>
                </table>
            </form>
        <?
    } else { /* no id parameter given -- show list of applicant entries - all if staff, otherwise their own (if any) */
        $Page            = isset($_GET['page']) && is_number($_GET['page']) ? intval($_GET['page']) : 1;
        $UserID          = $IS_STAFF ? 0 : $LoggedUser['ID'];
        $ApplicationList = Applicant::get_list($Page, $Resolved, $UserID);
        ?>
            <h2><?= $Resolved ? t('server.apply.resolved') : t('server.apply.current') ?><?= t('server.apply.blank_applications') ?></h2>
            <? if (count($ApplicationList)) { ?>
                <div class="TableContainer border">
                    <table class="TableResolvedApplication Table" id="resolved_application">
                        <tr class="Table-rowHeader">
                            <td class="Table-cell"><?= t('server.apply.role') ?></td>
                            <? if ($IS_STAFF) { ?>
                                <td class="Table-cell"><?= t('server.apply.applicant') ?></td>
                            <?      } ?>
                            <td class="Table-cell"><?= t('server.apply.date_created') ?></td>
                            <td class="Table-cell"><?= t('server.apply.comments') ?></td>
                            <td class="Table-cell"><?= t('server.apply.last_comment_from') ?></td>
                            <td class="Table-cell"><?= t('server.apply.last_comment_added') ?></td>
                        </tr>
                        <? foreach ($ApplicationList as $appl) { ?>
                            <tr class="Table-row">
                                <td class="Table-cell"><a href="/apply.php?action=view&amp;id=<?= $appl['ID'] ?>"><?= $appl['Role'] ?></a></td>
                                <? if ($IS_STAFF) { ?>
                                    <td class="Table-cell"><a href="/user.php?id=<?= $appl['UserID'] ?>"><?= $appl['Username'] ?></a></td>
                                <?      } ?>
                                <td class="Table-cell"><?= time_diff($appl['Created'], 2) ?></td>
                                <td class="Table-cell"><?= $appl['nr_notes'] ?></td>
                                <td class="Table-cell"><a href="/user.php?id=<?= $appl['last_UserID'] ?>"><?= $appl['last_Username'] ?></a></td>
                                <td class="Table-cell"><?= strlen($appl['last_Created']) ? time_diff($appl['last_Created'], 2) : '' ?></td>
                            </tr>
                        <?  } /* foreach */ ?>
                    </table>
                </div>
            <?
            } /* count($ApplicationList) > 0 */ else {
            ?>
                <div class="Box">
                    <div class="Box-body"><?= t('server.apply.the_cupboard_is_empty') ?></div>
                </div>
        <?
            } /* no applications */
        } /* show list of applicant entries */
        ?>
</div>
<?
View::show_footer();
