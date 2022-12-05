<?
define('EMAILS_PER_PAGE', 25);
if (!check_perms('users_view_email')) {
    error(403);
}
list($Page, $Limit) = Format::page_limit(EMAILS_PER_PAGE);

View::show_header(t('server.tools.manage_email_blacklist'));
$Where = "";
if (!empty($_POST['email'])) {
    $Email = db_string($_POST['email']);
    $Where .= " WHERE Email LIKE '%$Email%'";
}
if (!empty($_POST['comment'])) {
    $Comment = db_string($_POST['comment']);
    if (!empty($Where)) {
        $Where .= " AND";
    } else {
        $Where .= " WHERE";
    }
    $Where .= " Comment LIKE '%$Comment%'";
}
$DB->query("
	SELECT
		SQL_CALC_FOUND_ROWS
		ID,
		UserID,
		Time,
		Email,
		Comment
	FROM email_blacklist
	$Where
	ORDER BY Time DESC
	LIMIT $Limit");
$Results = $DB->to_array(false, MYSQLI_ASSOC, false);
$DB->query('SELECT FOUND_ROWS()');
list($NumResults) = $DB->next_record();

$Pages = Format::get_pages($Page, $NumResults, CONFIG['TOPICS_PER_PAGE'], 9);
?>
<div class="LayoutBody">
    <div class="BodyHeader">
        <h2 class="BodyHeader-nav"><?= t('server.tools.email_blacklist') ?></h2>
    </div>
    <form class="add_form" name="email_blacklist" action="tools.php" method="post">
        <input type="hidden" name="action" value="email_blacklist_alter" />
        <input type="hidden" name="auth" value="<?= $LoggedUser['AuthKey'] ?>" />
        <div class="Form-rowList" variant="header">
            <div class="Form-rowHeader">
                <?= t('server.tools.new_email_blacklist') ?>
            </div>
            <div class="Form-row">
                <div class="Form-label">
                    <?= t('server.tools.email_address') ?>
                </div>
                <div class="Form-inputs">
                    <input class="Input" type="text" name="email" size="30" />
                </div>
            </div>
            <div class="Form-row">
                <div class="Form-label">
                    <?= t('server.tools.email_blacklist_comment') ?>
                </div>
                <div class="Form-inputs">
                    <input class="Input" type="text" name="comment" size="60" /></td>
                </div>
            </div>
            <div class="Form-row">
                <button class="Button" type="submit" value="Create"><?= t('server.common.new') ?></button>
            </div>
        </div>
    </form>
    <form class="Form Box SearchPage" action="tools.php" method="post">
        <input type="hidden" name="action" value="email_blacklist" />
        <div class="SearchPageBody">
            <div class="Form-rowList">
                <div class="Form-row">
                    <div class="Form-label">
                        <?= t('server.tools.email') ?>
                    </div>
                    <div class="Form-inputs">
                        <input class="Input" type="email" name="email" size="30" />
                    </div>
                </div>
                <div class="Form-row">
                    <div class="Form-label">
                        <?= t('server.tools.email_blacklist_comment') ?>
                    </div>
                    <div class="Form-inputs">
                        <input class="Input" type="text" name="comment" size="60" />
                    </div>
                </div>
            </div>
        </div>
        <div class="SearchPageFooter">
            <div class="SearchPageFooter-actions">
                <input class="Button" type="submit" value="<?= t('server.common.search') ?>" />
            </div>
        </div>

    </form>
    <? if (count($Results) > 0) { ?>

        <? View::pages($Pages) ?>
        <table class="Table">
            <tr class="Table-rowHeader">
                <td class="Table-cell"><?= t('server.tools.email_address') ?></td>
                <td class="Table-cell"><?= t('server.tools.email_blacklist_comment') ?></td>
                <td class="Table-cell"><?= t('server.tools.date_added') ?></td>
                <td class="Table-cell"><?= t('server.tools.operations') ?></td>
            </tr>
            <?
            foreach ($Results as $Result) {
            ?>
                <tr class="Table-row">
                    <form class="manage_form" name="email_blacklist" action="tools.php" method="post">
                        <td class="Table-cell">
                            <input type="hidden" name="action" value="email_blacklist_alter" />
                            <input type="hidden" name="auth" value="<?= $LoggedUser['AuthKey'] ?>" />
                            <input type="hidden" name="id" value="<?= $Result['ID'] ?>" />
                            <input class="Input" type="email" name="email" value="<?= display_str($Result['Email']) ?>" size="30" />
                        </td>
                        <td class="Table-cell"><input class="Input" type="text" name="comment" value="<?= display_str($Result['Comment']) ?>" size="60" /></td>
                        <td class="Table-cell"><?= Users::format_username($Result['UserID'], false, false, false) ?><br /><?= time_diff($Result['Time'], 1) ?></td>
                        <td class="Table-cell">
                            <input class="Button" type="submit" name="submit" value="Edit" />
                            <input class="Button" type="submit" name="submit" value="Delete" />
                        </td>
                    </form>
                </tr>
            <?  } ?>
        </table>
        <? View::pages($Pages) ?>
    <? } ?>
</div>
<? View::show_footer(); ?>