<?php
if (!check_perms('admin_manage_ipbans')) {
    error(403);
}

if (isset($_POST['submit'])) {
    authorize();

    $IP = db_string($_POST['start']);
    if ($_POST['submit'] == 'Delete') { //Delete
        if (!is_number($_POST['id']) || $_POST['id'] == '') {
            error(0);
        }
        $DB->query('DELETE FROM ip_bans WHERE ID=' . $_POST['id']);
        $Cache->delete_value('ip_bans_' . $IP);
    } else { //Edit & Create, Shared Validation
        $Val->SetFields('start', '1', 'regex', 'You must include the starting IP address.', array('regex' => '/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}/i'));
        $Val->SetFields('end', '1', 'regex', 'You must include the ending IP address.', array('regex' => '/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}/i'));
        $Val->SetFields('notes', '1', 'string', 'You must include the reason for the ban.');
        $Err = $Val->ValidateForm($_POST); // Validate the form
        if ($Err) {
            error($Err);
        }

        $Notes = db_string($_POST['notes']);
        $Start = db_string($_POST['start']); //Sanitized by Validation regex
        $End = db_string($_POST['end']); //See above

        if ($_POST['submit'] == 'Edit') { //Edit
            if (empty($_POST['id']) || !is_number($_POST['id'])) {
                error(404);
            }
            $DB->query("
				UPDATE ip_bans
				SET
					FromIP=INET6_ATON('$Start'),
					ToIP=INET6_ATON('$End'),
					Reason='$Notes'
				WHERE ID='" . $_POST['id'] . "'");
        } else { //Create
            $DB->query("
				INSERT INTO ip_bans
					(FromIP, ToIP, Reason)
				VALUES
					(INET6_ATON('$Start'),INET6_ATON('$End'), '$Notes')");
        }
        $Cache->delete_value('ip_bans_' . $IP);
    }
    header('tools.php?action=ip_ban');
}

define('BANS_PER_PAGE', '20');
list($Page, $Limit) = Format::page_limit(BANS_PER_PAGE);

$sql = "
	SELECT
		SQL_CALC_FOUND_ROWS
		ID,
		INET6_NTOA(FromIP),
		INET6_NTOA(ToIP),
		Reason
	FROM ip_bans ";

if (!empty($_REQUEST['notes'])) {
    $sql .= "WHERE Reason LIKE '%" . db_string($_REQUEST['notes']) . "%' ";
}

if (!empty($_REQUEST['ip']) && preg_match('/' . IP_REGEX . '/', $_REQUEST['ip'])) {
    if (!empty($_REQUEST['notes'])) {
        $sql .= "AND INET6_ATON('" . $_REQUEST['ip'] . "') BETWEEN FromIP AND ToIP ";
    } else {
        $sql .= "WHERE INET6_ATON('" . $_REQUEST['ip'] . "') BETWEEN FromIP AND ToIP ";
    }
}

$sql .= "ORDER BY FromIP ASC";
$sql .= " LIMIT " . $Limit;
$Bans = $DB->query($sql);

$DB->query('SELECT FOUND_ROWS()');
list($Results) = $DB->next_record();

$PageLinks = Format::get_pages($Page, $Results, BANS_PER_PAGE, 11);

View::show_header(t('server.tools.h2_ip_address_bans'), '', 'PageToolBan');
$DB->set_query_id($Bans);
?>
<div class="LayoutBody">
    <div class="BodyHeader">
        <h2 class="BodyHeader-nav"><?= t('server.tools.h2_ip_address_bans') ?></h2>
    </div>
    <form class="create_form" name="ban" action="" method="post">
        <input type="hidden" name="action" value="ip_ban" />
        <input type="hidden" name="auth" value="<?= $LoggedUser['AuthKey'] ?>" />
        <div class="Form-rowList" variant="header">
            <div class="Form-rowHeader">
                <?= t('server.tools.h2_ip_address_bans') ?>
            </div>
            <div class="Form-row">
                <div class="Form-label">
                    <?= t('server.tools.ip_range') ?>
                </div>
                <div class="Form-inputs">
                    <input class="Input is-small" type="text" size="12" name="start" />
                    -
                    <input class="Input is-small" type="text" size="12" name="end" />
                </div>
            </div>
            <div class="Form-row">
                <div class="Form-label">
                    <?= t('server.tools.notes') ?>
                </div>
                <div class="Form-inputs">
                    <input class="Input" type="text" size="72" name="notes" />
                </div>
            </div>
            <div class="Form-row">
                <div class="Form-submit">
                    <button class="Button" type="submit" name="submit" value="Create"><?= t('server.common.new') ?></button>
                </div>
            </div>
        </div>
    </form>
    <form class="Form SearchPage Box SearchBan" name="bans" action="" method="get">
        <div class="SearchPageBody">
            <table class="Form-rowList">
                <tr class="Form-row">
                    <td class="Form-label"><label for="ip"><?= t('server.tools.ip_range') ?>:</label></td>
                    <td class="Form-inputs">
                        <input type="hidden" name="action" value="ip_ban" />
                        <input class="Input" type="text" id="ip" name="ip" size="20" value="<?= (!empty($_GET['ip']) ? display_str($_GET['ip']) : '') ?>" />
                    </td>
                </tr>
                <tr class="Form-row">
                    <td class="Form-label"><label for="notes"><?= t('server.tools.notes') ?>:</label></td>
                    <td class="Form-inputs">
                        <input type="hidden" name="action" value="ip_ban" />
                        <input class="Input" type="text" id="notes" name="notes" size="60" value="<?= (!empty($_GET['notes']) ? display_str($_GET['notes']) : '') ?>" />
                    </td>
                </tr>
            </table>
        </div>
        <div class="SearchPageFooter">
            <div class="SearchPageFooter-actions">
                <input class="Button" type="submit" value="<?= t('server.common.search') ?>" />
            </div>
        </div>
    </form>
    <? if ($DB->record_count() > 0) { ?>
        <? View::pages($PageLinks) ?>
        <table class="Table">
            <tr class="Table-rowHeader">
                <td class="Table-cell" colspan="2">
                    <span data-tooltip=""><?= t('server.tools.ip_range') ?></span>
                </td>
                <td class="Table-cell"><?= t('server.tools.notes') ?></td>
                <td class="Table-cell"><?= t('server.tools.operations') ?></td>
            </tr>
            <?
            $Row = 'a';
            while (list($ID, $Start, $End, $Reason) = $DB->next_record()) {
                $Row = $Row === 'a' ? 'b' : 'a';
            ?>
                <tr class="row<?= $Row ?>">
                    <form class="manage_form" name="ban" action="" method="post">
                        <input type="hidden" name="id" value="<?= $ID ?>" />
                        <input type="hidden" name="action" value="ip_ban" />
                        <input type="hidden" name="auth" value="<?= $LoggedUser['AuthKey'] ?>" />
                        <td class="Table-cell" colspan="2">
                            <input class="Input is-small" type="text" size="12" name="start" value="<?= $Start ?>" />
                            -
                            <input class="Input is-small" type="text" size="12" name="end" value="<?= $End ?>" />
                        </td>
                        <td class="Table-cell">
                            <input class="Input" type="text" size="72" name="notes" value="<?= $Reason ?>" />
                        </td>
                        <td class="Table-cell">
                            <button class="Button" type="submit" name="submit" value="Edit"><?= t('server.common.edit') ?></button>
                            <button class="Button" type="submit" name="submit" value="Delete"><?= t('server.common.delete') ?></button>
                        </td>
                    </form>
                </tr>
            <?
            }
            ?>
        </table>
        <? View::pages($PageLinks) ?>
    <? } ?>
</div>
<? View::show_footer(); ?>