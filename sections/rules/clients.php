<?
View::show_header(Lang::get('rules', 'clients_title'), '', 'PageRuleClient');

if (!$WhitelistedClients = $Cache->get_value('whitelisted_clients')) {
    $DB->query('
		SELECT vstring, peer_id
		FROM xbt_client_whitelist
		WHERE vstring NOT LIKE \'//%\'
		ORDER BY vstring ASC');
    $WhitelistedClients = $DB->to_array(false, MYSQLI_NUM, false);
    $Cache->cache_value('whitelisted_clients', $WhitelistedClients, 604800);
}
?>
<div class="LayoutBody">
    <? include('jump.php'); ?>
    <div class="header">
        <h2 class="general"><?= Lang::get('rules', 'clients_title') ?></h2>
        <p><?= Lang::get('rules', 'clients_summary') ?></p>
    </div>
    <div class="BoxBody">
        <table class="TableRuleClient Table">
            <tr class="Table-rowHeader">
                <td class="Table-cell"><?= Lang::get('rules', 'clients_list') ?></td>
                <td class="Table-cell" style="width: 75px">Peer ID</td>
                <!-- td style="width: 400px;"><strong>Additional Notes</strong></td> -->
            </tr>
            <?
            foreach ($WhitelistedClients as $Client) {
                //list($ClientName, $Notes) = $Client;
                list($ClientName, $PeerID) = $Client;
            ?>
                <tr class="Table-row">
                    <td class="Table-cell"><?= $ClientName ?></td>
                    <td class="Table-cell">----</td>
                </tr>
            <?  } ?>
        </table>
    </div>
</div>
<? View::show_footer(); ?>