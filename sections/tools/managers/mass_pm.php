<?
if (!check_perms("users_mod")) {
    error(403);
}

$Classes = Users::get_classes()[0];
// If your user base is large, sending a PM to the lower classes will take a long time
// add the class ID into this array to skip it when presenting the list of classes


View::show_header(t('server.tools.compose_mass_pm'), 'inbox,bbcode,jquery.validate,form_validate');
?>
<div class="LayoutBody">
    <div class="BodyHeader">
        <h2 class="BodyHeader-nav"><?= t('server.tools.send_a_mass_pm') ?></h2>
    </div>
    <form class="send_form" name="message" action="tools.php" method="post" id="messageform">
        <div class="BoxBody">
            <input type="hidden" name="action" value="take_mass_pm" />
            <input type="hidden" name="auth" value="<?= G::$LoggedUser['AuthKey'] ?>" />
            <div id="quickpost">
                <h3><?= t('server.tools.mass_pm_class') ?></h3>
                <select class="Input" id="class_id" name="class_id">
                    <option class="Select-option">---</option>
                    <option class="Select-option" value="99"><?= t('server.tools.all_users') ?></option>
                    <option class="Select-option" value="100"><?= t('server.tools.staff') ?></option>
                    <? foreach ($Classes as $Class) {
                        if (!in_array($Class['ID'])) { ?>
                            <option class="Select-option" value="<?= $Class['ID'] ?>"><?= $Class['Name'] ?></option>
                    <?                      }
                    } ?>

                </select>
                <h3><?= t('server.tools.subject') ?></h3>
                <input class="Input required" type="text" name="subject" size="95" /><br />
                <h3><?= t('server.tools.body') ?></h3>
                <textarea class="Input" id="body" class="required" name="body" cols="95" rows="10" onkeyup="resize('body')"></textarea>
            </div>
            <input type="checkbox" name="from_system" id="from_system" checked="checked" /><label for="from_system"><?= t('server.tools.send_as_system') ?></label>
            <div id="preview" class="hidden"></div>
            <div id="buttons" class="center">
                <input class="Button" type="button" value="Preview" onclick="Quick_Preview();" />
                <input class="Button" type="submit" value="<?= t('server.inbox.send_message') ?>" />
            </div>
        </div>
    </form>
</div>
<?
View::show_footer();
?>