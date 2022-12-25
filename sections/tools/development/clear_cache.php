<?
if (!check_perms('users_mod') || !check_perms('admin_clear_cache')) {
    error(403);
}

View::show_header(t('server.tools.clear_a_cache_key'), '', 'PageToolClearCache');

//Make sure the form was sent
if (isset($_GET['cache'])) {
    if ($_GET['cache'] === 'users') {
        $DB->query("SELECT max(id) as count FROM users_main");
        list($Count) = $DB->next_record();

        for ($i = 1; $i <= $Count; $i++) {
            $Cache->delete_value('user_stats_' . $i);
            $Cache->delete_value('user_info_' . $i);
            $Cache->delete_value('user_info_heavy_' . $i);
        }
    } elseif ($_GET['cache'] === 'torrent_groups') {
        $DB->query("SELECT max(id) as count FROM torrents_group");
        list($Count) = $DB->next_record();
        for ($i = 1; $i <= $Count; $i++) {
            $Cache->delete_value('torrent_group_' . $i);
            $Cache->delete_value('groups_artists_' . $i);
            $Cache->delete_value('torrents_details_' . $i);
        }
    } elseif ($_GET['cache'] === 'torrent_checked') {
        $DB->query("SELECT max(id) as count FROM torrents");
        list($Count) = $DB->next_record();
        for ($i = 1; $i <= $Count; $i++) {
            $Cache->delete_value('torrent_checked_' . $i);
        }
    }
}
if (isset($_POST['global_flush'])) {
    authorize();
    G::$Cache->flush();
}
if (!empty($_GET['key'])) {
    if ($_GET['submit'] == 'Multi') {
        $Keys = array_map('trim', preg_split('/\s+/', $_GET['key']));
    } else {
        $Keys = [trim($_GET['key'])];
    }
}

$MultiKeyTooltip = t('server.tools.enter_cache_keys_delimited_by_any_amount_of_whitespace');
?>
<div class="LayoutPage">
    <div class="BodyHeader">
        <h2 class="BodyHeader-nav"><?= t('server.tools.clear_a_cache_key') ?></h2>
    </div>
    <div class="Box">
        <div class="Box-header"><?= t('server.tools.clear_common_cashes') ?></div>
        <div class="Box-body">
            <div>
                <a href="tools.php?action=clear_cache&cache=users"><?= t('server.tools.users') ?></a> (<?= t('server.tools.clears_out') ?> user_stats_*, user_info_*, and user_info_heavy_*)
            </div>
            <div>
                <a href="tools.php?action=clear_cache&cache=torrent_groups"><?= t('server.tools.torrent_groups') ?></a> (<?= t('server.tools.clears_out') ?> torrent_group_* and groups_artists_*)
            </div>
            <div>
                <a href="tools.php?action=clear_cache&cache=torrent_checked"><?= t('server.tools.torrent_checked') ?></a> (<?= t('server.tools.clears_out') ?> torrent_checked_*)
            </div>
            <div>
                <form class="delete_form" id="clear_all_cache" name="cache" action="" method="post">
                    <input type="hidden" name="action" value="clear_cache" />
                    <input type="hidden" name="auth" value="<?= $LoggedUser['AuthKey'] ?>" />
                    <input type="hidden" name="global_flush" value="1" />
                    <a href="javascript:{}" onclick="document.getElementById('clear_all_cache').submit();"><?= t('server.tools.all') ?></a>
                </form>
            </div>
        </div>
    </div>
    <form class="manage_form" name="cache" method="get" action="">
        <input type="hidden" name="action" value="clear_cache" />
        <table variant="header" class="Form-rowList" cellpadding="2" cellspacing="1" border="0" align="center">
            <tr class="Form-rowHeader">
                <td><?= t('server.tools.clear_a_cache_key') ?></td>
            </tr>
            <tr class="Form-row">
                <td class="Form-label">
                    <?= t('server.common.actions') ?>:
                </td>
                <td class="Form-inputs">
                    <select class="Input" name="type">
                        <option class="Select-option" value="view"><?= t('server.tools.view') ?></option>
                        <option class="Select-option" value="clear"><?= t('server.tools.clear') ?></option>
                    </select>
                </td>
            </tr>
            <tr class="Form-row">
                <td class="Form-label" data-tooltip="<?= $MultiKeyTooltip ?>">
                    <?= t('server.tools.multi_key') ?>:
                </td>
                <td class="Form-items">
                    <textarea class="Input inputtext" name="key" id="key"><?= (isset($_GET['key']) && $_GET['submit'] == 'Multi' ? display_str($_GET['key']) : '') ?></textarea>
                </td>
            </tr>
            <tr class="Form-row">
                <td>
                    <button class="Button" type="submit" name="submit" value="Multi"><?= t('server.common.submit') ?></button>
                </td>
            </tr>

            <?
            if (isset($Keys) && $_GET['type'] == 'clear') {
                foreach ($Keys as $Key) {
                    if (preg_match('/(.*?)(\d+)\.\.(\d+)$/', $Key, $Matches) && is_number($Matches[2]) && is_number($Matches[3])) {
                        for ($i = $Matches[2]; $i <= $Matches[3]; $i++) {
                            $Cache->delete_value($Matches[1] . $i);
                        }
                    } else {
                        $Cache->delete_value($Key);
                    }
                }
            ?>
                <tr class="Form-row">
                    <td>
                        <?
                        echo '<div class="save_message">Key(s) ' . implode(', ', array_map('display_str', $Keys)) . ' cleared!</div>';
                        ?>
                    </td>
                </tr>
            <?
            }
            ?>

        </table>
    </form>
    <?
    if (isset($Keys) && $_GET['type'] == 'view') {
    ?>
        <table class="Table" cellpadding="2" cellspacing="1" border="0" align="center" style="margin-top: 1em;">
            <tr class="Table-rowHeader">
                <td class="Table-cellHeader">
                    Key
                </td>
                <td class="Table-cellHeader">
                    Value
                </td>
            </tr>
            <?
            foreach ($Keys as $Key) {
            ?>
                <tr class="Tabel-row">
                    <td class="Table-cell"><?= display_str($Key) ?></td>
                    <td class="Tabel-cell">
                        <pre><?= print_r($Cache->get_value($Key)); ?></pre>
                    </td>
                </tr>
            <?  } ?>
        </table>
    <?
    }
    ?>
</div>
<?

View::show_footer();
