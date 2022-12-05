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

View::show_header(t('server.collages.collage'), '', 'PageCollageDelete');
?>
<div class="LayoutBody">
    <div class="BodyHeader">
        <h2 class="BodyHeader-nav"><?= t('server.collages.collage') ?></h2>
    </div>
    <form class="Form delete_form" name="collage" action="collages.php" method="post">
        <input type="hidden" name="action" value="take_delete" />
        <input type="hidden" name="auth" value="<?= $LoggedUser['AuthKey'] ?>" />
        <input type="hidden" name="collageid" value="<?= $CollageID ?>" />
        <div class="Form-rowList" variant="header">
            <div class="Form-rowHeader">
                <?= t('server.collages.delete_collage') ?>
            </div>
            <?
            if ($CategoryID == $PersonalCollageCategoryCat) {
            ?>
                <div class="Form-row alertbar">
                    <strong><?= t('server.collages.delete_warning') ?></strong>
                </div>
            <?
            }
            ?>
            <div class="Form-row">
                <div class="Form-label"><?= t('server.collages.reason') ?>: </div>
                <div class="Form-inputs">
                    <input class="Input" type="text" name="reason" size="40" />
                    </dvi>
                </div>
            </div>
            <div class="Form-row">
                <button class="Button" value="Delete" type="submit"><?= t('server.common.delete') ?></button>
            </div>
        </div>
    </form>
</div>
<?
View::show_footer();
?>