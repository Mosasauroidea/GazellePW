<?
if (!check_perms('site_collages_recover')) {
    error(403);
}

if ($_POST['collage_id'] && is_number($_POST['collage_id'])) {
    authorize();
    $CollageID = $_POST['collage_id'];

    $DB->query("
		SELECT Name
		FROM collages
		WHERE ID = $CollageID");
    if (!$DB->has_results()) {
        error(Lang::get('collages.collage_is_completely_deleted'));
    } else {
        $DB->query("
			UPDATE collages
			SET Deleted = '0'
			WHERE ID = $CollageID");
        $Cache->delete_value("collage_$CollageID");
        Misc::write_log("Collage $CollageID was recovered by " . $LoggedUser['Username']);
        header("Location: collages.php?id=$CollageID");
    }
}
View::show_header(Lang::get('collages.collage_recovery'), '', 'PageCollageRecovery');
?>
<div class="thin center">
    <div class="box" id="collage_recover_box">
        <div class="head colhead"><?= Lang::get('collages.recover_deleted_collage') ?></div>
        <div class="pad">
            <form class="undelete_form" name="collage" action="collages.php" method="post">
                <input type="hidden" name="action" value="recover" />
                <input type="hidden" name="auth" value="<?= $LoggedUser['AuthKey'] ?>" />
                <div class="field_div">
                    <strong><?= Lang::get('collages.collage_id') ?>: </strong>
                    <input class="Input" type="text" name="collage_id" size="8" />
                </div>
                <div class="submit_div">
                    <input value="Recover!" type="submit" />
                </div>
            </form>
        </div>
    </div>
</div>
<?
View::show_footer();
