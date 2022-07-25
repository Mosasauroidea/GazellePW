<?
if (!check_perms('admin_whitelist')) {
    error(403);
}

View::show_header(t('server.tools.client_whitelist_manager'));
$DB->query('
	SELECT id, vstring, peer_id
	FROM xbt_client_whitelist
	ORDER BY peer_id ASC');
?>
<div class="BodyHeader">
    <h2 class="BodyHeader-nav"><?= t('server.tools.client_whitelist') ?></h2>
</div>
<div class="box2 pad thin">
    <form class="add_form" name="clients" action="" method="post">
        <input type="hidden" name="action" value="whitelist_alter" />
        <input type="hidden" name="auth" value="<?= $LoggedUser['AuthKey'] ?>" />
        <table class="Table">
            <tr class="Table-rowHeader">
                <td class="Table-cell" colspan="4"><?= t('server.tools.add_client') ?></td>
            </tr>
            <tr class="Table-row">
                <td class="Table-cell">
                    <input class="Input" type="text" size="60" name="client" placeholder="Client name" />
                </td>
                <td class="Table-cell">
                    <input class="Input" type="text" size="10" name="peer_id" placeholder="Peer ID" />
                </td>
                <td class="Table-cell">
                    <input class="Button" type="submit" value="Create" />
                </td>
            </tr>
        </table>
    </form>
    <table class="Table">
        <tr class="Table-rowHeader">
            <td class="Table-cell"><?= t('server.tools.client') ?></td>
            <td class="Table-cell"><?= t('server.tools.peer_id') ?></td>
            <td class="Table-cell"><?= t('server.tools.submit') ?></td>
        </tr>
        <?
        $Row = 'b';
        while (list($ID, $Client, $Peer_ID) = $DB->next_record()) {
            $Row = $Row === 'a' ? 'b' : 'a';
        ?>
            <form class="manage_form" name="clients" action="" method="post">
                <input type="hidden" name="action" value="whitelist_alter" />
                <input type="hidden" name="auth" value="<?= $LoggedUser['AuthKey'] ?>" />
                <tr class="Table-row row<?= $Row ?>">
                    <td class="Table-cell">
                        <input type="hidden" name="id" value="<?= $ID ?>" />
                        <input class="Input" type="text" size="60" name="client" value="<?= $Client ?>" />
                    </td>
                    <td class="Table-cell">
                        <input class="Input" type="text" size="10" name="peer_id" value="<?= $Peer_ID ?>" />
                    </td>
                    <td class="Table-cell">
                        <input class="Button" type="submit" name="submit" value="Edit" />
                        <input class="Button" type="submit" name="submit" value="Delete" />
                    </td>
                </tr>
            </form>
        <? } ?>

    </table>
</div>
<? View::show_footer(); ?>