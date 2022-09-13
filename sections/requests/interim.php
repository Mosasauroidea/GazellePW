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

$Title = '';
if ($Action === 'unfill') {
    if ($LoggedUser['ID'] !== $RequestorID && $LoggedUser['ID'] !== $FillerID && !check_perms('site_moderate_requests')) {
        error(403);
    }
    $Title = t('server.requests.unfill_request');
    $ButtonTItle = t('server.requests.unfill_request');
} elseif ($Action === 'delete') {
    if ($LoggedUser['ID'] !== $RequestorID && !check_perms('site_moderate_requests')) {
        error(403);
    }
    $Title = t('server.requests.delete_request');
    $ButtonTItle = t('server.common.delete');
}

$Request = Requests::get_request($_GET['id']);
$Name = Torrents::group_name($Request);

View::show_header($Title, '', 'PageRequestInterim');
?>

<div class="LayoutBody">
    <div class="BodyHeader">
        <div class="BodyHeader-nav">
            <?= t('server.requests.requests') ?>
        </div>
        <div class="BodyHeader-subNav">
            <?= $Name ?>
        </div>
        <form class="u-vstack <?= (($Action === 'delete') ? 'delete_form' : 'edit_form') ?>" name="request" action="requests.php" method="post">
            <input type="hidden" name="action" value="take<?= $Action ?>" />
            <input type="hidden" name="auth" value="<?= $LoggedUser['AuthKey'] ?>" />
            <input type="hidden" name="id" value="<?= $_GET['id'] ?>" />
            <div class="Form-rowList" variant="header">
                <div class="Form-rowHeader">
                    <div class="Form-title">
                        <?= $Title ?>
                    </div>
                </div>
                <? if ($Action === 'delete') { ?>
                    <div class="Form-row">
                        <div class=" u-colorWarning"><?= t('server.requests.delete_request_warning') ?></div>
                    </div>
                <?  } ?>
                <div class="Form-row">
                    <div class="Form-label"><?= t('server.requests.reason') ?>:</div>
                    <div class="Form-inputs">
                        <input class="Input" type="text" name="reason" size="30" />
                    </div>
                </div>
                <div class="Form-row">
                    <button class="Button" type="submit" value="<?= ucwords($Action) ?>"><?= $ButtonTItle ?></button>
                </div>
            </div>
        </form>
    </div>
</div>
<?
View::show_footer();
?>