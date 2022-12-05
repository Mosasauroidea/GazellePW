<?
if (!check_perms('admin_dnu')) {
    error(403);
}
$Title = t('server.tools.manage_the_dnu_list');

View::show_header($Title, 'jquery-ui,dnu_list');
$DB->query("
	SELECT
		d.ID,
		d.Name,
		d.Comment,
		d.UserID,
		d.Time
	FROM do_not_upload AS d
		LEFT JOIN users_main AS um ON um.ID = d.UserID
	ORDER BY d.Sequence");
?>
<div class="LayoutBody">
    <div class="BodyHeader">
        <h2 class="BodyHeader-nav"><?= ($Title) ?></h2>
    </div>
    <form class="Form add_form" name="dnu" action="tools.php" method="post">
        <input type="hidden" name="action" value="dnu_alter" />
        <input type="hidden" name="auth" value="<?= $LoggedUser['AuthKey'] ?>" />
        <table class="Form-rowList" variant="header">
            <tr class="Form-rowHeader">
                <td>
                    <?= t('server.tools.add_an_entry_to_the_dnu_list') ?>
                </td>
            </tr>
            <tr class="Form-row">
                <td class="Form-label">
                    <?= t('server.tools.name') ?>
                </td>
                <td class="Form-inputs">
                    <input class="Input" type="text" name="name" size="40" />
                </td>
            <tr class="Form-row">
                <td class="Form-label">
                    <?= t('server.tools.dnu_comment') ?>
                </td>
                <td class="Form-inputs">
                    <input class="Input" type="text" name="comment" size="60" />
                </td>
            </tr>
            <tr class="Form-row">
                <td>
                    <button class="Button" type="submit" value="Create"><?= t('server.common.new') ?></button>
                </td>
            </tr>
        </table>
    </form>
    <? if (G::$DB->record_count() > 0) { ?>
        <div class="TableContainer">
            <table class="TableDnu Table">
                <tr class="Table-rowHeader">
                    <td class="Table-cell"><?= t('server.tools.name') ?></td>
                    <td class="Table-cell"><?= t('server.tools.dnu_comment') ?></td>
                    <td class="Table-cell"><?= t('server.tools.dnu_added') ?></td>
                    <td class="Table-cell"><?= t('server.tools.operations') ?></td>
                </tr>
                <tbody>
                    <? while (list($ID, $Name, $Comment, $UserID, $DNUTime) = $DB->next_record()) { ?>
                        <tr id="item_<?= $ID ?>">
                            <form class="manage_form dnu" action="tools.php" method="post">
                                <td>
                                    <input type="hidden" name="action" value="dnu_alter" />
                                    <input type="hidden" name="auth" value="<?= $LoggedUser['AuthKey'] ?>" />
                                    <input type="hidden" name="id" value="<?= $ID ?>" />
                                    <input class="Input" type="text" name="name" value="<?= display_str($Name) ?>" size="40" />
                                </td>
                                <td>
                                    <input class="Input" type="text" name="comment" value="<?= display_str($Comment) ?>" size="60" />
                                </td>
                                <td>
                                    <?= Users::format_username($UserID, false, false, false) ?><br />
                                    <? echo time_diff($DNUTime, 1) . "\n"; ?>
                                </td>
                                <td>
                                    <button class="Button" type="submit" name="submit" value="Edit"><?= t('server.common.edit') ?></button>
                                    <button class="Button" type="submit" name="submit" value="Delete"><?= t('server.common.delete') ?></button>
                                </td>
                            </form>
                        </tr>
                    <?  } ?>
                </tbody>
            </table>
        </div>
    <? } ?>
</div>
<? View::show_footer(); ?>