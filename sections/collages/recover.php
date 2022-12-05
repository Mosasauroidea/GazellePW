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
        error(t('server.collages.collage_is_completely_deleted'));
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
View::show_header(t('server.collages.collage'), '', 'PageCollageRecovery');
?>
<div class="LayoutBody">
    <div class="BodyHeader">
        <h2 class="BodyHeader-nav"><?= t('server.collages.collage') ?></h2>
    </div>
    <form class="Form undelete_form" name="collage" action="collages.php" method="post">
        <input type="hidden" name="action" value="recover" />
        <input type="hidden" name="auth" value="<?= $LoggedUser['AuthKey'] ?>" />
        <div class="Form-rowList" variant="header">
            <div class="Form-rowHeader">
                <?= t('server.collages.recover_deleted_collage') ?>
            </div>
            <div class="Form-row">
                <div class="Form-label"><?= t('server.collages.collage_id') ?>: </div>
                <div class="Form-inputs">
                    <input class="Input" type="text" name="collage_id" size="8" />
                </div>
            </div>
            <div class="Form-row">
                <button class="Button" value="Recover!" type="submit"><?= t('server.collages.recover_collages') ?></button>
            </div>
        </div>
    </form>
</div>
<?
View::show_footer();
