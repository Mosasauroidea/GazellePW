<?php
function type_list($Types, $Selected = 0) {
    $Ret = '';
    foreach ($Types as $id => $name) {
        $Ret .= "<option value=\"$id\"";
        if ($Selected == $id) {
            $Ret .= ' selected="selected"';
        }
        $Ret .= ">$name</option>\n";
    }
    return $Ret;
}

if (!check_perms('admin_manage_referrals')) {
    error(403);
}

$ReferralManager = new Gazelle\Manager\Referral;
$ReferralAccounts = $ReferralManager->getFullAccounts();

View::show_header('Referral Accounts');
?>
<div class="BodyHeader">
    <h2 class="BodyHeader-nav">Referral account manager</h2>
    <?php if ($ReferralManager->readOnly) { ?>
        <p>
            <strong class="u-colorWarning">DB key not loaded or incorrect - editing disabled</strong>
        </p>
    <?php } ?>
</div>
<table class="Table">
    <tr class="Table-rowHeader">
        <td class="Table-cell">Site</td>
        <td class="Table-cell">URL</td>
        <td class="Table-cell">User</td>
        <td class="Table-cell">Password</td>
        <td class="Table-cell">Type</td>
        <td class="Table-cell">Active</td>
        <td class="Table-cell">Cookie</td>
        <td class="Table-cell">Submit</td>
    </tr>
    <?php
    $Row = 'b';
    foreach ($ReferralAccounts as $a) {
        list($ID, $Site, $URL, $User, $Password, $Active, $Type, $Cookie) = array_values($a);
    ?>
        <tr class="Table-row">
            <form class="manage_form" name="accounts" action="" method="post">
                <input type="hidden" name="id" value="<?= $ID ?>" />
                <input type="hidden" name="action" value="referral_alter" />
                <input type="hidden" name="auth" value="<?= $LoggedUser['AuthKey'] ?>" />
                <td class="Table-cell">
                    <input class="Input" type="text" size="10" name="site" value="<?= $Site ?>" />
                </td>
                <td class="Table-cell">
                    <input class="Input" type="text" size="15" name="url" value="<?= $URL ?>" />
                </td>
                <td class="Table-cell">
                    <input class="Input" type="text" size="10" name="user" value="<?= $User ?>" />
                </td>
                <td class="Table-cell">
                    <input class="Input" type="password" size="10" name="password" />
                </td>
                <td class="Table-cell">
                    <select class="Input" name="type">
                        <?= type_list($ReferralManager->getTypes(), $Type) ?>
                    </select>
                </td>
                <td class="Table-cell">
                    <input type="checkbox" name="active" <?= ($Active == '1') ? ' checked="checked"' : '' ?> />
                </td>
                <td class="Table-cell">
                    <input class="Input" type="text" size="10" name="cookie" />
                </td>
                <td class="Table-cell">
                    <?php if (!$ReferralManager->readOnly) { ?>
                        <input class="Button" type="submit" name="submit" value="Edit" />
                    <?php } ?>
                    <input class="Button" type="submit" name="submit" value="Delete" onclick="return confirm('Are you sure you want to delete this account? This is an irreversible action!')" />
                </td>
            </form>
        </tr>
    <?php
    }
    if (!$ReferralManager->readOnly) {
    ?>
        <tr class="Table-rowHeader">
            <td class="Table-cell" colspan="8">Create Account</td>
        </tr>
        <tr class="Table-row">
            <form class="create_form" name="accounts" action="" method="post">
                <input type="hidden" name="action" value="referral_alter" />
                <input type="hidden" name="auth" value="<?= $LoggedUser['AuthKey'] ?>" />
                <td class="Table-cell">
                    <input class="Input" type="text" size="10" name="site" />
                </td>
                <td class="Table-cell">
                    <input class="Input" type="text" size="15" name="url" />
                </td>
                <td class="Table-cell">
                    <input class="Input" type="text" size="10" name="user" />
                </td>
                <td class="Table-cell">
                    <input class="Input" type="password" size="10" name="password" />
                </td>
                <td class="Table-cell">
                    <select class="Input" name="type">
                        <?= type_list($ReferralManager->getTypes()) ?>
                    </select>
                </td>
                <td class="Table-cell">
                    <input type="checkbox" name="active" checked="checked" />
                </td>
                <td class="Table-cell">
                    <input class="Input" type="text" size="10" name="cookie" />
                </td>
                <td class="Table-cell">
                    <input class="Button" type="submit" name="submit" value="Create" />
                </td>
            </form>
        </tr>
    <?php
    } ?>
</table>
<?php
View::show_footer();
?>