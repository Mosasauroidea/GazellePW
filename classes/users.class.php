<?

use Gazelle\Manager\Donation;

class Users {
    /**
     * Get $Classes (list of classes keyed by ID) and $ClassLevels
     *      (list of classes keyed by level)
     * @return array ($Classes, $ClassLevels)
     */
    public static function get_classes() {
        global $Debug;
        // Get permissions
        list($Classes, $ClassLevels) = G::$Cache->get_value('classes');
        if (!$Classes || !$ClassLevels) {
            $QueryID = G::$DB->get_query_id();
            G::$DB->query('
				SELECT ID, Name, Level, Secondary
				FROM permissions
				ORDER BY Level');
            $Classes = G::$DB->to_array('ID');
            $ClassLevels = G::$DB->to_array('Level');
            G::$DB->set_query_id($QueryID);
            G::$Cache->cache_value('classes', array($Classes, $ClassLevels), 0);
        }
        $Debug->set_flag('Loaded permissions');

        return array($Classes, $ClassLevels);
    }

    // TOOD by qwerty HNR cal size and time config
    public static function get_hnr_count($UserID) {
        $Count = G::$Cache->get_value('user_hnr_count_' . $UserID);
        if ($Count == null) {
            $Count = count(self::get_hnr_torrents($UserID));
            G::$Cache->cache_value('user_hnr_count_' . $UserID, $Count, 3600);
        }
        return $Count;
    }

    private static function get_hnr_torrents($UserID) {
        $HNR_INTERVAL = HNR_INTERVAL;
        $HNR_MIN_MIN_RATIO = HNR_MIN_MIN_RATIO;
        $HNR_MIN_SIZE_PERCENT = HNR_MIN_SIZE_PERCENT;
        $HNR_MIN_SEEEDING_TIME = HNR_MIN_SEEEDING_TIME;
        $SQL = "
        SELECT
            ud.TorrentID
        FROM users_downloads AS ud 
            LEFT JOIN users_torrents AS ut on ut.fid = ud.TorrentID and ut.uid = ud.UserID
            JOIN torrents AS t ON t.ID = ud.TorrentID
            LEFT JOIN torrents_hnr as th ON th.torrent_id = ud.TorrentID and th.user_id = ud.UserID
        WHERE ud.UserID = '$UserID'
            AND ut.real_downloaded > t.Size * $HNR_MIN_SIZE_PERCENT
            AND (ut.seedtime < $HNR_MIN_SEEEDING_TIME or ut.real_uploaded <= 0 or ut.real_uploaded / ut.real_downloaded < $HNR_MIN_MIN_RATIO)
            AND unix_timestamp(now()) - unix_timestamp(ud.Time) > $HNR_INTERVAL
            AND th.torrent_id is null
        ORDER BY ud.Time";

        G::$DB->query($SQL);
        return G::$DB->to_array(false, MYSQLI_NUM);
    }

    public static function eliminate_latest_hnr($UserID) {
        $TorrentList = self::get_hnr_torrents($UserID);
        if (count($TorrentList) <= 0) {
            return 2;
        }
        list($TorrentID) = $TorrentList[0];
        G::$DB->query("insert into torrents_hnr (user_id, torrent_id, time) VALUES ($UserID, $TorrentID, '" . sqltime() . "')");
        G::$Cache->delete_value('user_hnr_count_' . $UserID);
        return 1;
    }

    public static function user_stats($UserID, $refresh = false) {
        global $Cache, $DB;
        if ($refresh) {
            $Cache->delete_value('user_stats_' . $UserID);
        }
        $UserStats = $Cache->get_value('user_stats_' . $UserID);
        if (!is_array($UserStats)) {
            $DB->query("
                SELECT Uploaded AS BytesUploaded, Downloaded AS BytesDownloaded, BonusPoints, RequiredRatio
                FROM users_main
                WHERE ID = '$UserID'
            ");
            $UserStats = $DB->next_record(MYSQLI_ASSOC);
            $UserStats = array_merge($UserStats, [
                'BytesUploaded' => (int) $UserStats['BytesUploaded'],
                'BytesDownloaded' => (int) $UserStats['BytesDownloaded'],
                'BonusPoints' => (float) $UserStats['BonusPoints'],
                'RequiredRatio' => (float) $UserStats['RequiredRatio'],
            ]);
            $Cache->cache_value('user_stats_' . $UserID, $UserStats, 3600);
        }
        return $UserStats;
    }

    /**
     * Get user info, is used for the current user and usernames all over the site.
     *
     * @param $UserID int   The UserID to get info for
     * @return array with the following keys:
     *  int ID
     *  string  Username
     *  int PermissionID
     *  array   Paranoia - $Paranoia array sent to paranoia.class
     *  boolean Artist
     *  boolean Donor
     *  string  Warned - When their warning expires in international time format
     *  string  Avatar - URL
     *  boolean Enabled
     *  string  Title
     *  string  CatchupTime - When they last caught up on forums
     *  boolean Visible - If false, they don't show up on peer lists
     *  array ExtraClasses - Secondary classes.
     *  int EffectiveClass - the highest level of their main and secondary classes
     */
    public static function user_info($UserID) {
        global $Classes, $SSL;
        $UserInfo = G::$Cache->get_value("user_info_$UserID");
        // the !isset($UserInfo['Paranoia']) can be removed after a transition period
        if (empty($UserInfo) || empty($UserInfo['ID']) || !isset($UserInfo['Paranoia']) || empty($UserInfo['Class'])) {
            $OldQueryID = G::$DB->get_query_id();

            G::$DB->query("
				SELECT
					m.ID,
					m.Username,
					m.PermissionID,
					m.Paranoia,
					i.Artist,
					i.Donor,
					i.Found,
					i.Warned,
					i.Avatar,
					i.Lang,
					m.Enabled,
					m.Title,
					i.CatchupTime,
					m.Visible,
					la.Type AS LockedAccount,
					GROUP_CONCAT(ul.PermissionID SEPARATOR ',') AS Levels
				FROM users_main AS m
					INNER JOIN users_info AS i ON i.UserID = m.ID
					LEFT JOIN locked_accounts AS la ON la.UserID = m.ID
					LEFT JOIN users_levels AS ul ON ul.UserID = m.ID
				WHERE m.ID = '$UserID'
				GROUP BY m.ID");

            if (!G::$DB->has_results()) { // Deleted user, maybe?
                $UserInfo = array(
                    'ID' => $UserID,
                    'Username' => '',
                    'PermissionID' => 0,
                    'Paranoia' => array(),
                    'Artist' => false,
                    'Donor' => false,
                    'Found' => false,
                    'Warned' => '0000-00-00 00:00:00',
                    'Avatar' => '',
                    'Lang' => Lang::DEFAULT_LANG,
                    'Enabled' => 0,
                    'Title' => '',
                    'CatchupTime' => 0,
                    'Visible' => '1',
                    'Levels' => '',
                    'Class' => 0
                );
            } else {
                $UserInfo = G::$DB->next_record(MYSQLI_ASSOC, false);
                $UserInfo['CatchupTime'] = strtotime($UserInfo['CatchupTime']);
                $UserInfo['Paranoia'] = unserialize_array($UserInfo['Paranoia']);
                if ($UserInfo['Paranoia'] === false) {
                    $UserInfo['Paranoia'] = array();
                }
                $UserInfo['Class'] = $Classes[$UserInfo['PermissionID']]['Level'];
            }

            if (empty($UserInfo['LockedAccount'])) {
                unset($UserInfo['LockedAccount']);
            }

            if (!empty($UserInfo['Levels'])) {
                $UserInfo['ExtraClasses'] = array_fill_keys(explode(',', $UserInfo['Levels']), 1);
            } else {
                $UserInfo['ExtraClasses'] = array();
            }
            unset($UserInfo['Levels']);
            $EffectiveClass = $UserInfo['Class'];
            foreach ($UserInfo['ExtraClasses'] as $Class => $Val) {
                $EffectiveClass = max($EffectiveClass, $Classes[$Class]['Level']);
            }
            $UserInfo['EffectiveClass'] = $EffectiveClass;

            G::$Cache->cache_value("user_info_$UserID", $UserInfo, 2592000);
            G::$DB->set_query_id($OldQueryID);
        }
        if (strtotime($UserInfo['Warned']) < time()) {
            $UserInfo['Warned'] = '0000-00-00 00:00:00';
            G::$Cache->cache_value("user_info_$UserID", $UserInfo, 2592000);
        }

        return $UserInfo;
    }

    /**
     * Gets the heavy user info
     * Only used for current user
     *
     * @param string $UserID The userid to get the information for
     * @return array fetched heavy info.
     *      Just read the goddamn code, I don't have time to comment this shit.
     */
    public static function user_heavy_info($UserID) {

        $HeavyInfo = G::$Cache->get_value("user_info_heavy_$UserID");
        if (empty($HeavyInfo)) {

            $QueryID = G::$DB->get_query_id();
            G::$DB->query("
				SELECT
					m.Invites,
					m.torrent_pass,
					m.IP,
					m.CustomPermissions,
					m.can_leech AS CanLeech,
					m.IRCKey,
					i.AuthKey,
					i.RatioWatchEnds,
					i.RatioWatchDownload,
					i.StyleID,
					i.Lang,
					i.StyleURL,
                    i.StyleTheme,
					i.DisableInvites,
					i.DisablePosting,
					i.DisableCheckAll,
					i.DisableCheckSelf,
					i.DisableUpload,
					i.DisablePoints,
					i.DisableWiki,
					i.DisableAvatar,
					i.DisablePM,
					i.DisableRequests,
					i.DisableForums,
					i.DisableIRC,
					i.DisableTagging," . "
					i.SiteOptions,
					i.DownloadAlt,
					i.LastReadNews,
					i.LastReadBlog,
					i.RestrictedForums,
					i.PermittedForums,
					i.RequestsAlerts,
					i.TGID,
					m.FLTokens,
					m.PermissionID,
                    i.SettingTorrentTitle,
                    i.JoinDate,
                    m.LastAccess
				FROM users_main AS m
					INNER JOIN users_info AS i ON i.UserID = m.ID
				WHERE m.ID = '$UserID'");
            $HeavyInfo = G::$DB->next_record(MYSQLI_ASSOC, array('CustomPermissions', 'SiteOptions', 'SettingTorrentTitle'));

            G::$DB->query("select count(ID) from tokens_typed where UserID=$UserID and Type='time'");
            list($TimedTokens) = G::$DB->next_record();
            $HeavyInfo['TimedTokens'] = $TimedTokens;
            G::$DB->query("select count(ID) from invites_typed where UserID=$UserID and Type='time' and Used=0");
            list($TimedInvites) = G::$DB->next_record();
            $HeavyInfo['TimedInvites'] = $TimedInvites;

            $HeavyInfo['CustomPermissions'] = unserialize_array($HeavyInfo['CustomPermissions']);

            if (!empty($HeavyInfo['RestrictedForums'])) {
                $RestrictedForums = array_map('trim', explode(',', $HeavyInfo['RestrictedForums']));
            } else {
                $RestrictedForums = array();
            }
            unset($HeavyInfo['RestrictedForums']);
            if (!empty($HeavyInfo['PermittedForums'])) {
                $PermittedForums = array_map('trim', explode(',', $HeavyInfo['PermittedForums']));
            } else {
                $PermittedForums = array();
            }
            unset($HeavyInfo['PermittedForums']);

            G::$DB->query("
				SELECT PermissionID
				FROM users_levels
				WHERE UserID = $UserID");
            $PermIDs = G::$DB->collect('PermissionID');
            foreach ($PermIDs as $PermID) {
                $Perms = Permissions::get_permissions($PermID);
                if (!empty($Perms['PermittedForums'])) {
                    $PermittedForums = array_merge($PermittedForums, array_map('trim', explode(',', $Perms['PermittedForums'])));
                }
            }
            $Perms = Permissions::get_permissions($HeavyInfo['PermissionID']);
            unset($HeavyInfo['PermissionID']);
            if (!empty($Perms['PermittedForums'])) {
                $PermittedForums = array_merge($PermittedForums, array_map('trim', explode(',', $Perms['PermittedForums'])));
            }

            if (!empty($PermittedForums) || !empty($RestrictedForums)) {
                $HeavyInfo['CustomForums'] = array();
                foreach ($RestrictedForums as $ForumID) {
                    $HeavyInfo['CustomForums'][$ForumID] = 0;
                }
                foreach ($PermittedForums as $ForumID) {
                    $HeavyInfo['CustomForums'][$ForumID] = 1;
                }
            } else {
                $HeavyInfo['CustomForums'] = null;
            }
            if (isset($HeavyInfo['CustomForums'][''])) {
                unset($HeavyInfo['CustomForums']['']);
            }

            $HeavyInfo['SiteOptions'] = unserialize_array($HeavyInfo['SiteOptions']);
            $HeavyInfo['SiteOptions'] = array_merge(static::default_site_options(), $HeavyInfo['SiteOptions']);
            $HeavyInfo = array_merge($HeavyInfo, $HeavyInfo['SiteOptions']);

            unset($HeavyInfo['SiteOptions']);

            $HeavyInfo['SettingTorrentTitle'] = $HeavyInfo['SettingTorrentTitle'] ? json_decode($HeavyInfo['SettingTorrentTitle'], true) : null;

            G::$DB->set_query_id($QueryID);

            G::$Cache->cache_value("user_info_heavy_$UserID", $HeavyInfo, 0);
        }
        return $HeavyInfo;
    }

    /**
     * Return the ID of a Username
     * @param string Username
     * @return userID if exists, null otherwise
     */
    public static function ID_from_username($name) {
        $digest = base64_encode(md5($name, true));
        $key = "username_id_$digest";
        $ID = G::$Cache->get_value($key);
        if ($ID == -1) {
            return null;
        } elseif ($ID === false) {
            G::$DB->prepared_query("SELECT ID FROM users_main WHERE Username=?", $name);
            if (!G::$DB->has_results()) {
                // cache negative hits for a while
                G::$Cache->cache_value($key, -1, 300);
                return null;
            }
            list($ID) = G::$DB->next_record();
            G::$Cache->cache_value($key, $ID, 300);
        }
        return $ID;
    }

    /**
     * Default settings to use for SiteOptions
     * @return array
     */
    public static function default_site_options() {
        return array(
            'CoverArt' => true,
            'AutoSubscribe' => true,
            'ShowHotMovieOnHomePage' => true,
        );
    }

    /**
     * Updates the site options in the database
     *
     * @param int $UserID the UserID to set the options for
     * @param array $NewOptions the new options to set
     * @return false if $NewOptions is empty, true otherwise
     */
    public static function update_site_options($UserID, $NewOptions) {
        if (!is_number($UserID)) {
            error(0);
        }
        if (empty($NewOptions)) {
            return false;
        }

        $QueryID = G::$DB->get_query_id();

        // Get SiteOptions
        G::$DB->query("
			SELECT SiteOptions
			FROM users_info
			WHERE UserID = $UserID");
        list($SiteOptions) = G::$DB->next_record(MYSQLI_NUM, false);
        $SiteOptions = unserialize_array($SiteOptions);
        $SiteOptions = array_merge(static::default_site_options(), $SiteOptions);

        // Get HeavyInfo
        $HeavyInfo = Users::user_heavy_info($UserID);

        // Insert new/replace old options
        $SiteOptions = array_merge($SiteOptions, $NewOptions);
        $HeavyInfo = array_merge($HeavyInfo, $NewOptions);

        // Update DB
        G::$DB->query("
			UPDATE users_info
			SET SiteOptions = '" . db_string(serialize($SiteOptions)) . "'
			WHERE UserID = $UserID");
        G::$DB->set_query_id($QueryID);

        // Update cache
        G::$Cache->cache_value("user_info_heavy_$UserID", $HeavyInfo, 0);

        // Update G::$LoggedUser if the options are changed for the current
        if (G::$LoggedUser['ID'] == $UserID) {
            G::$LoggedUser = array_merge(G::$LoggedUser, $NewOptions);
            G::$LoggedUser['ID'] = $UserID; // We don't want to allow userid switching
        }
        return true;
    }

    /**
     * Generates a check list of release types, ordered by the user or default
     * @param array $SiteOptions
     * @param boolean $Default Returns the default list if true
     */
    public static function release_order(&$SiteOptions, $Default = false) {
        $RT = t('server.torrents.release_types') + array(
            1024 => t('server.artist.1024'),
            1023 => t('server.artist.1023'),
            1022 => t('server.artist.1022'),
            1021 => t('server.artist.1021')
        );

        if ($Default || empty($SiteOptions['SortHide'])) {
            $Sort = &$RT;
            $Defaults = !empty($SiteOptions['HideTypes']);
        } else {
            $Sort = &$SiteOptions['SortHide'];
            $MissingTypes = array_diff_key($RT, $Sort);
            if (!empty($MissingTypes)) {
                foreach (array_keys($MissingTypes) as $Missing) {
                    $Sort[$Missing] = 0;
                }
            }
        }

        foreach ($Sort as $Key => $Val) {
            if (isset($Defaults)) {
                $Checked = $Defaults && isset($SiteOptions['HideTypes'][$Key]) ? ' checked="checked"' : '';
            } else {
                if (!isset($RT[$Key])) {
                    continue;
                }
                $Checked = $Val ? ' checked="checked"' : '';
                $Val = $RT[$Key];
            }

            $ID = $Key . '_' . (int)(!!$Checked);

            // The HTML is indented this far for proper indentation in the generated HTML
            // on user.php?action=edit
?>
            <li class="sortable_item">
                <label><input type="checkbox" <?= $Checked ?> id="<?= $ID ?>" /> <?= $Val ?></label>
            </li>
<?
        }
    }

    /**
     * Returns the default order for the sort list in a JS-friendly string
     * @return string
     */
    public static function release_order_default_js(&$SiteOptions) {
        ob_start();
        self::release_order($SiteOptions, true);
        $HTML = ob_get_contents();
        ob_end_clean();
        return json_encode($HTML);
    }

    /**
     * Generate a random string
     *
     * @param  int    $Length
     * @return string random alphanumeric string
     */
    public static function make_secret($Length = 32) {
        $NumBytes = (int) round($Length / 2);
        $Secret = bin2hex(openssl_random_pseudo_bytes($NumBytes));
        return substr($Secret, 0, $Length);
    }

    /**
     * Verify a password against a password hash
     *
     * @param string $Password password
     * @param string $Hash password hash
     * @return bool  true on correct password
     */
    public static function check_password($Password, $Hash) {
        if (empty($Password) || empty($Hash)) {
            return false;
        }

        return password_verify(hash('sha256', $Password), $Hash);
    }

    /**
     * Create salted crypt hash for a given string with
     * settings specified in CONFIG['CRYPT_HASH_PREFIX']
     *
     * @param string  $Str string to hash
     * @return string hashed password
     */
    public static function make_password_hash($Str) {
        return password_hash(hash('sha256', $Str), PASSWORD_DEFAULT);
    }

    /**
     * Returns a username string for display
     *
     * @param int|string $UserID
     * @param boolean $Badges whether or not badges (donor, warned, enabled) should be shown
     * @param boolean $IsWarned -- TODO: Why the fuck do we need this?
     * @param boolean $IsEnabled -- TODO: Why the fuck do we need this?
     * @param boolean $Class whether or not to show the class
     * @param boolean $Title whether or not to show the title
     * @param boolean $IsDonorForum for displaying donor forum honorific prefixes and suffixes
     * @return string HTML formatted username
     */
    public static function format_username($UserID, $Badges = false, $IsWarned = true, $IsEnabled = true, $Class = false, $Title = false, $IsDonorForum = false, $ProfileBadges = false, $UsernameBadges = false) {
        global $Classes;
        $donation = new Donation();
        $Badges = $Badges;
        $ProfileBadges = $ProfileBadges && CONFIG['ENABLE_BADGE'];
        $UsernameBadges = $UsernameBadges && CONFIG['ENABLE_BADGE'];

        // This array is a hack that should be made less retarded, but whatevs
        //                        PermID => ShortForm
        $SecondaryClasses = array(
            '23' => 'FLS', // First Line Support
            '30' => 'IN', // Interviewer
            '31' => 'TC', // Torrent Celebrity
            '32' => 'D', // Designer
            '33' => 'ST', // Security Team
            '37' => 'AR', // Archive Team
            '36' => 'AT', // Alpha Team
            '38' => 'CT', // Charlie Team
            '39' => 'DT', // Delta Team
            '56' => 'TI',
            '62' => 'A',
        );

        if ($UserID == 0) {
            return 'System';
        }

        $UserInfo = self::user_info($UserID);
        if ($UserInfo['Username'] == '') {
            return "Unknown [$UserID]";
        }

        $Str = '<span class="Username">';

        $Username = $UserInfo['Username'];
        $Paranoia = $UserInfo['Paranoia'];

        if ($UserInfo['Class'] < $Classes[CONFIG['USER_CLASS']['MOD']]['Level']) {
            $OverrideParanoia = check_perms('users_override_paranoia', $UserInfo['Class']);
        } else {
            // Don't override paranoia for mods who don't want to show their donor heart
            $OverrideParanoia = false;
        }
        $ShowDonorIcon = (!in_array('hide_donor_heart', $Paranoia) || $OverrideParanoia);

        if ($IsDonorForum) {
            list($Prefix, $Suffix, $HasComma) = $donation->titles($UserID);
            $Username = "$Prefix $Username" . ($HasComma ? ', ' : ' ') . "$Suffix ";
        }
        $DonorRewards = $donation->rewards($UserID);
        $EnabledRewards = $donation->enabledRewards($UserID);
        if ($EnabledRewards['HasGradientsColor'] && $DonorRewards['GradientsColor']) {
            $UsernameColor = ' style="background-image:-webkit-linear-gradient(left,' . $DonorRewards['GradientsColor'] . ');-webkit-background-clip:text;-webkit-text-fill-color:transparent;"';
        } else if (($EnabledRewards['HasUnlimitedColor'] || $EnabledRewards['HasLimitedColorName']) && $DonorRewards['ColorUsername']) {
            $UsernameColor = ' style="color:' . $DonorRewards['ColorUsername'] . ';"';
        } else {
            $UsernameColor = '';
        }
        if ($Title) {
            $Str .= "<strong><a href=\"user.php?id=$UserID\"$UsernameColor>$Username</a></strong>";
        } else {
            $Str .= "<a href=\"user.php?id=$UserID\"$UsernameColor>$Username</a>";
        }
        $DonorRank = $donation->rank($UserID);
        if ($DonorRank == 0 && $UserInfo['Donor'] == 1) {
            $DonorRank = 1;
        }
        if ($ShowDonorIcon && $DonorRank > 0) {
            $IconLink = '#';
            $IconImage = 'donor.png';
            $IconText = '捐助者';
            $DonorHeart = $DonorRank;
            $SpecialRank = $donation->specialRank($UserID);
            if ($EnabledRewards['HasDonorIconMouseOverText'] && !empty($DonorRewards['IconMouseOverText'])) {
                $IconText = display_str($DonorRewards['IconMouseOverText']);
            }
            if ($EnabledRewards['HasDonorIconLink'] && !empty($DonorRewards['CustomIconLink'])) {
                $IconLink = display_str($DonorRewards['CustomIconLink']);
            }
            if ($EnabledRewards['HasCustomDonorIcon'] && !empty($DonorRewards['CustomIcon'])) {
                $IconImage = ImageTools::process($DonorRewards['CustomIcon'], false, 'donoricon', $UserID);
            } else {
                if ($SpecialRank === MAX_SPECIAL_RANK) {
                    $DonorHeart = 6;
                } elseif ($DonorRank === 5) {
                    $DonorHeart = 4; // Two points between rank 4 and 5
                } elseif ($DonorRank >= MAX_RANK) {
                    $DonorHeart = 5;
                }
                if ($DonorHeart === 1) {
                    $IconImage = CONFIG['STATIC_SERVER'] . 'common/symbols/donor.png';
                } else {
                    $IconImage = CONFIG['STATIC_SERVER'] . "common/symbols/donor_{$DonorHeart}.png";
                }
            }
            $Str .= "<a target=\"_blank\" href=\"$IconLink\"><img class=\"donor_icon\" src=\"$IconImage\" data-tooltip=\"$IconText\" /></a>";
        }

        if ($IsEnabled && $UserInfo['Enabled'] == 2) {
            $Str .= '<a href="rules.php" data-tooltip="' . t('server.user.disabled') . '"><i class="disabled_flag" aria-hidden="true">' . icon("User/disabled") . '</i></a>';
        } else if ($IsWarned && $IsEnabled) {
            if ($UserInfo['Warned'] != '0000-00-00 00:00:00') {
                $Str .= '<a href="wiki.php?action=article&amp;id=114"'
                    . '><i class="warned-flag" aria-hidden="true" data-tooltip="Warned'
                    . (G::$LoggedUser['ID'] === $UserID ? ' - Expires ' . date('Y-m-d H:i', strtotime($UserInfo['Warned'])) : '')
                    . '">'
                    . icon("User/warned")
                    . '</i></a>';
            } else {
                $UserHeavyInfo = self::user_heavy_info($UserID);
                $DisabledTitle = "";
                $CheckDisabled = array(
                    "DisableInvites" => "Invites",
                    "DisablePosting" => "Posting",
                    "DisableCheckAll" => "Check All Torrents",
                    "DisableCheckSelf" => "Check Self Torrents",
                    "DisableUpload" => "Torrent upload",
                    "DisablePoints" => "Bonus Points",
                    "DisableWiki" => "Wiki",
                    "DisableAvatar" => "Avatar",
                    "DisablePM" => "PM",
                    "DisableRequests" => "Requests",
                    "DisableForums" => "Forums",
                    "DisableIRC" => "IRC",
                    "DisableTagging" => "Tagging"
                );
                foreach ($CheckDisabled as $key => $title) {
                    if ($UserHeavyInfo[$key]) {
                        if ($DisabledTitle) {
                            $DisabledTitle .= ", $title";
                        } else {
                            $DisabledTitle .= $title;
                        }
                    }
                }
                if (!$UserHeavyInfo["CanLeech"]) {
                    if ($DisabledTitle) {
                        $DisabledTitle .= ", Leech";
                    } else {
                        $DisabledTitle .= "Leech";
                    }
                }
                if ($DisabledTitle) {
                    if (!check_perms('users_view_disabled') && G::$LoggedUser['ID'] != $UserID) {
                        $DisabledTitle = "Limited privilege(s)";
                    } else {
                        $DisabledTitle .= " privilege(s) disabled";
                    }
                    $Str .= '<i class="half-warned-flag" aria-hidden="true" data-tooltip="' . $DisabledTitle . '">' . icon("User/warned")  . '</i>';
                }
            }
        }

        if ($Badges) {
            $ClassesDisplay = array();
            foreach (array_intersect_key($SecondaryClasses, $UserInfo['ExtraClasses']) as $PermID => $PermShort) {
                $ClassesDisplay[] = '<span class="secondary_class" data-tooltip="' . $Classes[$PermID]['Name'] . '">' . $PermShort . '</span>';
            }
            if (!empty($ClassesDisplay)) {
                $Str .= '<span class="Username-classes">' . implode('', $ClassesDisplay) . '</span>';
            }
        }

        if ($Class) {
            if ($Title) {
                $Str .= ' <strong>(' . Users::make_class_string($UserInfo['PermissionID']) . ')</strong>';
            } else {
                $Str .= ' (' . Users::make_class_string($UserInfo['PermissionID']) . ')';
            }
        }
        if ($ProfileBadges || $UsernameBadges) {
            $WearOrDisplay = Badges::get_wear_badges($UserID);
            foreach ($WearOrDisplay['Username'] as $BadgeID) {
                $Badge = Badges::get_badges_by_id($BadgeID);
                $Str .= "<span class=\"" . ($ProfileBadges ? "user_profile" : "post_username") . "_badges\" data-tooltip=\"" . Badges::get_text($Badge['Label'], 'badge_name') . "\"><img src=\"" . $Badge['SmallImage'] . "\"></span>";
            }
        }
        if ($Title) {
            // Image proxy CTs
            if (check_perms('site_proxy_images') && !empty($UserInfo['Title'])) {
                $UserInfo['Title'] = preg_replace_callback(
                    '~src=("?)(http.+?)(["\s>])~',
                    function ($Matches) {
                        return 'src=' . $Matches[1] . ImageTools::process($Matches[2]) . $Matches[3];
                    },
                    $UserInfo['Title']
                );
            }

            if ($UserInfo['Title']) {
                $Str .= ' <span class="Username-customTitle">(' . $UserInfo['Title'] . ')</span>';
            }
        }
        $Str .= '</span>';
        return $Str;
    }

    /**
     * Given a class ID, return its name.
     *
     * @param int $ClassID
     * @return string name
     */
    public static function make_class_string($ClassID) {
        global $Classes;
        return $Classes[$ClassID]['Name'];
    }

    /**
     * Returns an array with User Bookmark data: group IDs, collage data, torrent data
     * @param string|int $UserID
     * @return array Group IDs, Bookmark Data, Torrent List
     */
    public static function get_bookmarks($UserID) {
        $UserID = (int)$UserID;

        if (($Data = G::$Cache->get_value("bookmarks_group_ids_$UserID"))) {
            list($GroupIDs, $BookmarkData) = $Data;
        } else {
            $QueryID = G::$DB->get_query_id();
            G::$DB->query("
				SELECT GroupID, Sort, `Time`
				FROM bookmarks_torrents
				WHERE UserID = $UserID
				ORDER BY Sort, `Time` ASC");
            $GroupIDs = G::$DB->collect('GroupID');
            $BookmarkData = G::$DB->to_array('GroupID', MYSQLI_ASSOC);
            G::$DB->set_query_id($QueryID);
            G::$Cache->cache_value(
                "bookmarks_group_ids_$UserID",
                array($GroupIDs, $BookmarkData),
                3600
            );
        }

        $TorrentList = Torrents::get_groups($GroupIDs, true, false, false);

        return array($GroupIDs, $BookmarkData, $TorrentList);
    }

    /**
     * Generate HTML for a user's avatar or just return the avatar URL
     * @param unknown $Avatar
     * @param unknown $UserID
     * @param string $Username
     * @param unknown $Setting
     * @param number $Size
     * @param string $ReturnHTML
     * @return string
     */
    public static function show_avatar($Avatar, $UserID, $Username, $Setting, $Size = 150, $ReturnHTML = True) {
        $donation = new Donation();
        $Avatar = ImageTools::process($Avatar, false, 'avatar', $UserID);
        $AvatarMouseOverText = '';
        $FirstAvatar = '';
        $SecondAvatar = '';
        $EnabledRewards = $donation->enabledRewards($UserID);
        if ($EnabledRewards['HasAvatarMouseOverText']) {
            $Rewards = $donation->rewards($UserID);
            $AvatarMouseOverText = $Rewards['AvatarMouseOverText'];
        }
        if (!empty($AvatarMouseOverText)) {
            $AvatarMouseOverText =  "data-tooltip=\"$AvatarMouseOverText\" alt=\"$AvatarMouseOverText\"";
        } else {
            $AvatarMouseOverText = "alt=\"$Username's avatar\"";
        }
        if ($EnabledRewards['HasSecondAvatar'] && !empty($Rewards['SecondAvatar'])) {
            $SecondAvatar = ImageTools::process($Rewards['SecondAvatar'], true, 'avatar2', $UserID);
        }
        $ShowAvatar = false;
        $Attrs = "width=\"$Size\" $AvatarMouseOverText";
        // purpose of the switch is to set $FirstAvatar (URL)
        // case 1 is avatars disabled
        switch ($Setting) {
            case 0:
                if (!empty($Avatar)) {
                    $FirstAvatar = $Avatar;
                } else {
                    $FirstAvatar = CONFIG['STATIC_SERVER'] . 'common/avatars/default.png';
                }
                break;
            case 2:
                $ShowAvatar = true;
                // Fallthrough
            case 3:
                if ($ShowAvatar && !empty($Avatar)) {
                    $FirstAvatar = $Avatar;
                    break;
                }
                switch (G::$LoggedUser['Identicons']) {
                    case 0:
                        $Type = 'identicon';
                        break;
                    case 1:
                        $Type = 'monsterid';
                        break;
                    case 2:
                        $Type = 'wavatar';
                        break;
                    case 3:
                        $Type = 'retro';
                        break;
                    case 4:
                        $Type = '1';
                        $Robot = true;
                        break;
                    case 5:
                        $Type = '2';
                        $Robot = true;
                        break;
                    case 6:
                        $Type = '3';
                        $Robot = true;
                        break;
                    default:
                        $Type = 'identicon';
                }
                $Rating = 'pg';
                if (!$Robot) {
                    $FirstAvatar = 'https://secure.gravatar.com/avatar/' . md5(strtolower(trim($Username))) . "?s=$Size&amp;d=$Type&amp;r=$Rating";
                } else {
                    $FirstAvatar = 'https://robohash.org/' . md5($Username) . "?set=set$Type&amp;size={$Size}x$Size";
                }
                break;
            default:
                $FirstAvatar = CONFIG['STATIC_SERVER'] . 'common/avatars/default.png';
        }
        // in this case, $Attrs is actually just a URL
        if (!$ReturnHTML) {
            return $FirstAvatar;
        }
        $Class = boolval($SecondAvatar) ? 'is-canChange' : '';
        $ToReturn = "<div class='Avatar $Class'>";
        foreach ([$FirstAvatar, $SecondAvatar] as $AvatarNum => $CurAvatar) {
            if ($CurAvatar) {
                $ToReturn .= "<div class=\"Avatar-item\"><img class=\"Avatar-image avatar_$AvatarNum\" $Attrs  src=\"$CurAvatar\" /></div>";
            }
        }
        $ToReturn .= '</div>';
        return $ToReturn;
    }
    public static function has_avatars_enabled() {
        global $HeavyInfo;
        return empty($HeavyInfo['DisableAvatars']) || $HeavyInfo['DisableAvatars'] != 1;
    }
    /**
     * Checks whether user has autocomplete enabled
     *
     * 0 - Enabled everywhere (default), 1 - Disabled, 2 - Searches only
     *
     * @param string $Type the type of the input.
     * @param boolean $Output echo out HTML
     * @return boolean
     */
    public static function has_autocomplete_enabled($Type, $Output = true) {
        $Enabled = false;
        if (empty(G::$LoggedUser['AutoComplete'])) {
            $Enabled = true;
        } elseif (G::$LoggedUser['AutoComplete'] !== 1) {
            switch ($Type) {
                case 'search':
                    if (G::$LoggedUser['AutoComplete'] == 2) {
                        $Enabled = true;
                    }
                    break;
                case 'other':
                    if (G::$LoggedUser['AutoComplete'] != 2) {
                        $Enabled = true;
                    }
                    break;
            }
        }
        if ($Enabled && $Output) {
            echo ' data-gazelle-autocomplete="true"';
        }
        if (!$Output) {
            // don't return a boolean if you're echoing HTML
            return $Enabled;
        }
    }

    /**
     * Initiate a password reset
     *
     * @param int $UserID The user ID
     * @param string $Username The username
     * @param string $Email The email address
     */
    public static function resetPassword($UserID, $Username, $Email) {
        $ResetKey = Users::make_secret();
        G::$DB->query("
			UPDATE users_info
			SET
				ResetKey = '" . db_string($ResetKey) . "',
				ResetExpires = '" . time_plus(60 * 60) . "'
			WHERE UserID = '$UserID'");

        Misc::send_email_with_tpl($Email, 'password_reset', [
            'Username' => $Username,
            'ResetKey' => $ResetKey,
            'IP' => $_SERVER['REMOTE_ADDR'],
            'SITE_NAME' => CONFIG['SITE_NAME'],
            'SITE_URL' => site_url(false),
        ], 'text/html');
    }

    /**
     * Removes the custom title of a user
     *
     * @param integer $ID The id of the user in users_main
     */
    public static function removeCustomTitle($ID) {
        G::$DB->prepared_query("UPDATE users_main SET Title='' WHERE ID = ? ", $ID);
        G::$Cache->delete_value("user_info_{$ID}");
        G::$Cache->delete_value("user_stats_{$ID}");
    }

    /**
     * Purchases the custom title for a user
     *
     * @param integer $ID The id of the user in users_main
     * @param string $Title The text of the title (may contain BBcode)
     * @return boolean false if insufficient funds, otherwise true
     */
    public static function setCustomTitle($ID, $Title) {
        G::$DB->prepared_query(
            "UPDATE users_main SET Title = ? WHERE ID = ?",
            $Title,
            $ID
        );
        if (G::$DB->affected_rows() == 1) {
            G::$Cache->delete_value("user_info_{$ID}");
            G::$Cache->delete_value("user_stats_{$ID}");
            return true;
        }
        return false;
    }

    /**
     * Checks whether a user is allowed to purchase an invite. User classes up to Elite are capped,
     * users above this class will always return true.
     *
     * @param integer $ID The id of the user in users_main
     * @param integer $MinClass Minimum class level necessary to purchase invites
     * @return boolean false if insufficient funds, otherwise true
     */
    public static function canPurchaseInvite($ID, $MinClass) {
        $heavy = self::user_heavy_info($ID);
        if ($heavy['DisableInvites']) {
            return false;
        }
        $info = self::user_info($ID);
        return $info['EffectiveClass'] >= $MinClass;
    }

    public $UserID;

    function __construct($UserID) {
        $this->UserID = $UserID;
    }

    public function seedingLight() {
        global $DB;
        $UserID = $this->UserID;
        $DB->query("
            SELECT COUNT(x.uid) AS seedingCount
            FROM xbt_files_users AS x
                INNER JOIN torrents AS t ON t.ID = x.fid
            WHERE x.uid = '$UserID'
                AND x.remaining = 0
        ");
        $result = $DB->next_record(MYSQLI_ASSOC);
        return [
            'seedingCount' => (int) $result['seedingCount']
        ];
    }

    public function seedingHeavy() {
        global $DB;
        $UserID = $this->UserID;
        $DB->prepared_query("
            SELECT
                COUNT(xfu.uid) as seedingCount,
                SUM(t.Size) as seedingSize,
                SUM(IFNULL(t.Size / (1024 * 1024 * 1024) * 1 * (
                    0.025 + (
                        (0.06 * LN(1 + (xfh.seedtime / (24)))) / (POW(GREATEST(t.Seeders, 1), 0.6))
                    )
                ), 0)) AS seedingBonusPointsPerHour
            FROM
                (SELECT DISTINCT uid,fid FROM xbt_files_users WHERE active=1 AND remaining=0 AND mtime > unix_timestamp(NOW() - INTERVAL 1 HOUR) AND uid = ?) AS xfu
                JOIN xbt_files_history AS xfh ON xfh.uid = xfu.uid AND xfh.fid = xfu.fid
                JOIN torrents AS t ON t.ID = xfu.fid
            WHERE
                xfu.uid = ?
        ", $UserID, $UserID);
        $result = $DB->next_record(MYSQLI_ASSOC);
        return [
            'seedingCount' => (int) $result['seedingCount'],
            'seedingSize' => (float) $result['seedingSize'],
            'seedingBonusPointsPerHour' => (float) $result['seedingBonusPointsPerHour']
        ];
    }

    public function leeching() {
        global $DB;
        $UserID = $this->UserID;
        $DB->query("
            SELECT COUNT(x.uid) AS leechingCount
            FROM xbt_files_users AS x
                INNER JOIN torrents AS t ON t.ID = x.fid
            WHERE x.uid = '$UserID'
                AND x.remaining > 0
        ");
        $result = $DB->next_record(MYSQLI_ASSOC);
        return [
            'leechingCount' => (int) $result['leechingCount']
        ];
    }

    public function snatched() {
        global $DB;
        $UserID = $this->UserID;
        $DB->query("
            SELECT COUNT(x.uid) AS snatchedCount, COUNT(DISTINCT x.fid) as uniqueSnatchedCount
            FROM xbt_snatched AS x
                INNER JOIN torrents AS t ON t.ID = x.fid
            WHERE x.uid = '$UserID'
        ");
        $result = $DB->next_record(MYSQLI_ASSOC);
        return [
            'snatchedCount' => (int) $result['snatchedCount'],
            'uniqueSnatchedCount' => (int) $result['uniqueSnatchedCount']
        ];
    }

    public function uploads() {
        global $DB;
        $UserID = $this->UserID;
        $DB->query("
            SELECT COUNT(t.ID) AS Uploads
            FROM users_main AS u
            LEFT JOIN torrents AS t ON t.UserID = u.ID
            WHERE u.id = '$UserID'
        ");
        $result = $DB->next_record(MYSQLI_ASSOC);
        return [
            'uploadCount' => (int) $result['Uploads'],
        ];
    }
    public static function get_nav_items(): array {
        $list = G::$Cache->get_value("nav_items");
        if (!$list) {
            $QueryID = G::$DB->get_query_id();
            G::$DB->prepared_query("
                SELECT id, tag, title, target, tests, test_user, mandatory, initial
                FROM nav_items");
            $list = G::$DB->to_array("id", MYSQLI_ASSOC, false);
            G::$Cache->cache_value("nav_items", $list, 0);
            G::$DB->set_query_id($QueryID);
        }
        return $list;
    }
}
