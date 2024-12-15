<?

use Gazelle\Manager\Donation;

if (!check_perms('users_mod')) {
    error(403);
}
$Title = t('server.tools.bitcoin_donations');
View::show_header($Title, '', 'PageToolBitcoinUnproc');

// Find all donors
$AllDonations = DonationsBitcoin::get_received();

$DB->query("
	SELECT BitcoinAddress, SUM(Amount)
	FROM donations_bitcoin
	GROUP BY BitcoinAddress");
$OldDonations = G::$DB->to_pair(0, 1, false);
?>
<div class="LayoutBody">
    <div class="BodyHeader">
        <h2 class="BodyHeader-nav"><?= $Title ?></h2>
        <h2 class="BodyHeader-subNav"><?= t('server.tools.btc_donations_unprocessed') ?></h2>
        <div class="BodyNavLinks">
            <a href="tools.php?action=bitcoin_balance" class="brackets"><?= t('server.tools.btc_donations_balance') ?></a>
        </div>
    </div>

    <div class="box2">
        <div class="pad"><?= t('server.tools.do_not_process_these_donations_manually') ?></div>
    </div>
    <?
    $NewDonations = array();
    $TotalUnproc = 0;
    foreach ($AllDonations as $Address => $Amount) {
        if (isset($OldDonations[$Address])) {
            if ($Amount == $OldDonations[$Address]) { // Direct comparison should be fine as everything comes from bitcoind
                continue;
            }
            $Debug->log_var(array('old' => $OldDonations[$Address], 'new' => $Amount), t('server.tools.new_donations_from', ['Values' => [$Address]]));
            // PHP doesn't do fixed-point math, and json_decode has already botched the precision
            // so let's just round this off to satoshis and pray that we're on a 64 bit system
            $Amount = round($Amount - $OldDonations[$Address], 8);
        }
        $TotalUnproc += $Amount;
        $NewDonations[$Address] = $Amount;
    }
    ?>
    <table class="Table">
        <tr class="Table-rowHeader">
            <td class="Table-cell"><?= t('server.tools.bitcoin_address') ?></td>
            <td class="Table-cell"><?= t('server.tools.user') ?></td>
            <td class="Table-cell"><?= t('server.tools.unprocessed_amount_total') ?>: <?= $TotalUnproc ?: '0' ?>)</td>
            <td class="Table-cell"><?= t('server.tools.total_amount') ?></td>
            <td class="Table-cell"><?= t('server.tools.donor_rank') ?></td>
            <td class="Table-cell"><?= t('server.tools.special_rank') ?></td>
        </tr>
        <?
        if (!empty($NewDonations)) {
            foreach (DonationsBitcoin::get_userids(array_keys($NewDonations)) as $Address => $UserID) {
                $donation = new Donation;
                $DonationEUR = $donation->currencyExchange($NewDonations[$Address], 'BTC');
        ?>
                <tr class="Table-row">
                    <td class="Table-cell"><?= $Address ?></td>
                    <td class="Table-cell"><?= Users::format_username($UserID, true, false, false) ?></td>
                    <td class="Table-cell"><?= $NewDonations[$Address] ?> (<?= "$DonationEUR EUR" ?>)</td>
                    <td class="Table-cell"><?= $AllDonations[$Address] ?></td>
                    <td class="Table-cell"><?= (int)$donation->rank($UserID) ?></td>
                    <td class="Table-cell"><?= (int)$donation->specialRank($UserID) ?></td>
                </tr>
            <?  }
        } else { ?>
            <tr class="Table-row">
                <td class="Table-cell Table-cellCenter" colspan="7"><?= t('server.tools.no_unprocessed_bitcoin_donations') ?></td>
            </tr>
        <? } ?>
    </table>
</div>
<?
View::show_footer();
