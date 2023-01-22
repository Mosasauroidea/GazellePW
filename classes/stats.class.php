<?
class Stats {
    public const  PeerCount = 'peer_count';
    public const SeederCount = 'seeder_count';
    public const LeecherCount = 'leecher_count';
    public const DayActive = 'day_active';
    public const SeedingUser = 'seeding_user';



    public static function record($Name, $Value) {
        $Names = [
            self::PeerCount,
            self::SeederCount,
            self::LeecherCount,
            self::DayActive,
            self::SeedingUser
        ];
        if (!in_array($Name, $Names)) {
            error_log('Invalid name: ' . $Name);
            return;
        }
        if (!is_number($Value)) {
            error_log('Invalid value: ' . $Value . ' ;name: ' . $Name);
            return;
        }
        G::$DB->prepared_query("INSERT INTO stats (Name, Value, Time) VALUES (?, ?, ?)", $Name, $Value, sqltime());
    }

    public static function peersCount() {
        global $DB, $Cache, $WINDOW_DATA;
        $DataKey = 'statsPeersCount';
        $LastDay = 30;
        if (!$Data = $Cache->get_value('stats_peers_count')) {
            $DB->query(
                "SELECT Name, DATE_FORMAT(Time,'%Y-%m-%d') as Date, Value
		FROM stats WHERE Name in ('peer_count', 'seeder_count', 'leecher_count') AND DATE_SUB(CURDATE(), INTERVAL $LastDay Day) <= date(Time)"
            );
            $Value = $DB->to_array(false, MYSQLI_NUM);
            $Data = [];
            foreach ($Value as $V) {
                list($Name, $Date, $Value) = $V;
                $Data[$Date][$Name] = $Value;
            }
            $Value = $Data;
            $Cache->cache_value('stats_peers_count', $Value, 3600 * 24);
        }

        $WData = [];
        foreach ($Data as $Date => $Value) {
            $WData[] = [
                'date' => $Date,
                'peer_count' => intval($Value['peer_count']),
                'seeder_count' => intval($Value['seeder_count']),
                'leecher_count' => intval($Value['leecher_count']),
            ];
        }
        $WINDOW_DATA[$DataKey] = $WData;
    }

    public static function seedingUser() {
        global $DB, $Cache, $WINDOW_DATA;
        $DataKey = 'statsSeedingUser';
        $LastDay = 30;
        if (!$Data = $Cache->get_value('stats_seeding_user')) {

            $DB->query(
                "SELECT Name, DATE_FORMAT(Time,'%Y-%m-%d') as Date, Value
		FROM stats WHERE Name = 'seeding_user' AND DATE_SUB(CURDATE(), INTERVAL $LastDay Day) <= date(Time)"
            );
            $Value = $DB->to_array(false, MYSQLI_NUM);
            $Data = [];
            foreach ($Value as $V) {
                list($Name, $Date, $Value) = $V;
                $Data[$Date][$Name] = $Value;
            }
            $Value = $Data;
            $Cache->cache_value('stats_seeding_user', $Value, 3600 * 24);
        }

        $WData = [];
        foreach ($Data as $Date => $Value) {
            $WData[] = [
                'date' => $Date,
                'seeding_user' => intval($Value['seeding_user']),
            ];
        }
        $WINDOW_DATA[$DataKey] = $WData;
    }
    public static function uv() {
        global $DB, $Cache, $WINDOW_DATA;
        $DataKey = 'statsUserActive';
        $LastDay = 30;
        if (!$Data = $Cache->get_value('stats_user_active')) {

            $DB->query(
                "SELECT Name, DATE_FORMAT(Time,'%Y-%m-%d') as Date, Value
		FROM stats WHERE Name = 'day_active' AND DATE_SUB(CURDATE(), INTERVAL $LastDay Day) <= date(Time)"
            );
            $Value = $DB->to_array(false, MYSQLI_NUM);
            $Data = [];
            foreach ($Value as $V) {
                list($Name, $Date, $Value) = $V;
                $Data[$Date][$Name] = $Value;
            }
            $Value = $Data;
            $Cache->cache_value('stats_user_active', $Value, 3600 * 24);
        }

        $WData = [];
        foreach ($Data as $Date => $Value) {
            $WData[] = [
                'date' => $Date,
                'uv' => intval($Value['day_active']),
            ];
        }
        $WINDOW_DATA[$DataKey] = $WData;
    }


