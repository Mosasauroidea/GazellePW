<?

if (!isset($_GET['id']) || !is_number($_GET['id'])) {
    error(404);
}

$Action = $_GET['action'];
if ($Action !== 'delete') {
    error(404);
}


$DB->query('SELECT name, uploader FROM subtitles WHERE id=' . $_GET['id']);
list($Name, $Uploader) = $DB->next_record(MYSQLI_NUM, false);

if ($LoggedUser['ID'] != $Uploader && !check_perms('users_mod')) {
    error(403);
}
View::show_header(t('server.subtitles.delete_subtitle'), '', 'PageSubtitleDelete');

?>
<form class="<?= (($Action === 'delete') ? 'delete_form' : 'edit_form') ?>" name="request" action="subtitles.php" method="post">
    <div class="Form-rowList" variant="header">
        <div class="Form-rowHeader">
            <?= t('server.subtitles.delete_subtitle') ?> > <?= $Name ?>
        </div>
        <div class="Form-row">
            <div class="Form-inputs">
                <input type="hidden" name="action" value="take<?= $Action ?>" />
                <input type="hidden" name="auth" value="<?= $LoggedUser['AuthKey'] ?>" />
                <input type="hidden" name="id" value="<?= $_GET['id'] ?>" />
                <strong><?= t('server.subtitles.reason') ?>:</strong>
                <input class="Input" type="text" name="reason" size="30" />
                <input class="Button" value="<?= t('server.common.delete') ?>" type="submit" />
            </div>
        </div>
    </div>
</form>
<?
View::show_footer();
?>