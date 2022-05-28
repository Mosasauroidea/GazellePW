<?
authorize();

if (!check_perms('admin_interviewer')) {
    error(403);
}
$P = db_array($_POST);
if ($_POST['submit'] == 'Agree') {
    $DB->query("select * from register_apply where `ID` = '" . $P['id'] . "' and `apply_status` = '1';");
    if ($DB->record_count() == 0) {
        $DB->query("
		INSERT INTO register_apply_log (UserID, ApplyID, ApplyStatus) 
		VALUES (" . $LoggedUser['ID'] . ", " . $P['id'] . ", 1)");

        $DB->query("UPDATE `register_apply` SET `apply_status` = '1', `note` = '" . base64_encode($P['note']) . "',  `ts` = `ts`,  `ts_mod` = now(), `id_mod` = '" . $LoggedUser['ID'] . "' WHERE `ID` = '" . $P['id'] . "';");

        $InviteKey = db_string(Users::make_secret());
        $SiteName = SITE_NAME;
        $SiteURL = site_url();
        $InviteExpires = time_plus(60 * 60 * 24 * 3); // 3 days
        $InviteReason = '';
        $Email = $P['email'];
        $DB->query("INSERT INTO invites (InviterID, InviteKey, Email, Expires, Reason) 
	VALUES ('412', '$InviteKey', '$Email', '$InviteExpires', '$InviteReason')");
        require(SERVER_ROOT . '/classes/templates.class.php');
        $Tpl = new \TEMPLATE;
        $Tpl->open(SERVER_ROOT . '/templates/apply_approved.tpl');
        $Tpl->set('SiteName', SITE_NAME);
        $Tpl->set('SiteURL', SITE_URL);
        $Tpl->set('InviteKey', $InviteKey);
        Misc::send_email($Email, '你有一封来自 ' . SITE_NAME . ' 的邀请函 | You have been invited to ' . SITE_NAME, $Tpl->get(), 'noreply');
    }
} elseif ($_POST['submit'] == 'Refuse') {
    $DB->query("SELECT `apply_status` FROM `register_apply` WHERE `ID` = '" . $P['id'] . "'");
    $sort0 = $DB->to_array(false, MYSQLI_NUM, false);
    if ($sort0[0][0] != 1) {
        $DB->query("
			INSERT INTO register_apply_log (UserID, ApplyID, ApplyStatus) 
			VALUES (" . $LoggedUser['ID'] . ", " . $P['id'] . ", 2)");
        $DB->query("UPDATE `register_apply` SET `apply_status` = '2', `note` = '" . base64_encode($P['note']) . "',  `ts` = `ts`,  `ts_mod` = now(), `id_mod` = '" . $LoggedUser['ID'] . "' WHERE `ID` = '" . $P['id'] . "';");
    }
} elseif ($_POST['submit'] == 'Pending') {
    $DB->query("SELECT `apply_status` FROM `register_apply` WHERE `ID` = '" . $P['id'] . "'");
    $sort0 = $DB->to_array(false, MYSQLI_NUM, false);
    if ($sort0[0][0] != 1) {
        $DB->query("
			INSERT INTO register_apply_log (UserID, ApplyID, ApplyStatus) 
			VALUES (" . $LoggedUser['ID'] . ", " . $P['id'] . ", 3)");
        $DB->query("UPDATE `register_apply` SET `apply_status` = '3', `note` = '" . base64_encode($P['note']) . "',  `ts` = `ts`,  `ts_mod` = now(), `id_mod` = '" . $LoggedUser['ID'] . "' WHERE `ID` = '" . $P['id'] . "';");
    }
} elseif ($_POST['submit'] == 'Add') {
    $DB->query("SELECT `apply_status` FROM `register_apply` WHERE `ID` = '" . $P['id'] . "'");
    $sort0 = $DB->to_array(false, MYSQLI_NUM, false);
    if ($sort0[0][0] != 1) {
        $DB->query("
			INSERT INTO register_apply_log (UserID, ApplyID, ApplyStatus) 
			VALUES (" . $LoggedUser['ID'] . ", " . $P['id'] . ", 4)");
        $DB->query("UPDATE `register_apply` SET `apply_status` = '4', `note` = '" . base64_encode($P['note']) . "', `waring` = '" . base64_encode($P['waring']) . "', `ts` = `ts`,  `ts_mod` = now(), `id_mod` = '" . $LoggedUser['ID'] . "' WHERE `ID` = '" . $P['id'] . "';");
    }
}

// Go back
$url = "tools.php?action=apply_list";
if (isset($P['apply_status'])) {
    $url = $url . "&status=" . $P['apply_status'];
}
header("Location: $url");
