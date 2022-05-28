<?php
if (!check_perms('users_mod')) {
    error(403);
}

if (isset($_REQUEST['addtokens'])) {
    authorize();
    $Tokens = $_REQUEST['numtokens'];

    if (!is_number($Tokens) || ($Tokens < 0)) {
        error('Please enter a valid number of tokens.');
    }
    $sql = "
		UPDATE users_main
		SET FLTokens = FLTokens + $Tokens
		WHERE Enabled = '1'";
    if (!isset($_REQUEST['leechdisabled'])) {
        $sql .= "
			AND can_leech = 1";
    }
    $DB->query($sql);
    $sql = "
		SELECT ID
		FROM users_main
		WHERE Enabled = '1'";
    if (!isset($_REQUEST['leechdisabled'])) {
        $sql .= "
			AND can_leech = 1";
    }
    $DB->query($sql);
    while (list($UserID) = $DB->next_record()) {
        $Cache->delete_value("user_info_heavy_$UserID");
    }
    $message = '<strong>' . number_format($Tokens) . 'freeleech tokens added to all enabled users' . (!isset($_REQUEST['leechdisabled']) ? ' with enabled leeching privs' : '') . '.</strong><br /><br />';
} elseif (isset($_REQUEST['cleartokens'])) {
    authorize();
    $Tokens = $_REQUEST['numtokens'];

    if (!is_number($Tokens) || ($Tokens < 0)) {
        error('Please enter a valid number of tokens.');
    }

    if (isset($_REQUEST['onlydrop'])) {
        $Where = "WHERE FLTokens > $Tokens";
    } elseif (!isset($_REQUEST['leechdisabled'])) {
        $Where = "WHERE (Enabled = '1' AND can_leech = 1) OR FLTokens > $Tokens";
    } else {
        $Where = "WHERE Enabled = '1' OR FLTokens > $Tokens";
    }
    $DB->query("
		SELECT ID
		FROM users_main
		$Where");
    $Users = $DB->to_array();
    $DB->query("
		UPDATE users_main
		SET FLTokens = $Tokens
		$Where");

    foreach ($Users as $UserID) {
        list($UserID) = $UserID;
        $Cache->delete_value("user_info_heavy_$UserID");
    }

    $where = '';
}


View::show_header(Lang::get('tools', 'add_tokens_sitewide'));

?>
<div class="BodyHeader">
    <h2 class="BodyHeader-nav"><?= Lang::get('tools', 'add_fl_tokens_to_all_enabled_users') ?></h2>
    <div class="BodyNavLinks">
        <a href="tools.php?action=tokens&amp;showabusers=1" class="brackets"><?= Lang::get('tools', 'show_abusers') ?></a>
    </div>
</div>
<div class="BoxBody" style="margin-left: auto; margin-right: auto; text-align: center; max-width: 40%;">
    <?= $message ?>
    <form class="add_form" name="fltokens" action="" method="post">
        <input type="hidden" name="action" value="tokens" />
        <input type="hidden" name="auth" value="<?= $LoggedUser['AuthKey'] ?>" />
        <?= Lang::get('tools', 'tokens_to_add') ?>: <input class="Input" type="text" name="numtokens" size="5" style="text-align: right;" value="0" /><br /><br />
        <label for="leechdisabled"><?= Lang::get('tools', 'grant_tokens_to_leech_disabled_users') ?>: </label><input type="checkbox" id="leechdisabled" name="leechdisabled" value="1" /><br /><br />
        <input class="Button" type="submit" name="addtokens" value="Add tokens" />
    </form>
</div>
<br />
<div class="BoxBody" style="margin-left: auto; margin-right: auto; text-align: center; max-width: 40%;">
    <?= $message ?>
    <form class="manage_form" name="fltokens" action="" method="post">
        <input type="hidden" name="action" value="tokens" />
        <input type="hidden" name="auth" value="<?= $LoggedUser['AuthKey'] ?>" />
        <?= Lang::get('tools', 'tokens_to_set') ?>: <input class="Input" type="text" name="numtokens" size="5" style="text-align: right;" value="0" /><br /><br />
        <span id="droptokens"><label for="onlydrop"><?= Lang::get('tools', 'only_affect_users_with_at_least_this_many_tokens') ?>: </label><input type="checkbox" id="onlydrop" name="onlydrop" value="1" onchange="$('#disabled').gtoggle(); return true;" /></span><br />
        <span id="disabled"><label for="leechdisabled"><?= Lang::get('tools', 'also_add_tokens_to_leech_disabled_users') ?>: </label><input type="checkbox" id="leechdisabled" name="leechdisabled" value="1" onchange="$('#droptokens').gtoggle(); return true;" /></span><br /><br />
        <input class="Button" type="submit" name="cleartokens" value="Set token total" />
    </form>
</div>
<?

View::show_footer()
?>