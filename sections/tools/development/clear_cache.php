<?
if (!check_perms('users_mod') || !check_perms('admin_clear_cache')) {
    error(403);
}

View::show_header(Lang::get('tools.clear_a_cache_key'), '', 'PageToolClearCache');

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
        echo "<div class='save_message'>{$Count} " . Lang::get('tools.users_caches_cleared') . "</div>";
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
if (!empty($_GET['key'])) {
    if ($_GET['submit'] == 'Multi') {
        $Keys = array_map('trim', preg_split('/\s+/', $_GET['key']));
    } else {
        $Keys = [trim($_GET['key'])];
    }
}
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
    echo '<div class="save_message">Key(s) ' . implode(', ', array_map('display_str', $Keys)) . ' cleared!</div>';
}
$MultiKeyTooltip = Lang::get('tools.enter_cache_keys_delimited_by_any_amount_of_whitespace');
?>
<div class="BodyHeader">
    <h2 class="BodyHeader-nav"><?= Lang::get('tools.clear_a_cache_key') ?></h2>
</div>
<table class="layout" cellpadding="2" cellspacing="1" border="0" align="center">
    <tr>
        <td style="width:15%; text-align:right;"><?= Lang::get('tools.key') ?>:</td>
        <td>
            <form class="manage_form" name="cache" method="get" action="">
                <input type="hidden" name="action" value="clear_cache" />
                <select class="Input" name="type">
                    <option class="Select-option" value="view"><?= Lang::get('tools.view') ?></option>
                    <option class="Select-option" value="clear"><?= Lang::get('tools.clear') ?></option>
                </select>
                <input class="Input" type="text" name="key" id="key" value="<?= (isset($_GET['key']) && $_GET['submit'] != 'Multi' ? display_str($_GET['key']) : '') ?>" />
                <input class="Button" type="submit" name="submit" value="Single" />
            </form>
        </td>
    </tr>
    <tr data-tooltip="<?= $MultiKeyTooltip ?>">
        <td style="text-align:right;"><?= Lang::get('tools.multi_key') ?>:</td>
        <td>
            <form class="manage_form" name="cache" method="get" action="">
                <input type="hidden" name="action" value="clear_cache" />
                <select class="Input" name="type">
                    <option class="Select-option" value="view"><?= Lang::get('tools.view') ?></option>
                    <option class="Select-option" value="clear"><?= Lang::get('tools.clear') ?></option>
                </select>
                <textarea class="Input inputtext" name="key" id="key"><?= (isset($_GET['key']) && $_GET['submit'] == 'Multi' ? display_str($_GET['key']) : '') ?></textarea>
                <input class="Button" type="submit" name="submit" value="Multi" />
            </form>
        </td>
    </tr>
    <tr>
        <td rowspan="3" style="text-align:right;"><?= Lang::get('tools.clear_common_cashes') ?>:</td>
        <td><a href="tools.php?action=clear_cache&cache=users"><?= Lang::get('tools.users') ?></a> (<?= Lang::get('tools.clears_out') ?> user_stats_*, user_info_*, and user_info_heavy_*)</td>
    </tr>
    <tr>
        <td><a href="tools.php?action=clear_cache&cache=torrent_groups"><?= Lang::get('tools.torrent_groups') ?></a> (<?= Lang::get('tools.clears_out') ?> torrent_group_* and groups_artists_*)</td>
    </tr>
    <tr>
        <td><a href="tools.php?action=clear_cache&cache=torrent_checked"><?= Lang::get('tools.torrent_checked') ?></a> (<?= Lang::get('tools.clears_out') ?> torrent_checked_*)</td>
    </tr>
</table>
<?
if (isset($Keys) && $_GET['type'] == 'view') {
?>
    <table class="layout" cellpadding="2" cellspacing="1" border="0" align="center" style="margin-top: 1em;">
        <?
        foreach ($Keys as $Key) {
        ?>
            <tr>
                <td><?= display_str($Key) ?></td>
                <td>
                    <pre><? var_dump($Cache->get_value($Key)); ?></pre>
                </td>
            </tr>
        <?  } ?>
    </table>
<?
}

View::show_footer();
