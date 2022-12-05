<?
if (!check_perms('users_view_invites')) {
    error(403);
}
$Title = t('server.tools.invite_pool');
View::show_header($Title, '', 'PageToolInvitePool');
define('INVITES_PER_PAGE', 50);
list($Page, $Limit) = Format::page_limit(INVITES_PER_PAGE);

if (!empty($_POST['invitekey']) && check_perms('users_edit_invites')) {
    authorize();

    $DB->query("
		DELETE FROM invites
		WHERE InviteKey = '" . db_string($_POST['invitekey']) . "'");
}

if (!empty($_GET['search'])) {
    $Search = db_string($_GET['search']);
} else {
    $Search = '';
}

$sql = "
	SELECT
		SQL_CALC_FOUND_ROWS
		um.ID,
		um.IP,
		i.InviteKey,
		i.Expires,
		i.Email
	FROM invites AS i
		JOIN users_main AS um ON um.ID = i.InviterID ";
if ($Search) {
    $sql .= "
	WHERE i.Email LIKE '%$Search%' ";
}
$sql .= "
	ORDER BY i.Expires DESC
	LIMIT $Limit";
$RS = $DB->query($sql);

$DB->query('SELECT FOUND_ROWS()');
list($Results) = $DB->next_record();

$DB->set_query_id($RS);
?>
<div class="LayoutBody">
    <div class="BodyHeader">
        <h2 class="BodyHeader-nav"><?= $Title ?></h2>
    </div>

    <div class="BodyContent">
        <form class="Form SearchPage Box SearchInvite" name="invites" action="" method="get">
            <div class="SearchPageBody">
                <table class="Form-rowList">
                    <tr class="Form-row">
                        <td class="Form-label"><?= t('server.tools.email_address') ?>:</td>
                        <td class="Form-inputs">
                            <input type="hidden" name="action" value="invite_pool" />
                            <input class="Input" type="email" name="search" size="60" value="<?= display_str($Search) ?>" />
                        </td>

                    </tr>
                </table>
            </div>
            <div class="SearchPageFooter">
                <div class="SearchPageFooter-resultCount">
                    <div id="unused_invites">
                        <?= number_format($Results) ?> <?= t('server.tools.unused_invites_have_been_sent') ?>
                    </div>
                </div>
                <div class="SearchPageFooter-actions">
                    <input class="Button" type="submit" value="<?= t('server.common.search') ?>" />
                </div>
            </div>
        </form>
    </div>
    <?
    if ($DB->record_count() > 0) {
        $Pages = Format::get_pages($Page, $Results, INVITES_PER_PAGE, 11);
        if ($Pages) { ?>
            <div class="BodyNavLinks pager"><?= ($Pages) ?></div>
        <?  } ?>
        <table class="Table">
            <tr class="Table-rowHeader">
                <td class="Table-cell"><?= t('server.tools.inviter') ?></td>
                <td class="Table-cell"><?= t('server.tools.email_address') ?></td>
                <td class="Table-cell"><?= t('server.tools.ip_address') ?></td>
                <td class="Table-cell"><?= t('server.tools.invite_code') ?></td>
                <td class="Table-cell"><?= t('server.tools.expires') ?></td>
                <? if (check_perms('users_edit_invites')) { ?>
                    <td class="Table-cell"><?= t('server.tools.controls') ?></td>
                <? } ?>
            </tr>
            <?
            $Row = 'b';
            while (list($UserID, $IP, $InviteKey, $Expires, $Email) = $DB->next_record()) {
                $Row = $Row === 'b' ? 'a' : 'b';
            ?>
                <tr class="Table-row">
                    <td><?= Users::format_username($UserID, true, true, true, true) ?></td>
                    <td><?= display_str($Email) ?></td>
                    <td><?= Tools::display_ip($IP) ?></td>
                    <td><?= display_str($InviteKey) ?></td>
                    <td><?= time_diff($Expires) ?></td>
                    <? if (check_perms('users_edit_invites')) { ?>
                        <td>
                            <form class="delete_form" name="invite" action="" method="post">
                                <input type="hidden" name="action" value="invite_pool" />
                                <input type="hidden" name="auth" value="<?= $LoggedUser['AuthKey'] ?>" />
                                <input type="hidden" name="invitekey" value="<?= display_str($InviteKey) ?>" />
                                <button class="Button" type="submit" value="Delete"><?= t('server.common.delete') ?></button>
                            </form>
                        </td>
                    <?      } ?>
                </tr>
            <?  } ?>
        </table>
        <? if ($Pages) { ?>
            <div class="BodyNavLinks pager"><?= ($Pages) ?></div>
        <?  }
    } else {
        View::line(t('server.common.no_results'));
        ?>

    <? } ?>
</div>
<?
View::show_footer(); ?>