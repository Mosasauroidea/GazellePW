<?php
if (!isset($_GET['id']) || !is_number($_GET['id'])) {
    error(404);
}

$Action = $_GET['action'];
if ($Action !== 'unfill' && $Action !== 'delete') {
    error(404);
}

$DB->query("
	SELECT UserID, FillerID
	FROM requests
	WHERE ID = " . $_GET['id']);
list($RequestorID, $FillerID) = $DB->next_record();

if ($Action === 'unfill') {
    if ($LoggedUser['ID'] !== $RequestorID && $LoggedUser['ID'] !== $FillerID && !check_perms('site_moderate_requests')) {
        error(403);
    }
} elseif ($Action === 'delete') {
    if ($LoggedUser['ID'] !== $RequestorID && !check_perms('site_moderate_requests')) {
        error(403);
    }
}

View::show_header(ucwords($Action) . ' Request', '', 'PageRequestInterim');
?>

<div class="LayoutBody">
    <div class="BodyHeader">
        <h2 class="BodyHeader-nav">
            <?= ucwords($Action) ?> Request
        </h2>
        <form class="u-vstack <?= (($Action === 'delete') ? 'delete_form' : 'edit_form') ?>" name="request" action="requests.php" method="post">
            <input type="hidden" name="action" value="take<?= $Action ?>" />
            <input type="hidden" name="auth" value="<?= $LoggedUser['AuthKey'] ?>" />
            <input type="hidden" name="id" value="<?= $_GET['id'] ?>" />
            <? if ($Action === 'delete') { ?>
                <div class="u-colorWarning"><?= Lang::get('requests', 'delete_request_warning') ?></div>
            <?  } ?>
            <strong><?= Lang::get('requests', 'reason') ?>:</strong>
            <input class="Input" type="text" name="reason" size="30" />
            <div>
                <input class="Button" type="submit" value="<?= ucwords($Action) ?>" />
            </div>
        </form>
    </div>
</div>
<?
View::show_footer();
?>