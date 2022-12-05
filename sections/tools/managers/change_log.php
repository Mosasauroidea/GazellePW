<?
$PerPage = CONFIG['POSTS_PER_PAGE'];
list($Page, $Limit) = Format::page_limit($PerPage);

$CanEdit = check_perms('users_mod');

if ($CanEdit && isset($_POST['perform'])) {
    authorize();
    if ($_POST['perform'] === 'add' && !empty($_POST['message'])) {
        $Message = db_string($_POST['message']);
        $Author = db_string($_POST['author']);
        $Date = db_string($_POST['date']);
        if (!is_valid_date($Date)) {
            $Date = sqltime();
        }
        $DB->query("
			INSERT INTO changelog (Message, Author, Time)
			VALUES ('$Message', '$Author', '$Date')");
        $ID = $DB->inserted_id();
        //  SiteHistory::add_event(sqltime(), "Change log $ID", "tools.php?action=change_log", 1, 3, "", $Message, $LoggedUser['ID']);

    }
    if ($_POST['perform'] === 'remove' && !empty($_POST['change_id'])) {
        $ID = (int)$_POST['change_id'];
        $DB->query("
			DELETE FROM changelog
			WHERE ID = '$ID'");
    }
    header('Location:tools.php?action=change_log');
}

$DB->query("
	SELECT
		SQL_CALC_FOUND_ROWS
		ID,
		Message,
		Author,
		Date(Time) as Time2
	FROM changelog
	ORDER BY Time DESC
	LIMIT $Limit");
$ChangeLog = $DB->to_array();
$DB->query('SELECT FOUND_ROWS()');
list($NumResults) = $DB->next_record();

View::show_header(t('server.tools.change_log'), 'datetime_picker');
?>
<div class="LayoutBody">
    <div class="BodyHeader">
        <h2 class="BodyHeader-nav"><?= t('server.tools.change_log') ?></h2>

    </div>
    <? if ($CanEdit) { ?>
        <form method="post" action="">
            <input type="hidden" name="perform" value="add" />
            <input type="hidden" name="auth" value="<?= $LoggedUser['AuthKey'] ?>" />
            <div class="box box2 edit_changelog Form-rowList" variant="header">
                <div class="Form-rowHeader">
                    <strong><?= t('server.tools.manually_submit_a_log') ?></strong>
                </div>
                <div class="Form-row" id="cl_message">
                    <div class="Form-label"><?= t('server.tools.commit_message') ?>:</div>
                    <div class="Form-items">
                        <? new TEXTAREA_PREVIEW('message', '', '', 60, 8); ?>
                    </div>
                </div>
                <div class="Form-row" id="cl_date">
                    <div class="Form-label"><?= t('server.tools.date') ?>:</div>
                    <div class="Form-inputs"><input class="Input" type="date" name="date" /></div>
                </div>
                <div class="Form-row" id="cl_author">
                    <span class="Form-label"><?= t('server.tools.author') ?>:</span>
                    <div class="Form-inputs"><input class="Input" type="text" name="author" value="<?= $LoggedUser['Username'] ?>" /></div>
                </div>
                <div class="Form-row" id="cl_submit">
                    <input class="Button" type="submit" value="<?= t('server.common.submit') ?>" />
                </div>
            </div>
        </form>
    <?
    }
    ?>
    <div class="BodyNavLinks">
        <?
        $Pages = Format::get_pages($Page, $NumResults, $PerPage, 11);
        echo "\t\t$Pages\n";
        ?>
    </div>
    <div class="BoxList">
        <?

        foreach ($ChangeLog as $Change) {
        ?>
            <div class="Box">
                <div class="Box-header">
                    <div class="Box-headerTitle">
                        <?= $Change['Time2'] ?> - <?= $Change['Author'] ?>
                    </div>
                    <? if ($CanEdit) { ?>
                        <div class="Box-headerActions">
                            <form id="delete_<?= $Change['ID'] ?>" method="post" action="">
                                <input type="hidden" name="perform" value="remove" />
                                <input type="hidden" name="auth" value="<?= $LoggedUser['AuthKey'] ?>" />
                                <input type="hidden" name="change_id" value="<?= $Change['ID'] ?>" />
                            </form>
                            <a href="#" onclick="$('#delete_<?= $Change['ID'] ?>').raw().submit(); return false;" class="brackets"><?= t('server.common.delete') ?></a>
                        </div>
                    <?      } ?>
                </div>
                <div class="Box-body">
                    <?= $Change['Message'] ?>
                </div>
            </div>
        <?  } ?>
    </div>
    <div class="BodyNavLinks">
        <?
        $Pages = Format::get_pages($Page, $NumResults, $PerPage, 11);
        echo "\t\t$Pages\n";
        ?>
    </div>
</div>
<? View::show_footer(); ?>