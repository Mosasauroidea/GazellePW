<?

if (!isset($_GET['id']) || !is_number($_GET['id'])) {
    error(404);
}

$Action = $_GET['action'];
if ($Action !== 'delete') {
    error(404);
}

if ($LoggedUser['ID'] != $UserID && !check_perms('torrents_delete')) {
    error(403);
}
$DB->query('SELECT name FROM subtitles WHERE id=' . $_GET['id']);
list($Name) = $DB->next_record(MYSQLI_NUM, false);
View::show_header(Lang::get('subtitles', 'delete_subtitle'), '', 'PageSubtitleDelete');

?>
<form class="<?= (($Action === 'delete') ? 'delete_form' : 'edit_form') ?>" name="request" action="subtitles.php" method="post">
    <div class="Form-rowList" variant="header">
        <div class="Form-rowHeader">
            <?= Lang::get('subtitles', 'delete_subtitle') ?> > <?= $Name ?>
        </div>
        <div class="Form-row">
            <div class="Form-inputs">
                <input type="hidden" name="action" value="take<?= $Action ?>" />
                <input type="hidden" name="auth" value="<?= $LoggedUser['AuthKey'] ?>" />
                <input type="hidden" name="id" value="<?= $_GET['id'] ?>" />
                <strong><?= Lang::get('subtitles', 'reason') ?>:</strong>
                <input class="Input" type="text" name="reason" size="30" />
                <input class="Button" value="<?= Lang::get('global', 'delete') ?>" type="submit" />
            </div>
        </div>
    </div>
</form>
<?
View::show_footer();
?>