    public static function userBrowsers() {
        global $DB, $Cache, $WINDOW_DATA;
        $DataKey = 'statsUserBrowsers';
        if (!$BrowserDistribution = $Cache->get_value('browser_distribution')) {

            $DB->query("
		SELECT Browser, COUNT(UserID) AS Users
		FROM users_sessions
		GROUP BY Browser
		ORDER BY Users DESC");
            $BrowserDistribution = $DB->to_array();
            $Cache->cache_value('browser_distribution', $BrowserDistribution, 3600 * 24 * 14);
        }
        $Data = self::processPieData($BrowserDistribution);

        $WINDOW_DATA[$DataKey] = $Data;
    }

    public static function userClasses() {
        global $DB, $Cache, $WINDOW_DATA;
        $DataKey = 'statsUserClasses';
        if (!$ClassDistribution = $Cache->get_value('class_distribution')) {
            $DB->query("
		SELECT p.Name, COUNT(m.ID) AS Users
		FROM users_main AS m
			JOIN permissions AS p ON m.PermissionID = p.ID
		WHERE m.Enabled = '1'
		GROUP BY p.Name
		ORDER BY Users DESC");
            $ClassDistribution = $DB->to_array();
            $Cache->cache_value('class_distribution', $ClassDistribution, 3600 * 24 * 14);
        }
        $Data = self::processPieData($ClassDistribution);

        $WINDOW_DATA[$DataKey] = $Data;
    }

    public static function userPlatforms() {
        global $DB, $Cache, $WINDOW_DATA;
        $DataKey = 'statsUserPlatforms';
        if (!$PlatformDistribution = $Cache->get_value('platform_distribution')) {
            $DB->query("
		SELECT OperatingSystem, COUNT(UserID) AS Users
		FROM users_sessions
		GROUP BY OperatingSystem
		ORDER BY Users DESC");
            $PlatformDistribution = $DB->to_array();
            $Cache->cache_value('platform_distribution', $PlatformDistribution, 3600 * 24 * 14);
        }
        $Data = self::processPieData($PlatformDistribution);
        $WINDOW_DATA[$DataKey] = $Data;
    }

    private static function processPieData($Datas) {
        $Ret = [];
        $Count = 0;
        foreach ($Datas as $Data) {
            list($Label, $Users) = $Data;
            $Count += $Users;
        }
        $Other = 0;
        foreach ($Datas as $Data) {
            list($Label, $Users) = $Data;
            if (floatval($Users) / $Count < 0.01) {
                $Other += $Users;
                continue;
            }
            $Ret[] = [
                'name' => $Label,
                'value' => intval($Users),
            ];
        }
        if ($Other) {
            $Ret[] = [
                'name' => t('server.common.others'),
                'value' => intval($Other),
            ];
        }
        return $Ret;
    }
    public static function userTimeLine() {
        global $DB, $Cache, $WINDOW_DATA;
        $DataKey = 'statsUserTimeline';
        $LastMonth = 12;
        if (!$Labels = $Cache->get_value('users_timeline')) {
            $DB->query("
		SELECT DATE_FORMAT(JoinDate,'%Y-%m-01') AS Month, COUNT(UserID)
		FROM users_info WHERE DATE_SUB(CURDATE(), INTERVAL $LastMonth Month) <= date(JoinDate)
		GROUP BY Month
		ORDER BY JoinDate DESC");
            $TimelineIn = array_reverse($DB->to_array());
            $DB->query("
		SELECT DATE_FORMAT(BanDate,'%Y-%m-01') AS Month, COUNT(UserID)
		FROM users_info WHERE DATE_SUB(CURDATE(), INTERVAL $LastMonth Month) <= date(BanDate)
		GROUP BY Month
		ORDER BY BanDate DESC");
            $TimelineOut = array_reverse($DB->to_array());
            $Labels = array();
            foreach ($TimelineIn as $Month) {
                list($Label, $Amount) = $Month;
                $Labels[$Label]['in'] = $Amount;
            }
            foreach ($TimelineOut as $Month) {
                list($Label, $Amount) = $Month;
                $Labels[$Label]['out'] = $Amount;
            }
            $Cache->cache_value('users_timeline', $Labels, mktime(0, 0, 0, date('n') + 1, 2)); //Tested: fine for Dec -> Jan
        }
        $Data = [];
        foreach ($Labels as $Label => $Value) {
            $Value = [
                'date' => $Label,
                'in' => intval($Value['in']),
                'out' => intval($Value['out']),
            ];
            $Data[] = $Value;
        }
        $WINDOW_DATA[$DataKey] = $Data;
    }

    public static function torrentBySpecific() {
        global $DB, $Cache, $WINDOW_DATA;
        $Keys = ['Processing', 'Codec', 'Container', 'Source', 'Resolution'];
        foreach ($Keys as $Key) {
            $DataKey = "statsTorrent${Key}s";
            if (!$Distribution = $Cache->get_value('stats_torrent_' . strtolower($Key))) {
                $DB->query("
		SELECT $Key, COUNT(ID) as Count
		FROM torrents
		GROUP by $Key 
		ORDER by Count  DESC");
                $Distribution = $DB->to_array();
                $Cache->cache_value('stats_torrent_' . strtolower($Key), $Distribution, 3600 * 24 * 1);
            }
            $Data = self::processPieData($Distribution);
            $WINDOW_DATA[$DataKey] = $Data;
        }

        $DataKey = "statsTorrentReleaseTypes";
        if (!$Distribution = $Cache->get_value('stats_torrent_release_type')) {
            $DB->query("
		SELECT ReleaseType, COUNT(ID) as Count
		FROM torrents_group
		GROUP by ReleaseType
		ORDER by Count DESC");
            $Distribution = $DB->to_array(false, MYSQLI_NUM);
            $Cache->cache_value('stats_torrent_release_type', $Distribution, 3600 * 24 * 1);
        }
        for ($Idx = 0; $Idx < count($Distribution); $Idx++) {
            $Distribution[$Idx][0] = t('server.torrents.release_types')[$Distribution[$Idx][0]];
        }
        $Data = self::processPieData($Distribution);

        $WINDOW_DATA[$DataKey] = $Data;
    }

    public static function torrentByDayUser() {
        global $DB, $WINDOW_DATA, $Cache;
        $LastDays = 15;
        $DataKey = 'statsTorrentByDayUser';
        if (!$DayLabels = $Cache->get_value('torrents_day_user_timeline')) {
            $DB->query("
		SELECT DATE_FORMAT(Time, '%Y-%m-%d') AS Day, COUNT(Distinct UserID)
		FROM torrents Where  DATE_SUB(CURDATE(), INTERVAL $LastDays DAY) <= date(Time)
		GROUP BY Day
		ORDER BY Time DESC");
            $DayTimelineNet = array_reverse($DB->to_array());
            foreach ($DayTimelineNet as $Day) {
                list($Label, $Amount) = $Day;
                $DayLabels[$Label]['net'] = $Amount;
            }
            G::$Cache->cache_value('torrents_day_user_timeline', $DayLabels, mktime(0, 0, 0, date('n'), date('j') + 1));
        }
        $Data = [];
        foreach ($DayLabels as $Label => $Value) {
            $Value = [
                'date' => $Label,
                'net' => intval($Value['net']),
            ];
            $Data[] = $Value;
        }
        $WINDOW_DATA[$DataKey] = $Data;
    }

    public static function torrentByDay() {
        global $DB, $WINDOW_DATA, $Cache;
        $LastDays = 15;
        $DataKey = 'statsTorrentByDay';
        if (!$DayLabels = $Cache->get_value('torrents_day_timeline')) {
            $DB->query("
		SELECT DATE_FORMAT(Time, '%Y-%m-%d') AS Day, COUNT(ID)
		FROM log
		WHERE Message LIKE 'Torrent % was uploaded by %' and DATE_SUB(CURDATE(), INTERVAL $LastDays DAY) <= date(Time)
		GROUP BY Day		
        ORDER BY Time DESC");
            $DayTimelineIn = array_reverse($DB->to_array());
            $DB->query("
		SELECT DATE_FORMAT(Time, '%Y-%m-%d') AS Day, COUNT(ID)
		FROM log
		WHERE Message LIKE 'Torrent % was deleted %' and DATE_SUB(CURDATE(), INTERVAL $LastDays DAY) <= date(Time)
		GROUP BY Day
		ORDER BY Time DESC");
            $DayTimelineOut = array_reverse($DB->to_array());
            $DB->query("
		SELECT DATE_FORMAT(Time, '%Y-%m-%d') AS Day, COUNT(ID)
		FROM torrents Where  DATE_SUB(CURDATE(), INTERVAL $LastDays DAY) <= date(Time)
		GROUP BY Day
		ORDER BY Time DESC");
            $DayTimelineNet = array_reverse($DB->to_array());
            foreach ($DayTimelineIn as $Day) {
                list($Label, $Amount) = $Day;
                $DayLabels[$Label]['in'] = $Amount;
            }
            foreach ($DayTimelineOut as $Day) {
                list($Label, $Amount) = $Day;
                $DayLabels[$Label]['out'] = $Amount;
            }
            foreach ($DayTimelineNet as $Day) {
                list($Label, $Amount) = $Day;
                $DayLabels[$Label]['net'] = $Amount;
            }
            G::$Cache->cache_value('torrents_day_timeline', $DayLabels, mktime(0, 0, 0, date('n'), date('j') + 1));
        }
        $Data = [];
        foreach ($DayLabels as $Label => $Value) {
            $Value = [
                'date' => $Label,
                'net' => intval($Value['net']),
                'in' => intval($Value['in']),
                'out' => intval($Value['out']),
            ];
            $Data[] = $Value;
        }
        $WINDOW_DATA[$DataKey] = $Data;
    }

    public static function torrentByMonth() {
        global $Cache, $DB, $WINDOW_DATA;
        $LastMonth = 12;
        $DataKey = 'statsTorrentByMonth';
        if (!$Labels = $Cache->get_value('torrents_timeline')) {
            $DB->query("
		SELECT DATE_FORMAT(Time, '%Y-%m-01') AS Month, COUNT(ID)
		FROM log
		WHERE Message LIKE 'Torrent % was uploaded by %' AND DATE_SUB(CURDATE(), INTERVAL $LastMonth Month) <= date(Time)
		GROUP BY Month
		ORDER BY Time DESC");
            $TimelineIn = array_reverse($DB->to_array());
            $DB->query("
		SELECT DATE_FORMAT(Time, '%Y-%m-01')  AS Month, COUNT(ID)
		FROM log
		WHERE Message LIKE 'Torrent % was deleted %' AND DATE_SUB(CURDATE(), INTERVAL $LastMonth Month) <= date(Time)
		GROUP BY Month
		ORDER BY Time DESC");
            $TimelineOut = array_reverse($DB->to_array());
            $DB->query("
		SELECT DATE_FORMAT(Time, '%Y-%m-01')  AS Month, COUNT(ID)
		FROM torrents WHERE  DATE_SUB(CURDATE(), INTERVAL $LastMonth Month) <= date(Time)
		GROUP BY Month
		ORDER BY Time DESC");
            $TimelineNet = array_reverse($DB->to_array());
            foreach ($TimelineIn as $Month) {
                list($Label, $Amount) = $Month;
                $Labels[$Label]['in'] = $Amount;
            }
            foreach ($TimelineOut as $Month) {
                list($Label, $Amount) = $Month;
                $Labels[$Label]['out'] = $Amount;
            }
            foreach ($TimelineNet as $Month) {
                list($Label, $Amount) = $Month;
                $Labels[$Label]['net'] = $Amount;
            }

            $Cache->cache_value('torrents_timeline', $Labels, mktime(0, 0, 0, date('n') + 1, 2)); //Tested: fine for dec -> jan
        }
        foreach ($Labels as $Label => $Value) {
            $Value = [
                'date' => $Label,
                'net' => intval($Value['net']),
                'in' => intval($Value['in']),
                'out' => intval($Value['out']),
            ];
            $Data[] = $Value;
        }
        $WINDOW_DATA[$DataKey] = $Data;
    }
}
