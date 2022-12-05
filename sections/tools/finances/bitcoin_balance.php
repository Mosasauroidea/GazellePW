<?
if (!check_perms('admin_donor_log')) {
    error(403);
}
$Title = t('server.tools.bitcoin_donations');
View::show_header($Title, '', 'PageToolBitcoinBalance');

$Balance = DonationsBitcoin::get_balance() . ' BTC';
?>
<div class="LayoutBody">
    <div class="BodyHeader">
        <h2 class="BodyHeader-nav"><?= $Title ?></h2>
        <h2 class="BodyHeader-subNav"><?= t('server.tools.btc_donations_balance') ?></h2>
        <div class="BodyNavLinks">
            <a href="tools.php?action=bitcoin_unproc" class="brackets"><?= t('server.tools.btc_donations_unprocessed') ?></a>
        </div>
    </div>
    <div class="LayoutBody">
        <div class="header">
            <h3><?= $Balance ?></h3>
        </div>
        <?
        if (empty($_GET['list'])) {
        ?>
            <a href="?action=<?= $_REQUEST['action'] ?>&amp;list=1" class="brackets"><?= t('server.tools.show_donor_list') ?></a>
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
                    <th class="Table-cell"><?= t('server.tools.username') ?></th>
                    <th class="Table-cell"><?= t('server.tools.receiving_bitcoin_address') ?></th>
                    <th class="Table-cell"><?= t('server.tools.amount') ?></th>
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
</div>
<? View::show_footer(); ?>