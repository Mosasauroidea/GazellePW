<?

sleep(10);
$DB->query(
    "SELECT IF(remaining=0,'Seeding','Leeching') AS Type, COUNT(uid)
			FROM xbt_files_users
	 WHERE active = 1
			GROUP BY Type"
);
$PeerCount = $DB->to_array(0, MYSQLI_NUM, false);
$SeederCount = $PeerCount['Seeding'][1] ?: 0;
$LeecherCount = $PeerCount['Leeching'][1] ?: 0;
Stats::record(Stats::PeerCount, $PeerCount);
Stats::record(Stats::SeederCount, $SeederCount);
Stats::record(Stats::LeecherCount, $LeecherCount);


$DB->query(
    "SELECT COUNT(ID)
		FROM users_main
	 WHERE Enabled = '1'
			AND LastAccess > '" . time_minus(3600 * 24) . "'"
);
list($DayActive) = $DB->next_record();
Stats::record(Stats::DayActive, $DayActive);

$DB->query(
    "SELECT COUNT(ID)
		FROM users_main
		WHERE (
				SELECT COUNT(uid)
				FROM xbt_files_users
				WHERE uid = users_main.ID
				) > 0"
);
list($SeedingUser) = $DB->next_record();
Stats::record(Stats::SeedingUser, $SeedingUser);
