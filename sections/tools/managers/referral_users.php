<?php
if (!check_perms('admin_view_referrals')) {
    error(403);
}

$ReferralManager = new Gazelle\Manager\Referral;

if (isset($_POST['id'])) {
    authorize();
    if (!check_perms('admin_manage_referrals')) {
        error(403);
    }

    $ReferralManager->deleteUserReferral($_POST['id']);
}

define('USERS_PER_PAGE', 50);
list($Page, $Limit) = Format::page_limit(USERS_PER_PAGE);

$StartDate = isset($_GET['start_date']) ? $_GET['start_date'] : NULL;
$EndDate = isset($_GET['start_date']) ? $_GET['end_date'] : NULL;
$Site = (isset($_GET['site']) && strlen($_GET['site'])) ? $_GET['site'] : NULL;
$Username = (isset($_GET['username']) && strlen($_GET['username'])) ? $_GET['username'] : NULL;
$Invite = (isset($_GET['invite']) && strlen($_GET['invite'])) ? $_GET['invite'] : NULL;

if ($StartDate != NULL) {
    list($Y, $M, $D) = explode('-', $StartDate);
    if (!checkdate($M, $D, $Y)) {
        $StartDate = NULL;
    }
}

if ($EndDate != NULL) {
    list($Y, $M, $D) = explode('-', $EndDate);
    if (!checkdate($M, $D, $Y)) {
        $EndDate = NULL;
    }
}

$View = isset($_GET['view']) ? $_GET['view'] : 'all';

$ReferredUsers = $ReferralManager->getReferredUsers($StartDate, $EndDate, $Site, $Username, $Invite, $Limit, $View);

View::show_header('Referred Users');

?>

<div class="BodyHeader">
    <h2 class="BodyHeader-nav">Referred users</h2>
</div>
<div class="BodyNavLinks">
    <a class="brackets" href="tools.php?action=referral_users">All</a>
    <a class="brackets" href="tools.php?action=referral_users&amp;view=pending">Pending</a>
    <a class="brackets" href="tools.php?action=referral_users&amp;view=processed">Processed</a>
</div>
<div class="thin box">
    <form class="manage_form" name="users" action="" method="get">
        <input type="hidden" name="action" value="referral_users" />
        <input type="hidden" name="view" value="<?= $View ?>" />
        <div class="pad">
            <table class="layout">
                <tr>
                    <td class="label"><label for="start_date">Start Date</label></td>
                    <td><input class="Input" type="text" name="start_date" size="10" value="<?= display_str($StartDate) ?>" placeholder="YYYY-MM-DD" /></td>
                </tr>
                <tr>
                    <td class="label"><label for="end_date">End Date</label></td>
                    <td><input class="Input" type="text" name="end_date" size="10" value="<?= display_str($EndDate) ?>" placeholder="YYYY-MM-DD" /></td>
                </tr>
                <tr>
                    <td class="label"><label for="site">Site</label></td>
                    <td><input class="Input" type="text" name="site" size="10" value="<?= display_str($Site) ?>" /></td>
                </tr>
                <tr>
                    <td class="label"><label for="username">Username</label></td>
                    <td><input class="Input" type="text" name="username" size="10" value="<?= display_str($Username) ?>" /></td>
                </tr>
                <tr>
                    <td class="label"><label for="invite">Invite</label></td>
                    <td><input class="Input" type="text" name="invite" size="10" value="<?= display_str($Invite) ?>" /></td>
                </tr>
            </table>
            <div class="center">
                <input class="Button" type="submit" name="submit" value="<?= t('server.common.submit') ?>" />
            </div>
        </div>
    </form>
</div>

<?php
if ($ReferredUsers["Results"] > 0) { ?>
    <div class="BodyNavLinks">
        <?= Format::get_pages($Page, $ReferredUsers["Results"], USERS_PER_PAGE, 11) ?>
    </div>
    <table class="Table">
        <tr class="Table-rowHeader">
            <td class="Table-cell">User</td>
            <td class="Table-cell">Site</td>
            <td class="Table-cell">Username</td>
            <td class="Table-cell">Referred</td>
            <td class="Table-cell">Joined</td>
            <td class="Table-cell">Active</td>
            <?php
            if (check_perms('users_view_invites')) { ?>
                <td class="Table-cell">Invite</td>
            <?php
            }
            if (check_perms('admin_manage_referrals')) { ?>
                <td class="Table-cell"></td>
            <?php
            } ?>
        </tr>
        <?php
        $Row = 'b';
        foreach ($ReferredUsers["Users"] as $a) {
            list($ID, $UserID, $Site, $Username, $Referred, $Joined, $IP, $Active, $Invite) = array_values($a);
        ?>
            <tr class="Table-row">
                <form class="manage_form" name="accounts" action="" method="post">
                    <input type="hidden" name="id" value="<?= $ID ?>" />
                    <input type="hidden" name="action" value="referral_users" />
                    <input type="hidden" name="auth" value="<?= $LoggedUser['AuthKey'] ?>" />
                    <td class="Table-cell">
                        <?= $UserID ? Users::format_username($UserID, true, true, true, true) : "" ?>
                    </td>
                    <td class="Table-cell">
                        <?= $Site ?>
                    </td>
                    <td class="Table-cell">
                        <?= $Username ?>
                    </td>
                    <td class="Table-cell">
                        <?= Gazelle\Util\Time::timeDiff($Referred) ?>
                    </td>
                    <td class="Table-cell">
                        <?= Gazelle\Util\Time::timeDiff($Joined) ?>
                    </td>
                    <td class="Table-cell">
                        <input type="checkbox" name="active" disabled="disabled" <?= ($Active == '1') ? ' checked="checked"' : '' ?> />
                    </td>
                    <?php if (check_perms('users_view_invites')) { ?>
                        <td class="Table-cell">
                            <?php if (!$Active) { ?>
                                <a href="<?= CONFIG['SITE_URL'] ?>/register.php?invite=<?= $Invite ?>"><?= $Invite ?></a>
                            <?php       } ?>
                        </td>
                    <?php   }
                    if (check_perms('admin_manage_referrals')) { ?>
                        <td class="Table-cell">
                            <input class="Button" type="submit" name="submit" value="Unlink" onclick="return confirm('Are you sure you want to unlink this account? This is an irreversible action!')" />
                        </td>
                    <?php   } ?>
                </form>
            </tr>
        <?php
        }
        ?>
    </table>
    <div class="BodyNavLinks">
        <?= Format::get_pages($Page, $ReferredUsers["Results"], USERS_PER_PAGE, 11) ?>
    </div>
<?php
} else {
?>
    <div class="center">
        <h2>No users found</h2>
    </div>
<?php
}
View::show_footer();
?>