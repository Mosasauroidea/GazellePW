<?
if (!check_perms('admin_manage_permissions') && !check_perms('users_mod')) {
    error(403);
}

if (!check_perms('admin_manage_permissions')) {
    View::show_header(t('server.tools.h2_site_options'), '', 'PageToolSiteOption');
    $DB->query("SELECT Name, Value, Comment FROM site_options");
?>
    <div class="LayoutBody">
        <div class="BodyHeader">
            <h2 class="BodyHeader-nav"><?= t('server.tools.h2_site_options') ?></h2>
        </div>
        <div class="TableContainer">
            <table class="Table">
                <tr class="Table-rowHeader">
                    <td class="Table-cell"><?= t('server.tools.name') ?></td>
                    <td class="Table-cell"><?= t('server.tools.value') ?></td>
                    <td class="Table-cell"><?= t('server.tools.comment') ?></td>
                </tr>
                <?
                $Row = 'a';
                while (list($Name, $Value, $Comment) = $DB->next_record()) {
                    $Row = $Row === 'a' ? 'b' : 'a';
                ?>
                    <tr class="Table-row">
                        <td class="Table-cell"><?= $Name ?></td>
                        <td class="Table-cell"><?= $Value ?></td>
                        <td class="Table-cell"><?= $Comment ?></td>
                    </tr>
                <?
                }
                ?>
            </table>
        </div>
    <?
    View::show_footer();
    die();
}

if (isset($_POST['submit'])) {
    authorize();

    if ($_POST['submit'] == 'Delete') {
        $Name = db_string($_POST['name']);
        $DB->query("DELETE FROM site_options WHERE Name = '" . $Name . "'");
        $Cache->delete_value('site_option_' . $Name);
    } else {
        $Val->SetFields('name', '1', 'regex', 'The name must be separated by underscores. No spaces are allowed.', array('regex' => '/^[a-z][_a-z0-9]{0,63}$/i'));
        $Val->SetFields('value', '1', 'string', 'You must specify a value for the option.');
        $Val->SetFields('comment', '1', 'string', 'You must specify a comment for the option.');

        $Error = $Val->ValidateForm($_POST);
        if ($Error) {
            error($Error);
        }

        $Name = db_string($_POST['name']);
        $Value = db_string($_POST['value']);
        $Comment = db_string($_POST['comment']);

        if ($_POST['submit'] == 'Edit') {
            $DB->query("SELECT Name FROM site_options WHERE ID = '" . db_string($_POST['id']) . "'");
            list($OldName) = $DB->next_record();
            $DB->query("
                UPDATE site_options
                SET
                    Name = '$Name',
                    Value = '$Value',
                    Comment = '$Comment'
                WHERE ID = '" . db_string($_POST['id']) . "'
            ");
            $Cache->delete_value('site_option_' . $OldName);
        } else {
            $DB->query("
                INSERT INTO site_options (Name, Value, Comment)
                VALUES ('$Name', '$Value', '$Comment')
            ");
        }

        $Cache->delete_value('site_option_' . $Name);
    }
}

$DB->query("
    SELECT
        ID,
        Name,
        Value,
        Comment
    FROM site_options
    ORDER BY LOWER(Name) DESC
");

View::show_header(t('server.tools.h2_site_options'), '', 'PageToolSiteOption');
    ?>
    <div class="LayoutPage">
        <div class="BodyHeader">
            <h2 class="BodyHeader-nav"><?= t('server.tools.h2_site_options') ?></h2>
        </div>
        <form class="create_form" name="site_option" action="" method="post">
            <input type="hidden" name="action" value="site_options" />
            <input type="hidden" name="auth" value="<?= $LoggedUser['AuthKey'] ?>" />
            <table class="Form-rowList" variant="header">
                <tr class="Form-row">
                    <td class="Form-label">
                        <span data-tooltip="<?= t('server.tools.words_must_be_separated_by_underscores') ?>"><?= t('server.tools.name') ?></span>
                    </td>
                    <td class="Form-inputs">
                        <input class="Input" type="text" size="40" name="name" />
                    </td>
                </tr>
                <tr class="Form-row">
                    <td class="Form-label">
                        <?= t('server.tools.value') ?>
                    </td>
                    <td class="Form-inputs">
                        <input class="Input" type="text" size="20" name="value" />
                    </td>
                </tr>
                <tr class="Form-row">
                    <td class="Form-label">
                        <?= t('server.tools.comment') ?>
                    </td>
                    <td class="Form-inputs">
                        <input class="Input" type="text" size="75" name="comment" />
                    </td>
                </tr>
                <tr class="Form-row">
                    <td>
                        <button class="Button" type="submit" name="submit" value="Create"><?= t('server.common.new') ?></button>
                    </td>
                </tr>
            </table>
        </form>
        <div class="TableContainer">
            <table class="Table">
                <tr class="Table-rowHeader">
                    <td class="Table-cell">
                        <span data-tooltip="<?= t('server.tools.words_must_be_separated_by_underscores') ?>"><?= t('server.tools.name') ?></span>
                    </td>
                    <td class="Table-cell"><?= t('server.tools.value') ?></td>
                    <td class="Table-cell"><?= t('server.tools.comment') ?></td>
                    <td class="Table-cell"><?= t('server.tools.submit') ?></td>
                </tr>
                <?
                while (list($ID, $Name, $Value, $Comment) = $DB->next_record()) {
                ?>
                    <tr class="Table-row">
                        <form class="manage_form" name="site_option" action="" method="post">
                            <input type="hidden" name="id" value="<?= $ID ?>" />
                            <input type="hidden" name="action" value="site_options" />
                            <input type="hidden" name="auth" value="<?= $LoggedUser['AuthKey'] ?>" />
                            <td class="Table-cell">
                                <input class="Input" type="text" size="40" name="name" value="<?= $Name ?>" />
                            </td>
                            <td class="Table-cell">
                                <input class="Input" type="text" size="20" name="value" value="<?= $Value ?>" />
                            </td>
                            <td class="Table-cell">
                                <input class="Input" type="text" size="75" name="comment" value="<?= $Comment ?>" />
                            </td>
                            <td class="Table-cell">
                                <button class="Button" type="submit" name="submit" value="Edit"><?= t('server.common.edit') ?></button>
                                <button class="Button" type="submit" name="submit" value="Delete"><?= t('server.common.delete') ?></button>
                            </td>
                        </form>
                    </tr>
                <?
                }
                ?>
            </table>
        </div>
    </div>
    <? View::show_footer(); ?>