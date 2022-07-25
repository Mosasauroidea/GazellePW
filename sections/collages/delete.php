<?

$CollageID = $_GET['collageid'];
if (!is_number($CollageID) || !$CollageID) {
    error(404);
}

$DB->query("
	SELECT Name, CategoryID, UserID
	FROM collages
	WHERE ID = '$CollageID'");
list($Name, $CategoryID, $UserID) = $DB->next_record();

if (!check_perms('site_collages_delete') && $UserID != $LoggedUser['ID']) {
    error(403);
}

View::show_header('Delete collage', '', 'PageCollageDelete');
?>
<div class="thin center">
    <div class="box" id="collage_delete_box">
        <div class="head colhead">
            <?= t('server.collages.delete_collage') ?>
        </div>
        <div class="pad">
            <form class="delete_form" name="collage" action="collages.php" method="post">
                <input type="hidden" name="action" value="take_delete" />
                <input type="hidden" name="auth" value="<?= $LoggedUser['AuthKey'] ?>" />
                <input type="hidden" name="collageid" value="<?= $CollageID ?>" />
                <?
                if ($CategoryID == $PersonalCollageCategoryCat) {
                ?>
                    <div class="alertbar" style="margin-bottom: 1em;">
                        <strong><?= t('server.collages.delete_warning') ?></strong>
                    </div>
                <?
                }
                ?>
                <div class="field_div">
                    <strong><?= t('server.collages.reason') ?>: </strong>
                    <input class="Input" type="text" name="reason" size="40" />
                </div>
                <div class="submit_div">
                    <input value="Delete" type="submit" />
                </div>
            </form>
        </div>
    </div>
</div>
<?
View::show_footer();
?>