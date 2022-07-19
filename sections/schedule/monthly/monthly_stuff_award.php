<?php

//------------- Delete dead torrents ------------------------------------//
//sleep(10);
include(CONFIG['SERVER_ROOT'] . '/sections/tools/managers/award_functions.php');
$Year = date('Y');
$Month = date('n') - 1;
$Quarter = 0;
if (!$Month) {
    $Month = 12;
    $Year -= 1;
}
$Time = timeConvert($Year, $Quarter, $Month);
$AwardDatas = [
    getAwardData("Sysop", $Time),
    getAwardData("Administrator", $Time),
    getAwardData("Moderator", $Time),
    getAwardData("Torrent Moderator", $Time),
    getAwardData("Forum Moderator", $Time),
    getAwardData("Torrent Inspector", $Time),
    getAwardData("First Line Support", $Time),
    getAwardData("Interviewer", $Time),
    getAwardData("Translators", $Time),
    getAwardData("Developer", $Time),
];
$MaxValue = [
    'DownloadCount' => 0,
    'UploadCount' => 0,
    'CheckCount' => 0,
    'RSReportCount' => 0,
    'RPReportCount' => 0,
    'EditCount' => 0,
    'PostCount' => 0,
    'SendJF' => 0,
    'Point' => 0,
    'ApplyCount' => 0,
];
makePoint($AwardDatas, $Bases, $PointRadios, $MaxValue);
$PutoutUsersIDs = [];
$testMsg = "";
foreach ($AwardDatas as $data) {
    foreach ($data['Users'] as $User) {
        if (!in_array($User['UserID'], $PutoutUsersIDs)) {
            $PutoutUsersIDs[] = $User['UserID'];
            $DB->query("UPDATE users_main SET BonusPoints = BonusPoints + " . $User['Salary'] . " WHERE ID = " . $User['UserID']);
            $Cache->delete_value('user_stats_' . $User['UserID']);
            $testMsg .= $User['UserID'] . ", " . $User['Salary'] . "\n";
        }
    }
}
print_r($testMsg);
