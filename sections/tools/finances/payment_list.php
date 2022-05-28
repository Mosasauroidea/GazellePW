<?php

if (!check_perms('admin_manage_payments') && !check_perms('admin_view_payments')) {
    error(403);
}
$donorMan = new Gazelle\Manager\Donation;

View::show_header('Payment Dates', 'PageToolPaymentList');
?>
<div class="BodyHeader">
    <h2 class="BodyHeader-nav">Payment Dates</h2>
</div>
<table class="Table">
    <tr class="Table-rowHeader">
        <td class="Table-cell">Payment</td>
        <td class="Table-cell">Expiry</td>
        <td class="Table-cell">Annual Rent</td>
        <td class="Table-cell">Currency Code</td>
        <td class="Table-cell">Equivalent XBT</td>
        <td class="Table-cell">Active</td>
        <?php if (check_perms('admin_manage_payments')) { ?>
            <td class="Table-cell">Submit</td>
        <?php } ?>
    </tr>
    <?php
    $Row = 'b';
    $totalRent = 0;

    $Payment = new \Gazelle\Manager\Payment;
    $paymentList = $Payment->list();

    foreach ($paymentList as $r) {
        if ($r['Active']) {
            $totalRent += $r['btcRent'];
        }
    ?>
        <tr class="Table-row">
            <?php if (!check_perms('admin_manage_payments')) { ?>
                <td class="Table-cell"><?= $r['Text'] ?></td>
                <td class="Table-cell"><?= date('Y-m-d', strtotime($r['Expiry'])) ?></td>
                <td class="Table-cell"><?= $r['Rent'] ?></td>
                <td class="Table-cell"><?= $r['cc'] ?></td>
                <td class="Table-cell" data-tooltip="Based on a rate of <?= sprintf('%0.4f', $r['fiatRate']) ?>"><?= $r['btcRent'] ?></td>
                <td class="Table-cell"><?= $r['Active'] == '1' ? 'Active' : 'Inactive' ?></td>
            <?php } else { ?>
                <form class="manage_form" name="accounts" action="" method="post">
                    <input type="hidden" name="action" value="payment_alter" />
                    <input type="hidden" name="auth" value="<?= $LoggedUser['AuthKey'] ?>" />
                    <input type="hidden" name="id" value="<?= $r['ID'] ?>" />
                    <td class="Table-cell">
                        <input class="Input" type="text" name="text" value="<?= $r['Text'] ?>" />
                    </td>
                    <td class="Table-cell">
                        <input class="Input" type="text" name="expiry" value="<?= date('Y-m-d', strtotime($r['Expiry'])) ?>" placeholder="YYYY-MM-DD" />
                    </td>
                    <td class="Table-cell">
                        <input class="Input" type="text" name="rent" value="<?= $r['Rent'] ?>" />
                    </td>
                    <td class="Table-cell">
                        <select class="Input" name="cc">
                            <option class="Select-option" value="XBT" <?= $r['cc'] == 'XBT' ? ' selected="selected"' : '' ?>>XBT</option>
                            <option class="Select-option" value="EUR" <?= $r['cc'] == 'EUR' ? ' selected="selected"' : '' ?>>EUR</option>
                            <option class="Select-option" value="USD" <?= $r['cc'] == 'USD' ? ' selected="selected"' : '' ?>>USD</option>
                        </select>
                    </td>
                    <td class="Table-cell" data-tooltip="Based on a rate of <?= sprintf('%0.4f', $r['fiatRate']) ?>"><?= $r['btcRent'] ?></td>
                    <td class="Table-cell">
                        <input type="checkbox" name="active" <?= ($r['Active'] == '1') ? ' checked="checked"' : '' ?> />
                    </td>
                    <td class="Table-cell">
                        <input class="Button" type="submit" name="submit" value="Edit" />
                        <input class="Button" type="submit" name="submit" value="Delete" onclick="return confirm('Are you sure you want to delete this payment? This is an irreversible action!')" />
                    </td>
                </form>
        </tr>
    <?php
            } /* admin_manage_payments */
        } /* foreach */

        if (check_perms('admin_manage_payments')) {
    ?>
    <tr class="Table-rowHeader">
        <td class="Table-cell" colspan="7">Create Payment</td>
    </tr>
    <tr class="Table-row">
        <form class="manage_form" name="accounts" action="" method="post">
            <input type="hidden" name="action" value="payment_alter" />
            <input type="hidden" name="auth" value="<?= $LoggedUser['AuthKey'] ?>" />
            <td class="Table-cell">
                <input class="Input" type="text" size="15" name="text" value="" />
            </td>
            <td class="Table-cell">
                <input class="Input" type="text" size="10" name="expiry" value="" placeholder="YYYY-MM-DD" />
            </td>
            <td class="Table-cell">
                <input class="Input" type="text" name="rent" value="0" />
            </td>
            <td class="Table-cell">
                <select class="Input" name="cc">
                    <option class="Select-option" value="EUR" selected="selected">EUR</option>
                    <option class="Select-option" value="USD">USD</option>
                    <option class="Select-option" value="XBT">XBT</option>
                </select>
            </td>
            <td class="Table-cell">&nbsp;</td>
            <td class="Table-cell">
                <input type="checkbox" name="active" checked="checked" />
            </td>
            <td class="Table-cell">
                <input class="Button" type="submit" name="submit" value="Create" />
            </td>
        </form>
    </tr>
<?php } /* admin_manage_payments */ ?>
</table>

<div class="BoxBody">
    <div class="BodyHeader">
        <h2 class="BodyHeader-nav">Budget Forecast</h2>
    </div>
    <table class="Table">
        <tr class="Table-rowHeader">
            <td class="Table-cell">&nbsp;</td>
            <td class="Table-cell">Monthly</td>
            <td class="Table-cell">Quarterly</td>
            <td class="Table-cell">Annual</td>
        </tr>
        <tr class="Table-row">
            <td class="Table-cell">Budget</td>
            <td class="Table-cell"><?= sprintf('%0.4f', $totalRent / 12) ?></td>
            <td class="Table-cell"><?= sprintf('%0.4f', $totalRent /  4) ?></td>
            <td class="Table-cell"><?= sprintf('%0.4f', $totalRent) ?></td>
        </tr>
        <tr class="Table-row">
            <td class="Table-cell">Actual</td>
            <td class="Table-cell"><?= sprintf('%0.4f', $donorMan->totalMonth(1)) ?></td>
            <td class="Table-cell"><?= sprintf('%0.4f', $donorMan->totalMonth(3)) ?></td>
            <td class="Table-cell"><?= sprintf('%0.4f', $donorMan->totalMonth(12)) ?></td>
        </tr>
        <tr class="Table-row">
            <td class="Table-cell">Target</td>
            <td class="Table-cell"><?= sprintf('%0.1f%%', $donorMan->totalMonth(1) / ($totalRent / 12) * 100) ?></td>
            <td class="Table-cell"><?= sprintf('%0.1f%%', $donorMan->totalMonth(3) / ($totalRent / 4) * 100) ?></td>
            <td class="Table-cell"><?= sprintf('%0.1f%%', $donorMan->totalMonth(12) / ($totalRent) * 100) ?></td>
        </tr>
    </table>
</div>
<?php

View::show_footer();
