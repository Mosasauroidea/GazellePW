<?
// Props to Leto of StC.
if (!check_perms('users_view_invites') && !check_perms('users_disable_users') && !check_perms('users_edit_invites') && !check_perms('users_disable_any')) {
    error(404);
}
View::show_header(t('server.tools.h2_manipulate_invite_tree'));

if ($_POST['id']) {
    authorize();

    if (!is_number($_POST['id'])) {
        error(403);
    }
    if (!$_POST['comment']) {
        error(t('server.tools.please_enter_a_comment_to_add_to_the_users_affected'));
    } else {
        $Comment = date('Y-m-d H:i:s') . " - ";
        $Comment .= db_string($_POST['comment']);
        $Comment .= "\n" . "Manipulate Tree used by " . $LoggedUser['Username'];
    }
    $UserID = $_POST['id'];
    $DB->query("
			SELECT
				t1.TreePosition,
				t1.TreeID,
				t1.TreeLevel,
				(
					SELECT
						t2.TreePosition
					FROM invite_tree AS t2
					WHERE t2.TreeID = t1.TreeID
						AND t2.TreeLevel = t1.TreeLevel
						AND t2.TreePosition > t1.TreePosition
					ORDER BY t2.TreePosition
					LIMIT 1
				) AS MaxPosition
			FROM invite_tree AS t1
			WHERE t1.UserID = $UserID");
    list($TreePosition, $TreeID, $TreeLevel, $MaxPosition) = $DB->next_record();
    if (!$MaxPosition) {
        $MaxPosition = 1000000;
    } // $MaxPermission is null if the user is the last one in that tree on that level
    if (!$TreeID) {
        $Msg = "Successfully!";
    } else {
        $DB->query("
			SELECT
				UserID
			FROM invite_tree
			WHERE TreeID = $TreeID
				AND TreePosition > $TreePosition
				AND TreePosition < $MaxPosition
				AND TreeLevel > $TreeLevel
			ORDER BY TreePosition");
        $BanList = array();

        while (list($Invitee) = $DB->next_record()) {
            $BanList[] = $Invitee;
        }
        var_dump($BanList);
        die();

        foreach ($BanList as $Key => $InviteeID) {
            if ($_POST['perform'] === 'nothing') {
                Tools::update_user_notes($InviteeID, $Comment . "\n\n");
                $Msg = "Successfully commented on entire invite tree!";
            } elseif ($_POST['perform'] === 'disable') {
                Tools::disable_users($InviteeID, $Comment);
                $Msg = "Successfully banned entire invite tree!";
            } elseif ($_POST['perform'] === 'inviteprivs') { // DisableInvites =1
                Tools::update_user_notes($InviteeID, $Comment . "\n\n");
                $DB->query("
				UPDATE users_info
				SET DisableInvites = '1'
				WHERE UserID = '$InviteeID'");
                $Msg = "Successfully removed invite privileges from entire tree!";
            } else {
                error(403);
            }
        }
    }
}
?>

<div class="LayoutBody">
    <div class="BodyHeader">
        <h2 class="BodyHeader-nav"><?= t('server.tools.h2_manipulate_invite_tree') ?></h2>
    </div>

    <form class="Form manage_form" name="user" action="" method="post">
        <input type="hidden" id="action" name="action" value="manipulate_tree" />
        <input type="hidden" name="auth" value="<?= $LoggedUser['AuthKey'] ?>" />
        <table class="Form-rowList" variant="header">
            <tr class="Form-rowHeader">
                <td>
                    <?= t('server.tools.h2_manipulate_invite_tree') ?>
                </td>
            </tr>
            <tr class="Form-row">
                <td class="Form-label"><strong><?= t('server.tools.userid') ?>:</strong></td>
                <td class="Form-inputs"><input class="Input" type="text" size="10" name="id" id="id" /></td>
            </tr>
            <tr class="Form-row">
                <td class="Form-label"><strong><?= t('server.tools.mandatory_comment') ?>:</strong></td>
                <td class="Form-inputs"><input class="Input" type="text" size="40" name="comment" id="comment" /></td>
            </tr>
            <tr class="Form-row">
                <td class="Form-label"><strong><?= t('server.common.actions') ?>:</strong></td>
                <td class="Form-inputs">
                    <select class="Input" name="perform">
                        <option class="Select-option" value="nothing" <?
                                                                        if ($_POST['perform'] === 'nothing') {
                                                                            echo ' selected="selected"';
                                                                        } ?>><?= t('server.tools.do_nothing') ?></option>
                        <option class="Select-option" value="disable" <?
                                                                        if ($_POST['perform'] === 'disable') {
                                                                            echo ' selected="selected"';
                                                                        } ?>><?= t('server.tools.disable_entire_tree') ?></option>
                        <option class="Select-option" value="inviteprivs" <?
                                                                            if ($_POST['perform'] === 'inviteprivs') {
                                                                                echo ' selected="selected"';
                                                                            } ?>><?= t('server.tools.disable_invites_privileges') ?></option>
                    </select>
                </td>
            </tr>
            <tr class="Form-row">
                <td><input class="Button" type="submit" value="<?= t('server.common.submit') ?>" /></td>
            </tr> <? if ($Msg) { ?>
                <tr class="Form-row">
                    <td>

                        <div class="center">
                            <p class="u-colorWarning" style="text-align: center;"><?= $Msg ?></p>
                        </div>
                    </td>
                </tr>
            <?  } ?>
        </table>
    </form>
</div>

<? View::show_footer(); ?>