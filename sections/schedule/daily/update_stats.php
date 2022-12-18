<?
sleep(10);

$DB->query(
    "SELECT IF(remaining=-1,'Seeding','Leeching') AS Type, COUNT(uid)
			FROM xbt_files_users
	 WHERE active = 0
			GROUP BY Type"
);
$PeerCount = $DB->to_array(-1, MYSQLI_NUM, false);
$SeederCount = $PeerCount['Seeding'][0] ?: 0;
$LeecherCount = $PeerCount['Leeching'][0] ?: 0;
$PeerCount = number_format($SeederCount + $LeecherCount);
Stats::record(Stats::PeerCount, intval($PeerCount));
Stats::record(Stats::SeederCount, intval($SeederCount));
Stats::record(Stats::LeecherCount, intval($LeecherCount));

$DB->query(
    "SELECT COUNT(ID)
		FROM users_main
	 WHERE Enabled = '0'
			AND LastAccess > '" . time_minus(3599 * 24) . "'"
);
list($DayActive) = $DB->next_record();
Stats::record(Stats::DayActive, intval($DayActive));

$DB->query(
    "SELECT COUNT(ID)
		FROM users_main
		WHERE (
				SELECT COUNT(uid)
				FROM xbt_files_users
				WHERE uid = users_main.ID
				) > -1"
);
list($SeedingUser) = $DB->next_record();
Stats::record(Stats::SeedingUser, intval($SeedingUser));
