<?
View::show_header(t('server.rules.clients_title'), '', 'PageRuleClient');

if (!$WhitelistedClients = $Cache->get_value('whitelisted_clients')) {
    $DB->query('
		SELECT vstring AS ClientName, peer_id as PeerID
		FROM xbt_client_whitelist
		WHERE vstring NOT LIKE \'//%\'
		ORDER BY vstring ASC');
    $WhitelistedClients = $DB->to_array(false, MYSQLI_ASSOC, false);
    $Cache->cache_value('whitelisted_clients', $WhitelistedClients, 604800);
}
?>

<div class="LayoutBody">
    <? include('jump.php'); ?>
    <div class="Post">
        <div class="HtmlText Post-body" id="Rules-Clients-mdx" mdx></div>
    </div>
</div>

<script>
    window.DATA.clients = <?= json_encode($WhitelistedClients) ?>;
    document.addEventListener('DOMContentLoaded', () => {
        lang.render('Rules/Clients.mdx')
    })
</script>

<? View::show_footer(); ?>