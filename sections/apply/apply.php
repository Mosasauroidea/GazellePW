<?
if (isset($_POST['auth'])) {
    authorize();
    $Role = array_key_exists('role', $_POST) ? trim($_POST['role']) : '';
    $Body = array_key_exists('body', $_POST) ? trim($_POST['body']) : '';

    if (strlen($Role)) {
        if (strlen($Body) > 80) {
            $Applicant = new Applicant($LoggedUser['ID'], $Role, $Body);
            header('Location: /apply.php?action=view&id=' . $Applicant->id());
            exit;
        } else {
            $Error = t('server.apply.you_need_explain_more');
        }
    } else {
        $Error = t('server.apply.you_need_choose_role');
    }
} else {
    $Role = '';
    $Body = '';
}
View::show_header(t('server.apply.apply'), 'apply,bbcode', 'PageApplyApply');
?>

<div class="LayoutBody">
    <div class="BodyHeader">
        <h2 class="BodyHeader-nav"><?= t('server.apply.apply') ?></h2>
        <? if (check_perms('admin_manage_applicants') || Applicant::user_is_applicant($LoggedUser['ID'])) { ?>
            <div class="BodyNavLinks">
                <? if (check_perms('admin_manage_applicants')) { ?>
                    <a href="/apply.php?action=view" class="brackets"><?= t('server.apply.current_applications') ?></a>
                    <a href="/apply.php?action=view&status=resolved" class="brackets"><?= t('server.apply.resolved_applications') ?></a>
                    <a href="/apply.php?action=admin" class="brackets"><?= t('server.apply.manage_roles') ?></a>
                <? }
                if (Applicant::user_is_applicant($LoggedUser['ID'])) { ?>
                    <a href="/apply.php?action=view" class="brackets"><?= t('server.apply.view_your_application') ?></a>
                <? } ?>
            </div>
        <? } ?>
    </div>
    <?
    $Roles = ApplicantRole::get_list();
    if (count($Roles)) { ?>
        <div class="Group">
            <div class="Group-header">
                <div class="Group-headerTitle">
                    <?= t('server.apply.open_roles') ?></div>
            </div>
            <div class="Group-body">
                <? foreach ($Roles as $title => $info) { ?>
                    <div class="Box">
                        <div class="Box-header">
                            <div class="head"><?= $title ?></div>
                        </div>
                        <div class="Box-body" role_container">
                            <div class="HtmlText PostArticle">
                                <?= Text::full_format($info['description']) ?>
                            </div>
                        </div>
                    </div>
                <?  } /* foreach */ ?>
            </div>
        </div>
    <? } ?>

    <? if (count($Roles) == 0) { ?>
        <div class="Box">
            <div class="Box-body">
                <p><?= t('server.apply.thanks_for_your_interest_in_helping') ?></p>
            </div>
        </div>
        <?
    } else {
        if ($Error) {
        ?>
            <div class="important"><?= $Error ?></div>
        <?
        }
        ?>
        <form class="send_form" id="applicationform" name="apply" action="/apply.php?action=save" method="post">
            <input type="hidden" name="auth" value="<?= $LoggedUser['AuthKey'] ?>" />
            <div id="quickpost">
                <? new TEXTAREA_PREVIEW('body', 'body', $Body, 95, 20, true, true, false, ['placeholder="' . t('server.apply.at_least_80_characters') . '"']) ?>
                <div class="Form-row">
                    <div><?= t('server.apply.role') ?>:
                        <select class="Input" name="role">
                            <option class="Select-option" value="">---</option>
                            <? foreach (array_keys($Roles) as $title) { ?>
                                <option class="Select-option" value="<?= $title ?>" <?= $Role == $title ? ' selected' : '' ?>><?= $title ?></option>
                            <?  } ?>
                        </select>
                        <input class="Button" type="submit" value="<?= t('server.common.submit') ?>" />
                    </div>
                </div>
            </div>
        </form>
    <? } /* else */ ?>
</div>

<? View::show_footer();
