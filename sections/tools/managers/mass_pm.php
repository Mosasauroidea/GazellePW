<?
if (!check_perms("users_mod")) {
    error(403);
}

$Classes = Users::get_classes()[0];
// If your user base is large, sending a PM to the lower classes will take a long time
// add the class ID into this array to skip it when presenting the list of classes


View::show_header(t('server.tools.send_a_mass_pm'), 'inbox,bbcode,jquery.validate,form_validate');
?>
<div class="LayoutBody">
    <div class="BodyHeader">
        <h2 class="BodyHeader-nav"><?= t('server.tools.send_a_mass_pm') ?></h2>
    </div>
    <form class="send_form" name="message" action="tools.php" method="post" id="messageform">
        <input type="hidden" name="action" value="take_mass_pm" />
        <input type="hidden" name="auth" value="<?= G::$LoggedUser['AuthKey'] ?>" />
        <div class="Form-rowList" variant="header">
            <div class="Form-rowHeader">
                <?= t('server.tools.compose_mass_pm') ?>
            </div>
            <div class="Form-row">
                <div class="Form-label">
                    <?= t('server.tools.mass_pm_class') ?>
                </div>
                <div class="Form-inputs">
                    <select class="Input" id="class_id" name="class_id">
                        <option class="Select-option">---</option>
                        <option class="Select-option" value="99"><?= t('server.tools.all_users') ?></option>
                        <option class="Select-option" value="100"><?= t('server.tools.staff') ?></option>
                        <? foreach ($Classes as $Class) {
                        ?>
                            <option class="Select-option" value="<?= $Class['ID'] ?>"><?= $Class['Name'] ?></option>
                        <?
                        } ?>

                    </select>
                </div>
            </div>
            <div class="Form-row">
                <div class="Form-label">
                    <?= t('server.tools.subject') ?>
                </div>
                <div class="Form-inputs">
                    <input class="Input required" type="text" name="subject" size="95" />
                </div>
            </div>
            <div class="Form-row">
                <div class="Form-label">
                    <?= t('server.tools.body') ?>
                </div>
                <div class="Form-items">
                    <? new TEXTAREA_PREVIEW('body', 'body'); ?>
                </div>
            </div>
            <div class="Form-row">
                <div class="Form-label">
                </div>
                <div class="Form-inputs">
                    <input type="checkbox" name="from_system" id="from_system" checked="checked" /><label for="from_system"><?= t('server.tools.send_as_system') ?></label>
                </div>
            </div>
            <div class="Form-row">
                <input class="Button" type="submit" value="<?= t('server.inbox.send_message') ?>" />
            </div>
    </form>
</div>
<?
View::show_footer();
?>