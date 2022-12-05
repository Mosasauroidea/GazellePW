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
<div class="LayoutBody">
    <div class="BodyHeader">
        <h2 class="BodyHeader-nav"><?= t('server.tools.client_whitelist') ?></h2>
    </div>
    <form class="add_form" name="clients" action="" method="post">
        <input type="hidden" name="action" value="whitelist_alter" />
        <input type="hidden" name="auth" value="<?= $LoggedUser['AuthKey'] ?>" />
        <table class="Form-rowList" variant="header">
            <tr class="Form-rowHeader">
                <td><?= t('server.tools.add_client') ?></td>
            </tr>
            <tr class="Form-row">
                <td class="Form-label">
                    <?= t('server.tools.client') ?>
                </td>
                <td class="Form-inputs">
                    <input class="Input" type="text" size="60" name="client" />
                </td>
            </tr>
            <tr class="Form-row">
                <td class="Form-label">
                    <?= t('server.tools.peer_id') ?>
                </td>
                <td class="Form-inputs">
                    <input class="Input" type="text" size="10" name="peer_id" />
                </td>
            </tr>
            <tr class="Form-row">
                <td>
                    <button class="Button" type="submit" value="Create"><?= t('server.common.new') ?></button>
                </td>
            </tr>
        </table>
    </form>
    <? if ($DB->record_count() != 0) {
    ?>
        <table class="Table">
            <tr class="Table-rowHeader">
                <td class="Table-cell"><?= t('server.tools.client') ?></td>
                <td class="Table-cell"><?= t('server.tools.peer_id') ?></td>
                <td class="Table-cell"><?= t('server.common.actions') ?></td>
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
                            <button class="Button" type="submit" name="submit" value="Edit"><?= t('server.common.edit') ?></button>
                            <button class="Button" type="submit" name="submit" value="Delete"><?= t('server.common.delete') ?> </button>
                        </td>
                    </tr>
                </form>
            <? } ?>
        </table>
    <?
    } ?>
</div>
<? View::show_footer(); ?>