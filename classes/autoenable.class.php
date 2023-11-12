<?

class AutoEnable {
    // Constants for database values
    const APPROVED = 1;
    const DENIED = 2;
    const DISCARDED = 3;

    // Cache key to store the number of enable requests
    const CACHE_KEY_NAME = 'num_enable_requests';


    /**
     * Handle a new enable request
     *
     * @param string $Username The user's username
     * @param string $Email The user's email address
     * @return string The output
     */
    public static function new_request($Username, $Email) {
        if (empty($Username)) {
            header("Location: login.php");
            die();
        }

        // Get the user's ID
        G::$DB->query("
				SELECT um.ID
				FROM users_main AS um
				JOIN users_info ui ON ui.UserID = um.ID
				WHERE um.Username = '$Username'
				  AND um.Enabled = '2'");

        if (G::$DB->has_results()) {
            // Make sure the user can make another request
            list($UserID) = G::$DB->next_record();
            G::$DB->query("
			SELECT 1 FROM users_enable_requests
			WHERE UserID = '$UserID'
			  AND (
					(
					  Timestamp > NOW() - INTERVAL 1 WEEK
						AND HandledTimestamp IS NULL
					)
					OR
					(
					  Timestamp > NOW() - INTERVAL 2 MONTH
						AND
						  (Outcome = '" . self::DENIED . "'
							 OR Outcome = '" . self::DISCARDED . "')
					)
				  )");
        }

        $IP = $_SERVER['REMOTE_ADDR'];

        if (G::$DB->has_results() || !isset($UserID)) {
            // User already has/had a pending activation request or username is invalid
            $Output = t(
                'server.login.re_enable_request_rejected',
                [
                    'Values' =>
                    [
                        CONFIG['BOT_DISABLED_CHAN'],
                        CONFIG['BOT_SERVER'],
                    ]
                ]
            );
            if (isset($UserID)) {
                Tools::update_user_notes($UserID, sqltime() . t('server.login.enable_request_rejected_from_ip') . "$IP\n\n");
            }
        } else {
            // New disable activation request
            $UserAgent = db_string($_SERVER['HTTP_USER_AGENT']);

            G::$DB->query("
				INSERT INTO users_enable_requests
				(UserID, Email, IP, UserAgent, Timestamp)
				VALUES ('$UserID', '$Email', '$IP', '$UserAgent', '" . sqltime() . "')");

            // Cache the number of requests for the modbar
            G::$Cache->increment_value(self::CACHE_KEY_NAME);
            setcookie('username', '', time() - 60 * 60, '/', '', false);
            $Output = t('server.login.re_enable_request_received');
            Tools::update_user_notes($UserID, sqltime() . t('server.login.enable_request_received_from_ip', ['Values' => [G::$DB->inserted_id()]]) . "$IP\n\n");
        }

        return $Output;
    }

    /*
     * Handle requests
     *
     * @param int|int[] $IDs An array of IDs, or a single ID
     * @param int $Status The status to mark the requests as
     * @param string $Comment The staff member comment
     */
    public static function handle_requests($IDs, $Status, $Comment) {
        if ($Status != self::APPROVED && $Status != self::DENIED && $Status != self::DISCARDED) {
            error(404);
        }

        $UserInfo = array();
        $IDs = (!is_array($IDs)) ? [$IDs] : $IDs;

        if (count($IDs) == 0) {
            error(404);
        }

        foreach ($IDs as $ID) {
            if (!is_number($ID)) {
                error(404);
            }
        }

        G::$DB->query("SELECT Email, ID, UserID
				FROM users_enable_requests
				WHERE ID IN (" . implode(',', $IDs) . ")
					AND Outcome IS NULL");
        $Results = G::$DB->to_array(false, MYSQLI_NUM);

        if ($Status != self::DISCARDED) {
            // Prepare email
            $TPL = '';
            $Params = [];
            if ($Status == self::APPROVED) {
                $TPL = 'enable_request_accept';
                $Params['SITE_URL'] =  site_url(false);
            } else {
                $TPL = 'enable_request_denied';
                $Params['TG_DISABLE_CHANNEL'] =  CONFIG['TG_DISBALE_CHANNEL'];
            }

            $Params['SITE_NAME'] = CONFIG['SITE_NAME'];

            foreach ($Results as $Result) {
                list($Email, $ID, $UserID) = $Result;
                $UserInfo[] = array($ID, $UserID);

                if ($Status == self::APPROVED) {
                    // Generate token
                    $Token = db_string(Users::make_secret());
                    G::$DB->query("
						UPDATE users_enable_requests
						SET Token = '$Token'
						WHERE ID = '$ID'");
                    $Params['TOKEN'] = $Token;
                }

                Misc::send_email_with_tpl($Email, $TPL, $Params);
            }
        } else {
            foreach ($Results as $Result) {
                list(, $ID, $UserID) = $Result;
                $UserInfo[] = array($ID, $UserID);
            }
        }

        // User notes stuff
        G::$DB->query("
			SELECT Username
			FROM users_main
			WHERE ID = '" . G::$LoggedUser['ID'] . "'");
        list($StaffUser) = G::$DB->next_record();

        foreach ($UserInfo as $User) {
            list($ID, $UserID) = $User;
            $BaseComment = sqltime()
                . t('server.login.enable_request_id_by_user', ['Values' => [
                    "$ID " . strtolower(self::get_outcome_string($Status))
                ]])
                . '[user]' . $StaffUser . '[/user]';
            $BaseComment .= (!empty($Comment)) ? "\n" . t('server.login.reason') . ": $Comment\n\n" : "\n\n";
            Tools::update_user_notes($UserID, $BaseComment);
        }

        // Update database values and decrement cache
        G::$DB->query("
				UPDATE users_enable_requests
				SET HandledTimestamp = '" . sqltime() . "',
					CheckedBy = '" . G::$LoggedUser['ID'] . "',
					Outcome = '$Status'
				WHERE ID IN (" . implode(',', $IDs) . ")");
        G::$Cache->delete_value(self::CACHE_KEY_NAME);
    }

    /**
     * Unresolve a discarded request
     *
     * @param int $ID The request ID
     */
    public static function unresolve_request($ID) {
        $ID = (int) $ID;

        if (empty($ID)) {
            error(404);
        }

        G::$DB->query("
			SELECT UserID
			FROM users_enable_requests
			WHERE Outcome = '" . self::DISCARDED . "'
			  AND ID = '$ID'");

        if (!G::$DB->has_results()) {
            error(404);
        } else {
            list($UserID) = G::$DB->next_record();
        }

        G::$DB->query("
			SELECT Username
			FROM users_main
			WHERE ID = '" . G::$LoggedUser['ID'] . "'");
        list($StaffUser) = G::$DB->next_record();

        Tools::update_user_notes($UserID, sqltime() . t('server.login.enable_request_id_unresolved_by', ['Values' => [$ID]]) . "[user]" . $StaffUser . '[/user]' . "\n\n");
        G::$DB->query("
			UPDATE users_enable_requests
			SET Outcome = NULL, HandledTimestamp = NULL, CheckedBy = NULL
			WHERE ID = '$ID'");
        G::$Cache->increment_value(self::CACHE_KEY_NAME);
    }

    /**
     * Get the corresponding outcome string for a numerical value
     *
     * @param int $Outcome The outcome integer
     * @return string The formatted output string
     */
    public static function get_outcome_string($Outcome) {
        if ($Outcome == self::APPROVED) {
            $String = t('server.login.outcome_approved');
        } else if ($Outcome == self::DENIED) {
            $String = t('server.login.outcome_rejected');
        } else if ($Outcome == self::DISCARDED) {
            $String = t('server.login.outcome_discarded');
        } else {
            $String = "---";
        }

        return $String;
    }

    /**
     * Handle a user's request to enable an account
     *
     * @param string $Token The token
     * @return string The error output, or an empty string
     */
    public static function handle_token($Token) {
        $Token = db_string($Token);
        G::$DB->query("
			SELECT UserID, HandledTimestamp
			FROM users_enable_requests
			WHERE Token = '$Token'");

        if (G::$DB->has_results()) {
            list($UserID, $Timestamp) = G::$DB->next_record();
            G::$DB->query("UPDATE users_enable_requests SET Token = NULL WHERE Token = '$Token'");
            if ($Timestamp < time_minus(3600 * 72)) {
                // Old request
                Tools::update_user_notes($UserID, sqltime() . t('server.login.tried_to_use_an_expired_token', ['Values' => [$_SERVER['REMOTE_ADDR']]]) . "\n\n");
                $Err = t('server.login.token_has_expired_please_visit', ['Values' => [
                    CONFIG['BOT_DISABLED_CHAN'],
                    CONFIG['BOT_SERVER']
                ]]);
            } else {
                // Good request, decrement cache value and enable account
                G::$Cache->decrement_value(AutoEnable::CACHE_KEY_NAME);
                G::$DB->query("UPDATE users_main SET Enabled = '1', can_leech = '1' WHERE ID = '$UserID'");
                G::$Cache->delete_value("user_info_$UserID");
                G::$DB->query("UPDATE users_info SET BanReason = '0' WHERE UserID = '$UserID'");
                G::$DB->query("SELECT torrent_pass FROM users_main WHERE ID='{$UserID}'");
                list($TorrentPass) = G::$DB->next_record();
                Tracker::update_tracker('add_user', array('id' => $UserID, 'passkey' => $TorrentPass));
                $Err = t('server.login.your_account_has_been_enabled');
            }
        } else {
            $Err = t('server.login.invalid_token');
        }

        return $Err;
    }

    /**
     * Build the search query, from the searchbox inputs
     *
     * @param int $UserID The user ID
     * @param string $IP The IP
     * @param string $SubmittedTimestamp The timestamp representing when the request was submitted
     * @param int $HandledUserID The ID of the user that handled the request
     * @param string $HandledTimestamp The timestamp representing when the request was handled
     * @param int $OutcomeSearch The outcome of the request
     * @param boolean $Checked Should checked requests be included?
     * @return array The WHERE conditions for the query
     */
    public static function build_search_query($Username, $IP, $SubmittedBetween, $SubmittedTimestamp1, $SubmittedTimestamp2, $HandledUsername, $HandledBetween, $HandledTimestamp1, $HandledTimestamp2, $OutcomeSearch, $Checked) {
        $Where = array();

        if (!empty($Username)) {
            $Where[] = "um1.Username = '$Username'";
        }

        if (!empty($IP)) {
            $Where[] = "uer.IP = '$IP'";
        }

        if (!empty($SubmittedTimestamp1)) {
            switch ($SubmittedBetween) {
                case 'on':
                    $Where[] = "DATE(uer.Timestamp) = DATE('$SubmittedTimestamp1')";
                    break;
                case 'before':
                    $Where[] = "DATE(uer.Timestamp) < DATE('$SubmittedTimestamp1')";
                    break;
                case 'after':
                    $Where[] = "DATE(uer.Timestamp) > DATE('$SubmittedTimestamp1')";
                    break;
                case 'between':
                    if (!empty($SubmittedTimestamp2)) {
                        $Where[] = "DATE(uer.Timestamp) BETWEEN DATE('$SubmittedTimestamp1') AND DATE('$SubmittedTimestamp2')";
                    }
                    break;
                default:
                    break;
            }
        }

        if (!empty($HandledTimestamp1)) {
            switch ($HandledBetween) {
                case 'on':
                    $Where[] = "DATE(uer.HandledTimestamp) = DATE('$HandledTimestamp1')";
                    break;
                case 'before':
                    $Where[] = "DATE(uer.HandledTimestamp) < DATE('$HandledTimestamp1')";
                    break;
                case 'after':
                    $Where[] = "DATE(uer.HandledTimestamp) > DATE('$HandledTimestamp1')";
                    break;
                case 'between':
                    if (!empty($HandledTimestamp2)) {
                        $Where[] = "DATE(uer.HandledTimestamp) BETWEEN DATE('$HandledTimestamp1') AND DATE('$HandledTimestamp2')";
                    }
                    break;
                default:
                    break;
            }
        }

        if (!empty($HandledUsername)) {
            $Where[] = "um2.Username = '$HandledUsername'";
        }

        if (!empty($OutcomeSearch)) {
            $Where[] = "uer.Outcome = '$OutcomeSearch'";
        }

        if ($Checked) {
            // This is to skip the if statement in enable_requests.php
            $Where[] = "(uer.Outcome IS NULL OR uer.Outcome IS NOT NULL)";
        }

        return $Where;
    }
}
