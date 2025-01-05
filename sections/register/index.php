<?

/*
if (isset($LoggedUser)) {

    //Silly user, what are you doing here!
    header('Location: index.php');
    die();
}
*/

use Gazelle\Manager\ActionTrigger;

include(CONFIG['SERVER_ROOT'] . '/classes/validate.class.php');
$Val = new VALIDATE;

if (!empty($_REQUEST['confirm'])) {
    // Confirm registration
    $DB->query("
		SELECT ID
		FROM users_main
		WHERE torrent_pass = '" . db_string($_REQUEST['confirm']) . "'
			AND Enabled = '0'");
    list($UserID) = $DB->next_record();

    if ($UserID) {
        $DB->query("
			UPDATE users_main
			SET Enabled = '1'
			WHERE ID = '$UserID'");
        $Cache->increment('stats_user_count');
        include('step2.php');
        die();
    }

    // Confirm registration
    $DB->query("
		SELECT ID 
		FROM users_main
		WHERE torrent_pass = '" . db_string($_REQUEST['confirm']) . "'
			AND Enabled = '1'");
    list($UserID) = $DB->next_record();

    if ($UserID) {
        include('step3.php');
        $Cache->delete_value("user_info_$UserID");
    }
} elseif (open_registration($_REQUEST['email']) || !empty($_REQUEST['invite'])) {
    $Val->SetFields('username', true, 'regex', t('server.register.you_did_not_enter_a_valid_username'), array('regex' => USERNAME_REGEX));
    $Val->SetFields('email', true, 'email', t('server.register.you_did_not_enter_a_valid_email_address'));
    $Val->SetFields('password', true, 'regex', t('server.register.a_strong_password_is_8_characters_or_longer'), array('regex' => '/(?=^.{8,}$)(?=.*[^a-zA-Z])(?=.*[A-Z])(?=.*[a-z]).*$|.{20,}/'));
    $Val->SetFields('confirm_password', true, 'compare', t('server.register.your_passwords_do_not_match'), array('comparefield' => 'password'));
    $Val->SetFields('readrules', true, 'checkbox', t('server.register.you_did_not_select_rules'));
    $Val->SetFields('readwiki', true, 'checkbox', t('server.register.you_did_not_select_wiki'));
    $Val->SetFields('agereq', true, 'checkbox', t('server.register.you_did_not_select_age'));
    //$Val->SetFields('captcha', true, 'string', t('server.register.you_did_not_enter_a_captcha_code'), array('minlength' => 6, 'maxlength' => 6));

    if (!empty($_POST['submit'])) {
        // User has submitted registration form
        $InviteID = 0;
        $Err = $Val->ValidateForm($_REQUEST);
        /*
        if (!$Err && strtolower($_SESSION['captcha']) != strtolower($_REQUEST['captcha'])) {
            $Err = 'You did not enter the correct captcha code.';
        }
        */
        // 限制指定邮箱注册
        if (LIMIT_REGISTER_VERSION) {
            if (open_registration()) {
                $NewValue = $Cache->increment(LIMIT_REGISTER_VERSION);
                if (LIMIT_REGISTER_COUNT < $NewValue) {
                    $Err = t('server.register.register_closed');
                }
            } else if (!empty($_REQUEST['invite'])) {
                $NewValue = $Cache->increment(LIMIT_REGISTER_VERSION);
                if (LIMIT_REGISTER_COUNT < $NewValue) {
                    $Err = t('server.register.register_closed');
                }
            }
        }
        if (!$Err) {
            $NotAllowedEmails = CONFIG['NOT_ALLOWED_REGISTRATION_EMAIL'];
            $EmailBox = explode('@', $_REQUEST['email']);
            if (in_array($EmailBox[1], $NotAllowedEmails)) {
                $Err = t('server.pub.not_allowed_email');
            }
            // Don't allow a username of "0" or "1" due to PHP's type juggling
            if (trim($_POST['username']) == '0' || trim($_POST['username']) == '1') {
                $Err = t('server.register.you_cannot_have_a_username_of_0_or_1');
            }



            $DB->query("
				SELECT COUNT(ID)
				FROM users_main
				WHERE Username LIKE '" . db_string(trim($_POST['username'])) . "'");
            list($UserCount) = $DB->next_record();

            if ($UserCount) {
                $Err = t('server.register.someone_registered_with_that_username');
                $_REQUEST['username'] = '';
            }
            $DB->query("
                SELECT COUNT(ID)
                FROM users_main
                WHERE Email = '" . db_string(trim($_POST['email'])) . "'");
            list($UserCount) = $DB->next_record();
            if ($UserCount) {
                $Err = t('server.register.someone_registered_with_that_email');
                $_REQUEST['email'] = '';
            } else if ($_REQUEST['invite']) {
                $DB->query("
					SELECT InviterID, Email, Reason, InviteID
					FROM invites
					WHERE InviteKey = '" . db_string($_REQUEST['invite']) . "'");
                if (!$DB->has_results()) {
                    $Err = t('server.register.invite_does_not_exist');
                    $InviterID = 0;
                } else {
                    list($InviterID, $InviteEmail, $InviteReason, $InviteID) = $DB->next_record(MYSQLI_NUM, false);
                }
                if ($_REQUEST['email'] != $InviteEmail) {
                    error_log("Mismatch invite email, request email: " . $_REQUEST['email'] . " invite email: " . $InviteEmail);
                    $Err = t('server.register.invite_email_mismatch');
                }
            } else {
                $InviterID = 0;
                $InviteEmail = $_REQUEST['email'];
                $InviteReason = '';
            }
        }

        if (!$Err) {
            $torrent_pass = Users::make_secret();

            // Previously SELECT COUNT(ID) FROM users_main, which is a lot slower.
            $DB->query("
				SELECT ID
				FROM users_main
				LIMIT 1");
            $UserCount = $DB->record_count();
            if ($UserCount == 0) {
                $NewInstall = true;
                $Class = CONFIG['USER_CLASS']['SYSOP'];
                $Enabled = '1';
            } else {
                $NewInstall = false;
                $Class = CONFIG['USER_CLASS']['USER'];
                $Enabled = '0';
            }

            $IPcc = Tools::geoip($_SERVER['REMOTE_ADDR']);

            $DB->query("
				INSERT INTO users_main
					(Username, Email, PassHash, torrent_pass, IP, PermissionID, Enabled, Invites, Uploaded, ipcc, FLTokens, LastLogin, LastAccess, Title)
				VALUES
					('" . db_string(trim($_POST['username'])) . "', '" . db_string($_POST['email']) . "', '" . db_string(Users::make_password_hash($_POST['password'])) . "', '" . db_string($torrent_pass) . "', '" . db_string($_SERVER['REMOTE_ADDR']) . "', '$Class', '$Enabled', '" . CONFIG['STARTING_INVITES'] . "', '" . CONFIG['STARTING_UPLOAD'] . "', '$IPcc', '0', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '')");

            $UserID = $DB->inserted_id();

            // User created, delete invite. If things break after this point, then it's better to have a broken account to fix than a 'free' invite floating around that can be reused
            if ($InviteID != 0) {
                $DB->query("
				DELETE FROM invites_typed
				WHERE ID = '$InviteID'");
            }
            $DB->query("
				DELETE FROM invites
				WHERE InviteKey = '" . db_string($_REQUEST['invite']) . "'");

            $SiteName = CONFIG['SITE_NAME'];
            Misc::send_pm_with_tpl($UserID, 'welcome_new_users', ['SiteName' => $SiteName, 'TGGroup' => CONFIG['TG_GROUP'], 'UserName' => trim($_POST['username'])]);
            $DB->query("
				SELECT ID
				FROM stylesheets
				WHERE `Default` = '1'");
            list($StyleID) = $DB->next_record();
            $AuthKey = Users::make_secret();


            if ($InviteReason != "") {
                $InviterUserInfo = Users::user_info($InviterID);
                $InviterUserName = $InviterUserInfo['Username'];
                $InviteReason = ", Reason: $InviteReason";
                $InviteReason = db_string(sqltime() . " - Invited by $InviterUserName$InviteReason");
            }
            $DB->query("
				INSERT INTO users_info
					(UserID, StyleID, AuthKey, Inviter, JoinDate, AdminComment, Info, Avatar, SiteOptions, Warned, SupportFor, TorrentGrouping, ResetKey, ResetExpires, RatioWatchEnds, BanDate, InfoTitle)
				VALUES
					('$UserID', '$StyleID', '" . db_string($AuthKey) . "', '$InviterID', '" . sqltime() . "', '$InviteReason', '', '', '', '0000-00-00 00:00:00', '', '0', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '')");

            $DB->query("
				INSERT INTO users_history_ips
					(UserID, IP, StartTime)
				VALUES
					('$UserID', '" . db_string($_SERVER['REMOTE_ADDR']) . "', '" . sqltime() . "')");
            $DB->query("
				INSERT INTO users_notifications_settings
					(UserID)
				VALUES
					('$UserID')");


            $DB->query("
				INSERT INTO users_history_emails
					(UserID, Email, Time, IP)
				VALUES
					('$UserID', '" . db_string($_REQUEST['email']) . "', '0000-00-00 00:00:00', '" . db_string($_SERVER['REMOTE_ADDR']) . "')");

            if ($_REQUEST['email'] != $InviteEmail) {
                $DB->query("
					INSERT INTO users_history_emails
						(UserID, Email, Time, IP)
					VALUES
						('$UserID', '" . db_string($InviteEmail) . "', '" . sqltime() . "', '" . db_string($_SERVER['REMOTE_ADDR']) . "')");
            }


            // Manage invite trees, delete invite

            if (!empty($InviterID)) {
                $DB->query("
					SELECT TreePosition, TreeID, TreeLevel
					FROM invite_tree
					WHERE UserID = '$InviterID'");
                list($InviterTreePosition, $TreeID, $TreeLevel) = $DB->next_record();

                // If the inviter doesn't have an invite tree
                // Note: This should never happen unless you've transferred from another database, like What.CD did
                if (!$DB->has_results()) {
                    $DB->query("
						SELECT MAX(TreeID)
						FROM invite_tree");
                    list($TreeID) = $DB->next_record();
                    $TreeID += 1;

                    $DB->query("
						INSERT INTO invite_tree
							(UserID, InviterID, TreePosition, TreeID, TreeLevel)
						VALUES ('$InviterID', '0', '1', '$TreeID', '1')");

                    $TreePosition = 2;
                    $TreeLevel = 2;
                } else {
                    $DB->query("
						SELECT TreePosition
						FROM invite_tree
						WHERE TreePosition > '$InviterTreePosition'
							AND TreeLevel <= '$TreeLevel'
							AND TreeID = '$TreeID'
						ORDER BY TreePosition
						LIMIT 1");
                    list($TreePosition) = $DB->next_record();

                    if ($TreePosition) {
                        $DB->query("
							UPDATE invite_tree
							SET TreePosition = TreePosition + 1
							WHERE TreeID = '$TreeID'
								AND TreePosition >= '$TreePosition'");
                    } else {
                        $DB->query("
							SELECT TreePosition + 1
							FROM invite_tree
							WHERE TreeID = '$TreeID'
							ORDER BY TreePosition DESC
							LIMIT 1");
                        list($TreePosition) = $DB->next_record();
                    }
                    $TreeLevel++;

                    // Create invite tree record
                    $DB->query("
						INSERT INTO invite_tree
							(UserID, InviterID, TreePosition, TreeID, TreeLevel)
						VALUES
							('$UserID', '$InviterID', '$TreePosition', '$TreeID', '$TreeLevel')");
                }
                $trigger = new ActionTrigger;
                $trigger->triggerInviteeRegister($InviterID, $UserID);
            } else { // No inviter (open registration)
                $DB->query("
					SELECT MAX(TreeID)
					FROM invite_tree");
                list($TreeID) = $DB->next_record();
                $TreeID++;
                $InviterID = 0;
                $TreePosition = 1;
                $TreeLevel = 1;
                // Create invite tree record
                $DB->query("
						INSERT INTO invite_tree
							(UserID, InviterID, TreePosition, TreeID, TreeLevel)
						VALUES
							('$UserID', '$InviterID', '$TreePosition', '$TreeID', '$TreeLevel')");
            }

            if (CONFIG['CLOSE_LOGIN']) {
                $LoginKey = Users::make_secret();
                $DB->query("insert into login_link (LoginKey, UserID, Username) values ('" . db_string($LoginKey) . "', '$UserID', '" . db_string(trim($_POST['username'])) . "')");
                Misc::send_email_with_tpl($_REQUEST['email'], 'new_registration_close_login', [
                    'LoginKey' => $LoginKey,
                    'TorrentKeyRight' => substr($torrent_pass, -8),
                    'Username' => $_REQUEST['username'],
                    'TorrentKey' => $torrent_pass,
                    'SITE_NAME' => CONFIG['SITE_NAME'],
                    'SITE_URL' => CONFIG['SITE_URL'],
                ], 'text/html');
            } else {
                Misc::send_email_with_tpl($_REQUEST['email'], 'new_registration', [
                    'Username' => $_REQUEST['username'],
                    'TorrentKey' => $torrent_pass,
                    'SITE_NAME' => CONFIG['SITE_NAME'],
                    'SITE_URL' => CONFIG['SITE_URL'],
                ], 'text/html');
            }

            Tracker::update_tracker('add_user', array('id' => $UserID, 'passkey' => $torrent_pass));
            $Sent = 1;
        }
    } elseif ($_GET['invite']) {
        // If they haven't submitted the form, check to see if their invite is good
        $DB->query("
			SELECT InviteKey
			FROM invites
			WHERE InviteKey = '" . db_string($_GET['invite']) . "'");
        if (!$DB->has_results()) {
            error('Invite not found!');
        }
    }

    include('step1.php');
} elseif (!open_registration($_REQUEST['email'])) {
    if (isset($_GET['welcome'])) {
        include('code.php');
    } else {
        include('closed.php');
    }
}
