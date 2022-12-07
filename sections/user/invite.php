<?

if (isset($_GET['userid']) && check_perms('site_can_invite_always')) {
    if (!is_number($_GET['userid'])) {
        error(403);
    }

    $UserID = $_GET['userid'];
    $Sneaky = true;
} else {
    if (!$UserCount = $Cache->get_value('stats_user_count')) {
        $DB->query("
			SELECT COUNT(ID)
			FROM users_main
			WHERE Enabled = '1'");
        list($UserCount) = $DB->next_record();
        $Cache->cache_value('stats_user_count', $UserCount, 0);
    }

    $UserID = $LoggedUser['ID'];
    $Sneaky = false;
}

list($UserID, $Username, $PermissionID) = array_values(Users::user_info($UserID));


$DB->query("
	SELECT InviteKey, Email, Expires
	FROM invites
	WHERE InviterID = '$UserID'
	ORDER BY Expires");
$Pending =  $DB->to_array();

$OrderWays = array('username', 'email', 'joined', 'lastseen', 'uploads', 'uploaded', 'downloaded', 'ratio');

if (empty($_GET['order'])) {
    $CurrentOrder = 'id';
    $CurrentSort = 'asc';
    $NewSort = 'desc';
} else {
    if (in_array($_GET['order'], $OrderWays)) {
        $CurrentOrder = $_GET['order'];
        if ($_GET['sort'] == 'asc' || $_GET['sort'] == 'desc') {
            $CurrentSort = $_GET['sort'];
            $NewSort = ($_GET['sort'] == 'asc' ? 'desc' : 'asc');
        } else {
            error(404);
        }
    } else {
        error(404);
    }
}

switch ($CurrentOrder) {
    case 'username':
        $OrderBy = "um.Username";
        break;
    case 'email':
        $OrderBy = "um.Email";
        break;
    case 'joined':
        $OrderBy = "ui.JoinDate";
        break;
    case 'lastseen':
        $OrderBy = "um.LastAccess";
        break;
    case 'uploads':
        $OrderBy = "Uploads";
        break;
    case 'uploaded':
        $OrderBy = "um.Uploaded";
        break;
    case 'downloaded':
        $OrderBy = "um.Downloaded";
        break;
    case 'ratio':
        $OrderBy = "(um.Uploaded / um.Downloaded)";
        break;
    default:
        $OrderBy = "um.ID";
        break;
}

$CurrentURL = Format::get_url(array('action', 'order', 'sort'));

$DB->query(
    "
	SELECT
		um.ID,
		um.Email,
		um.Uploaded,
		um.Downloaded,
		ui.JoinDate,
		um.LastAccess,
        COUNT(t.ID) AS Uploads
	FROM users_main AS um
		LEFT JOIN users_info AS ui ON ui.UserID = um.ID
        LEFT JOIN torrents AS t ON t.UserID = um.ID
	WHERE ui.Inviter = '$UserID'
    GROUP BY um.ID
	ORDER BY $OrderBy $CurrentSort
    "
);

$Invited = $DB->to_array();

View::show_header(t('server.user.invites'), '', 'PageUserInvite');

?>
<div class="LayoutBody">
    <div class="BodyHeader">
        <h2 class="BodyHeader-nav"><?= t('server.user.invites') ?></h2>
        <div class="BodyNavLinks">
            <a href="user.php?action=invitetree<? if ($Sneaky) {
                                                    echo '&amp;userid=' . $UserID;
                                                } ?>" class="brackets">
                <?= t('server.user.invite_tree') ?>
            </a>
        </div>
    </div>
    <? if ($UserCount >= CONFIG['USER_LIMIT'] && !check_perms('site_can_invite_always')) { ?>
        <div class="BoxBody notice">
            <p><?= t('server.user.because_the_user_limit_has_been_reached_you_are_unable_to_send_invites_at_this_time') ?></p>
        </div>
    <? }

    /*
    Users cannot send invites if they:
        -Are on ratio watch
        -Have disabled leeching
        -Have disabled invites
        -Have no invites (Unless have unlimited)
        -Cannot 'invite always' and the user limit is reached
*/

    $DB->query("
	SELECT can_leech
	FROM users_main
	WHERE ID = $UserID");
    list($CanLeech) = $DB->next_record();


    if (
        !$Sneaky
        && !$LoggedUser['RatioWatch']
        && $CanLeech
        && empty($LoggedUser['DisableInvites'])
        && check_perms('site_can_invite')
        && ($LoggedUser['Invites'] > 0 || check_perms('site_send_unlimited_invites'))
        && ($UserCount <= CONFIG['USER_LIMIT'] || CONFIG['USER_LIMIT'] == 0 || check_perms('site_can_invite_always'))
    ) { ?>

        <form class="send_form pad" name="invite" action="user.php" method="post">
            <input type="hidden" name="action" value="take_invite" />
            <input type="hidden" name="auth" value="<?= $LoggedUser['AuthKey'] ?>" />
            <div class="Form-rowList" variant="header">
                <div class="Form-rowHeader">
                    <?= t('server.user.invite') ?>
                </div>
                <div class="Form-row HtmlText" id="invite_rules_container">
                    <ul>
                        <li><?= t('server.user.invite_rules_1') ?></li>
                        <li><?= t('server.user.invite_rules_2') ?></li>
                        <li><?= t('server.user.invite_rules_3') ?></li>
                        <li><?= t('server.user.invite_rules_4') ?></li>
                        <li><?= t('server.user.invite_rules_5') ?></li>
                    </ul>
                </div>
                <div class="Form-row HtmlText">
                    <strong class="u-colorWarning"><?= t('server.user.invite_rules_6') ?></strong>
                </div>
                <div class="Form-row HtmlText"><?= t('server.user.invite_rules_7') ?></div>

                <div class="Form-row">
                    <div class="Form-label"><?= t('server.user.email_address') ?>:</div>
                    <div class="Form-inputs">
                        <input class="Input" type="email" name="email" size="60" />
                    </div>
                </div>
                <? if (check_perms('users_invite_notes')) { ?>
                    <div class="Form-row">
                        <div class="Form-label"><?= t('server.user.invite_note') ?>:</div>
                        <div class="Form-inputs">
                            <input class="Input" type="text" name="reason" size="60" maxlength="255" />
                        </div>
                    </div>
                <?  } ?>
                <div class="Form-row">
                    <button class="Button" type="submit" value="Invite"><?= t('server.common.submit') ?></button>
                </div>
            </div>
        </form>
    <? } elseif (!empty($LoggedUser['DisableInvites'])) { ?>
        <div class="BoxBody" style="text-align: center;">
            <strong class="u-colorWarning"><?= t('server.user.your_invites_have_been_disabled') ?></strong>
        </div>
    <? } elseif ($LoggedUser['RatioWatch'] || !$CanLeech) { ?>
        <div class="BoxBody" style="text-align: center;">
            <strong class="u-colorWarning"><?= t('server.user.you_may_not_send_invites_while_on_ratio_watch_or') ?></strong>
        </div>
    <? } ?>
    <? if (!empty($Pending)) { ?>
        <div class="Group">
            <div class="Group-header">
                <div class="Group-headerTitle" id="pending_invites_header"><?= t('server.user.pending_invites') ?></div>
            </div>
            <div class="Group-body" id="pending_invites_container">
                <table class="TableInvite Table">
                    <tr class="Table-rowHeader">
                        <td class="Table-cell"><?= t('server.user.email_address') ?></td>
                        <td class="Table-cell"><?= t('server.user.expires_in') ?></td>
                        <td class="Table-cell"><?= t('server.user.invite_link') ?></td>
                        <td class="Table-cell"><?= t('server.user.delete_invite') ?></td>
                    </tr>
                    <?
                    foreach ($Pending as $Invite) {
                        list($InviteKey, $Email, $Expires) = $Invite;
                    ?>
                        <tr class="Table-row">
                            <td class="Table-cell"><?= display_str($Email) ?></td>
                            <td class="Table-cell"><?= time_diff($Expires) ?></td>
                            <td class="Table-cell"><a href="register.php?invite=<?= $InviteKey ?>"><?= t('server.user.invite_link') ?></a></td>
                            <td class="Table-cell"><a href="user.php?action=delete_invite&amp;invite=<?= $InviteKey ?>&amp;auth=<?= $LoggedUser['AuthKey'] ?>" onclick="return confirm('<?= t('server.user.are_you_sure_you_want_to_delete_this_invite') ?>');"><?= t('server.user.delete_invite') ?></a></td>
                        </tr>
                    <? } ?>
                </table>
            </div>
        </div>
    <? } ?>
    <div class="Group">
        <div class="Group-header">
            <div class="Group-headerTitle" id="invite_table_header"><?= t('server.user.invitee_list') ?></div>
        </div>
        <div class="Group-body TableContainer" id="invite_table_container">
            <table class="Table TableInvite">
                <tr class="Table-rowHeader">
                    <td class="Table-cell Table-cell"><a href="user.php?action=invite&amp;order=username&amp;sort=<?= (($CurrentOrder == 'username') ? $NewSort : 'desc') ?>&amp;<?= $CurrentURL ?>"><?= t('server.user.username') ?></a></td>
                    <td class="Table-cell"><a href="user.php?action=invite&amp;order=email&amp;sort=<?= (($CurrentOrder == 'email') ? $NewSort : 'desc') ?>&amp;<?= $CurrentURL ?>"><?= t('server.user.email') ?></a></td>
                    <td class="Table-cell"><a href="user.php?action=invite&amp;order=joined&amp;sort=<?= (($CurrentOrder == 'joined') ? $NewSort : 'desc') ?>&amp;<?= $CurrentURL ?>"><?= t('server.user.joined') ?></a></td>
                    <td class="Table-cell"><a href="user.php?action=invite&amp;order=lastseen&amp;sort=<?= (($CurrentOrder == 'lastseen') ? $NewSort : 'desc') ?>&amp;<?= $CurrentURL ?>"><?= t('server.user.last_seen') ?></a></td>
                    <td class="Table-cell Table-cellRight"><a href="user.php?action=invite&amp;order=uploads&amp;sort=<?= (($CurrentOrder == 'uploads') ? $NewSort : 'desc') ?>&amp;<?= $CurrentURL ?>"><?= t('server.user.uploads') ?></a></td>
                    <td class="Table-cell Table-cellRight"><a href="user.php?action=invite&amp;order=uploaded&amp;sort=<?= (($CurrentOrder == 'uploaded') ? $NewSort : 'desc') ?>&amp;<?= $CurrentURL ?>"><?= t('server.user.upload') ?></a></td>
                    <td class="Table-cell Table-cellRight"><a href="user.php?action=invite&amp;order=downloaded&amp;sort=<?= (($CurrentOrder == 'downloaded') ? $NewSort : 'desc') ?>&amp;<?= $CurrentURL ?>"><?= t('server.common.download') ?></a></td>
                    <td class="Table-cell Table-cellRight"><a href="user.php?action=invite&amp;order=ratio&amp;sort=<?= (($CurrentOrder == 'ratio') ? $NewSort : 'desc') ?>&amp;<?= $CurrentURL ?>"><?= t('server.user.ratio') ?></a></td>
                </tr>
                <?
                foreach ($Invited as $User) {
                    list($ID, $Email, $Uploaded, $Downloaded, $JoinDate, $LastAccess, $Uploads) = $User;
                ?>
                    <tr class="Table-row">
                        <td class="Table-cell"><?= Users::format_username($ID, true, true, true, true) ?></td>
                        <td class="Table-cell"><?= display_str($Email) ?></td>
                        <td class="Table-cell"><?= time_diff($JoinDate, 1) ?></td>
                        <td class="Table-cell"><?= time_diff($LastAccess, 1); ?></td>
                        <td class="Table-cell Table-cellRight"><?= number_format($Uploads) ?></td>
                        <td class="Table-cell Table-cellRight"><?= Format::get_size($Uploaded) ?></td>
                        <td class="Table-cell Table-cellRight"><?= Format::get_size($Downloaded) ?></td>
                        <td class="Table-cell Table-cellRight"><?= Format::get_ratio_html($Uploaded, $Downloaded) ?></td>
                    </tr>
                <?  } ?>
            </table>
        </div>
    </div>
</div>
<? View::show_footer(); ?>