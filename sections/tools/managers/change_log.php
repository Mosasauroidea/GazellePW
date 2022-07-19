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

View::show_header('Gazelle Change Log', 'datetime_picker', 'datetime_picker');
?>
<div class="LayoutBody">
    <div class="BodyHeader">
        <h2 class="BodyHeader-nav"><?= Lang::get('tools', 'gz_change_log') ?></h2>
        <div class="BodyNavLinks">
            <?
            $Pages = Format::get_pages($Page, $NumResults, $PerPage, 11);
            echo "\t\t$Pages\n";
            ?>
        </div>
    </div>
    <? if ($CanEdit) { ?>
        <div class="box box2 edit_changelog">
            <div class="head">
                <strong><?= Lang::get('tools', 'manually_submit_a_log') ?></strong>
            </div>
            <div class="pad">
                <form method="post" action="">
                    <input type="hidden" name="perform" value="add" />
                    <input type="hidden" name="auth" value="<?= $LoggedUser['AuthKey'] ?>" />
                    <div class="field_div" id="cl_message">
                        <span class="label"><?= Lang::get('tools', 'commit_message') ?>:</span>
                        <!-- <br /> -->
                        <textarea class="Input" name="message" rows="2"></textarea>
                    </div>
                    <div class="field_div" id="cl_date">
                        <span class="label"><?= Lang::get('tools', 'date') ?>:</span>
                        <!-- <br /> -->
                        <input class="Input" type="text" name="date" />
                    </div>
                    <div class="field_div" id="cl_author">
                        <span class="label"><?= Lang::get('tools', 'author') ?>:</span>
                        <!-- <br /> -->
                        <input class="Input" type="text" name="author" value="<?= $LoggedUser['Username'] ?>" />
                    </div>
                    <div class="submit_div" id="cl_submit">
                        <input class="Button" type="submit" value="<?= Lang::get('global', 'submit') ?>" />
                    </div>
                </form>
            </div>
        </div>
    <?
    }

    foreach ($ChangeLog as $Change) {
    ?>
        <div class="box box2 change_log_entry">
            <div class="head">
                <span><?= $Change['Time2'] ?> by <?= $Change['Author'] ?></span>
                <? if ($CanEdit) { ?>
                    <span style="float: right;">
                        <form id="delete_<?= $Change['ID'] ?>" method="post" action="">
                            <input type="hidden" name="perform" value="remove" />
                            <input type="hidden" name="auth" value="<?= $LoggedUser['AuthKey'] ?>" />
                            <input type="hidden" name="change_id" value="<?= $Change['ID'] ?>" />
                        </form>
                        <a href="#" onclick="$('#delete_<?= $Change['ID'] ?>').raw().submit(); return false;" class="brackets"><?= Lang::get('global', 'delete') ?></a>
                    </span>
                <?      } ?>
            </div>
            <div class="pad">
                <?= $Change['Message'] ?>
            </div>
        </div>
    <?  } ?>
</div>
<? View::show_footer(); ?>