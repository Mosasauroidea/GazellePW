<?

if (!check_perms("users_mod")) {
    error(404);
}

View::show_header(t('server.tools.global_notification'));

$GlobalNotification = NotificationsManager::get_global_notification();

$Expiration = $GlobalNotification['Expiration'] ? $GlobalNotification['Expiration'] / 60 : "";
?>

<div class="LayoutBody">
    <div class="BodyHeader">
        <div class="BodyHeader-nav">
            <?= t('server.tools.global_notification') ?>
        </div>
    </div>
    <form action="tools.php" method="post">
        <input type="hidden" name="action" value="take_global_notification" />
        <input type="hidden" name="type" value="set" />
        <table class="Form-rowList" variant="header">
            <tr class="Form-rowHeader">
                <td><?= t('server.tools.set_global_notification') ?></td>
            </tr>
            <tr class="Form-row">
                <td class="Form-label"><?= t('server.tools.message') ?>:</td>
                <td class="Form-inputs">
                    <input class="Input" type="text" name="message" id="message" size="50" value="<?= $GlobalNotification['Message'] ?>" />
                </td>
            </tr>
            <tr class="Form-row">
                <td class="Form-label"><?= t('server.tools.url') ?>:</td>
                <td class="Form-inputs">
                    <input class="Input" type="text" name="url" id="url" size="50" value="<?= $GlobalNotification['URL'] ?>" />
                </td>
            </tr>
            <tr class="Form-row">
                <td class="Form-label"><?= t('server.tools.importance') ?>:</td>
                <td class="Form-inputs">
                    <select class="Input" name="importance" id="importance">
                        <? foreach (NotificationsManager::$Importances as $Key => $Value) { ?>
                            <option class="Select-option" value="<?= $Value ?>" <?= $Value == $GlobalNotification['Importance'] ? ' selected="selected"' : '' ?>><?= ucfirst($Key) ?></option>
                        <?      } ?>
                    </select>
                </td>
            </tr>
            <tr class="Form-row">
                <td class="Form-label"><?= t('server.tools.length_in_min') ?>:</td>
                <td class="Form-inputs">
                    <input class="Input is-small" type="number" name="length" id="length" size="20" value="<?= $Expiration ?>" />
                </td>
            </tr>
            <tr class="Form-row FormOneLine">
                <td>
                    <input class="Button" type="submit" name="set" value="<?= t('client.common.save') ?>" />
                    <? if ($GlobalNotification) { ?>
                        <input class="Button" type="submit" name="delete" value="<?= t('server.common.delete') ?>" />
                    <?      } ?>
                </td>
            </tr>
        </table>
    </form>
</div>

<?
View::show_footer();
