<?

if (!check_perms("users_mod")) {
    error(404);
}

View::show_header(Lang::get('tools.global_notification'));

$GlobalNotification = NotificationsManager::get_global_notification();

$Expiration = $GlobalNotification['Expiration'] ? $GlobalNotification['Expiration'] / 60 : "";
?>

<h2><?= Lang::get('tools.set_global_notification') ?></h2>

<div class="thin BoxBody">
    <form action="tools.php" method="post">
        <input type="hidden" name="action" value="take_global_notification" />
        <input type="hidden" name="type" value="set" />
        <table align="center">
            <tr>
                <td class="label"><?= Lang::get('tools.message') ?>:</td>
                <td>
                    <input class="Input" type="text" name="message" id="message" size="50" value="<?= $GlobalNotification['Message'] ?>" />
                </td>
            </tr>
            <tr>
                <td class="label"><?= Lang::get('tools.url') ?>:</td>
                <td>
                    <input class="Input" type="text" name="url" id="url" size="50" value="<?= $GlobalNotification['URL'] ?>" />
                </td>
            </tr>
            <tr>
                <td class="label"><?= Lang::get('tools.importance') ?>:</td>
                <td>
                    <select class="Input" name="importance" id="importance">
                        <? foreach (NotificationsManager::$Importances as $Key => $Value) { ?>
                            <option class="Select-option" value="<?= $Value ?>" <?= $Value == $GlobalNotification['Importance'] ? ' selected="selected"' : '' ?>><?= ucfirst($Key) ?></option>
                        <?      } ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td class="label"><?= Lang::get('tools.length_in_min') ?>:</td>
                <td>
                    <input class="Input" type="text" name="length" id="length" size="20" value="<?= $Expiration ?>" />
                </td>
            </tr>
            <tr>
                <td>
                    <input class="Button" type="submit" name="set" value="Create Notification" />
                </td>
                <? if ($GlobalNotification) { ?>
                    <td>
                        <input class="Button" type="submit" name="delete" value="Delete Notification" />
                    </td>
                <?      } ?>
            </tr>
        </table>
    </form>
</div>

<?
View::show_footer();
