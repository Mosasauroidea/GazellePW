<?
if (!check_perms('admin_donor_log')) {
    error(403);
}
$Title = Lang::get('tools', 'bitcoin_donation_balance');
View::show_header($Title, '', 'PageToolBitcoinBalance');

$Balance = DonationsBitcoin::get_balance() . ' BTC';
?>
<div class="BodyHeader">
    <h2 class="BodyHeader-nav"><?= $Title ?></h2>
</div>
<div class="LayoutBody">
    <div class="header">
        <h3><?= $Balance ?></h3>
    </div>
    <?
    if (empty($_GET['list'])) {
    ?>
        <a href="?action=<?= $_REQUEST['action'] ?>&amp;list=1" class="brackets"><?= Lang::get('tools', 'show_donor_list') ?></a>
    <?
    } else {
        $BitcoinAddresses = DonationsBitcoin::get_received();
        $DB->query("
		SELECT i.UserID, i.BitcoinAddress
		FROM users_info AS i
			JOIN users_main AS m ON m.ID = i.UserID
		WHERE BitcoinAddress != ''
		ORDER BY m.Username ASC");
    ?>
        <table class="Table">
            <tr class="Table-rowHeader">
                <th class="Table-cell"><?= Lang::get('tools', 'username') ?></th>
                <th class="Table-cell"><?= Lang::get('tools', 'receiving_bitcoin_address') ?></th>
                <th class="Table-cell"><?= Lang::get('tools', 'amount') ?></th>
            </tr>
            <?
            while (list($UserID, $BitcoinAddress) = $DB->next_record(MYSQLI_NUM, false)) {
                if (!isset($BitcoinAddresses[$BitcoinAddress])) {
                    continue;
                }
            ?>
                <tr class="Table-row">
                    <td class="Table-cell"><?= Users::format_username($UserID, true, false, false, false) ?></td>
                    <td class="Table-cell"><tt><?= $BitcoinAddress ?></tt></td>
                    <td class="Table-cell"><?= $BitcoinAddresses[$BitcoinAddress] ?> BTC</td>
                </tr>
            <?
            }
            ?>
        </table>
    <?
    }
    ?>
</div>
<? View::show_footer(); ?>