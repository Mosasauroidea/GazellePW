<?

use Gazelle\Action\RewardInfo;
use Gazelle\Manager\Reward;

include(CONFIG['SERVER_ROOT'] . '/sections/tools/managers/award_functions.php');
$PromoteYear = date('Y');
$PromoteMonth = date('n') - 1;
if (!$PromoteMonth) {
    $PromoteMonth = 12;
    $PromoteYear -= 1;
}

$DemoteMonth = $PromoteMonth - 1;
$DemoteYear = $PromoteYear;
if (!$DemoteMonth) {
    $DemoteMonth = 12;
    $DemoteYear -= 1;
}
$Quarter = 0;


$PromoteTime = timeConvert($PromoteYear, $Quarter, $PromoteMonth);
list($promoteFromY, $promoteFromM, $promoteToY, $promoteToM) = $PromoteTime;

$DemoteTime = timeConvert($DemoteYear, $Quarter, $DemoteMonth);
list($demoteFromY, $demoteFromM,,) = $DemoteTime;
$demoteToY = $promoteToY;
$demoteToM = $promoteToM;

// TI 
$PromoteCheckCount = CONFIG['SecondaryClassAwardConfig']['TI_CHECK_COUNT'];
$TIPermissionID = CONFIG['SecondaryClassAwardConfig']['TI_PERMISSION_ID'];
if ($PromoteCheckCount > 0 && !empty($TIPermissionID)) {
    // promote
    $sql =
        "SELECT tc.UserID as UserID, COUNT(DISTINCT torrentid) AS Count, ul.PermissionID FROM torrents_check tc  
            LEFT JOIN users_levels ul ON ul.PermissionID = $TIPermissionID and ul.UserID =  tc.UserID
            WHERE tc.Time >= '$promoteFromY-$promoteFromM-1' AND tc.Time < '$promoteToY-$promoteToM-1' AND tc.Type = 1 
            GROUP By UserID 
            HAVING Count >= $PromoteCheckCount AND PermissionID is null";
    G::$DB->query($sql);
    $Ret = G::$DB->to_array(false, MYSQLI_ASSOC);
    $PromotedUserID = [];
    foreach ($Ret as $Data) {
        Users::add_secondary_class($Data['UserID'], [$TIPermissionID]);
        echo "Add TI secondary class to $UserID";
        $PromotedUserID[] = $Data['UserID'];
    }
    // demote
    $DemoteCheckCount = 2 * CONFIG['SecondaryClassAwardConfig']['TI_CHECK_COUNT'];
    $sql =
        "SELECT tc.UserID as UserID, COUNT(DISTINCT torrentid) AS Count, ul.PermissionID FROM torrents_check tc  
        RIGHT JOIN users_levels ul ON ul.PermissionID = $TIPermissionID and ul.UserID =  tc.UserID
        WHERE `Time` >= '$demoteFromY-$demoteFromM-1' AND `Time` < '$demoteToY-$demoteToM-1' AND `Type` = 1 
        GROUP By UserID 
        HAVING Count < $DemoteCheckCount AND PermissionID is not null";
    G::$DB->query($sql);
    $Ret = G::$DB->to_array(false, MYSQLI_ASSOC);
    foreach ($Ret as $Data) {
        if (in_array($Data['UserID'], $PromotedUserID)) {
            continue;
        }
        Users::remove_secondary_class($Data['UserID'], [$TIPermissionID]);
        echo "Drop TI secondary class from $UserID";
    }
}


// Uploader
$PromoteUploadCount = CONFIG['SecondaryClassAwardConfig']['UP_UPLOAD_COUNT'];
$UploaderPermissionID = CONFIG['SecondaryClassAwardConfig']['UPLOADER_PERMISSION_ID'];
if ($PromoteUploadCount > 0 && !empty($UploaderPermissionID)) {
    // promote
    $sql =
        "SELECT t.UserID as UserID, COUNT(t.ID) AS Count, ul.PermissionID FROM torrents t  
         LEFT JOIN users_levels ul ON ul.PermissionID = $UploaderPermissionID and ul.UserID =  t.UserID
         WHERE t.Time >= '$promoteFromY-$promoteFromM-1' AND t.Time < '$promoteToY-$promoteToM-1' 
         GROUP By UserID 
         HAVING Count >= $PromoteUploadCount AND PermissionID is null";
    G::$DB->query($sql);
    $Ret = G::$DB->to_array(false, MYSQLI_ASSOC);
    $PromotedUserID = [];
    foreach ($Ret as $Data) {
        Users::add_secondary_class($Data['UserID'], [$UploaderPermissionID]);
        echo "Add Uploader secondary class to $UserID";
        $PromotedUserID[] = $Data['UserID'];
    }
    // demote
    $DemoteUploadCount = 2 * CONFIG['SecondaryClassAwardConfig']['UP_UPLOAD_COUNT'];
    $sql =
        "SELECT t.UserID as UserID, COUNT(DISTINCT t.ID) AS Count, ul.PermissionID FROM torrents t
     RIGHT JOIN users_levels ul ON ul.PermissionID = $UploaderPermissionID and ul.UserID =  t.UserID
     WHERE t.Time >= '$demoteFromY-$demoteFromM-1' AND t.Time < '$demoteToY-$demoteToM-1'
     GROUP By UserID 
     HAVING Count < $DemoteUploadCount AND PermissionID is not null";
    G::$DB->query($sql);
    $Ret = G::$DB->to_array(false, MYSQLI_ASSOC);
    foreach ($Ret as $Data) {
        if (in_array($Data['UserID'], $PromotedUserID)) {
            continue;
        }
        Users::remove_secondary_class($Data['UserID'], [$UploaderPermissionID]);
        echo "Drop Uploader secondary class from $UserID";
    }
}

// Seeder
$PromoteSeedCount = CONFIG['SecondaryClassAwardConfig']['SD_SEED_SIZE'];
$SeederPermissionID = CONFIG['SecondaryClassAwardConfig']['SEEDER_PERMISSION_ID'];
if ($PromoteSeedCount > 0 && !empty($SeederPermissionID)) {
    // promote
    $sql = "SELECT xfu.uid AS UserID,  ul.PermissionID, floor(IFNULL(SUM(t.SIZE), 0) / 1024 / 1024 / 1024) AS Size FROM xbt_files_users xfu
             LEFT JOIN users_levels ul ON ul.UserID = xfu.uid AND ul.PermissionID = $SeederPermissionID
             RIGHT JOIN torrents t ON xfu.fid = t.ID
             WHERE active = 1 AND remaining = 0 AND mtime > Unix_timestamp(Now() - interval 1 hour)
             GROUP BY UserID
             Having Size >= $PromoteSeedCount AND PermissionID is null";
    G::$DB->query($sql);
    $Ret = G::$DB->to_array(false, MYSQLI_ASSOC);
    foreach ($Ret as $Data) {
        Users::add_secondary_class($Data['UserID'], [$SeederPermissionID]);
        echo "Add Seeder secondary class to $UserID";
    }
    // demote
    $DemoteSeedCount = 2 * CONFIG['SecondaryClassAwardConfig']['SD_SEED_SIZE'];
    $sql = "SELECT xfu.uid AS UserID,  ul.PermissionID, floor(IFNULL(SUM(t.SIZE), 0) / 1024 / 1024 / 1024) AS Size FROM xbt_files_users xfu
             RIGHT JOIN users_levels ul ON ul.UserID = xfu.uid AND ul.PermissionID = $SeederPermissionID
             RIGHT JOIN torrents t ON xfu.fid = t.ID
             WHERE active = 1 AND remaining = 0 AND mtime > Unix_timestamp(Now() - interval 1 hour)
             GROUP BY UserID
             Having Size < $DemoteSeedCount AND PermissionID is not null";
    G::$DB->query($sql);
    $Ret = G::$DB->to_array(false, MYSQLI_ASSOC);
    foreach ($Ret as $Data) {
        Users::remove_secondary_class($Data['UserID'], [$SeederPermissionID]);
        echo "Drop Seeder secondary class from $UserID";
    }
}

// check rewards
$rewardManager = new Reward;
if (!empty($TIPermissionID) && CONFIG['SecondaryClassAwardConfig']['TI_SALARY'] > 0) {
    $sql =
        "SELECT tc.UserID as UserID, COUNT(DISTINCT torrentid) AS Count FROM torrents_check tc  
    RIGHT JOIN users_levels ul ON ul.PermissionID = $TIPermissionID and ul.UserID =  tc.UserID
    WHERE tc.Time >= '$promoteFromY-$promoteFromM-1' AND tc.Time < '$promoteToY-$promoteToM-1' AND tc.Type = 1 
    GROUP By UserID";
    G::$DB->query($sql);
    $Ret = G::$DB->to_array(false, MYSQLI_ASSOC);
    foreach ($Ret as $Data) {
        $RewardInfo = new RewardInfo;
        $RewardInfo->bonus =  CONFIG['SecondaryClassAwardConfig']['TI_SALARY'] * $Data['Count'];
        $rewardManager->sendReward($RewardInfo, [$Data['UserID']], "TI salary.", true, true);
    }
}
// upload rewards
if (!empty($UploaderPermissionID) && CONFIG['SecondaryClassAwardConfig']['UPLOADER_SALARY'] > 0) {
    $sql =
        "SELECT t.UserID as UserID, COUNT(t.ID) AS Count FROM torrents t  
 RIGHT JOIN users_levels ul ON ul.PermissionID = $UploaderPermissionID and ul.UserID =  t.UserID
 WHERE t.Time >= '$promoteFromY-$promoteFromM-1' AND t.Time < '$promoteToY-$promoteToM-1' 
 GROUP By UserID";
    G::$DB->query($sql);
    $Ret = G::$DB->to_array(false, MYSQLI_ASSOC);
    foreach ($Ret as $Data) {
        $RewardInfo = new RewardInfo;
        $RewardInfo->bonus =  CONFIG['SecondaryClassAwardConfig']['UPLOADER_SALARY'] * $Data['Count'];
        $rewardManager->sendReward($RewardInfo, [$Data['UserID']], "Uploader salary.", true, true);
    }
}

// seed reward
if (!empty($SeederPermissionID) && CONFIG['SecondaryClassAwardConfig']['SEEDER_SALARY'] > 0) {
    $sql = "SELECT xfu.uid AS UserID, floor(IFNULL(SUM(t.SIZE), 0) / 1024 / 1024 / 1024) AS Size FROM xbt_files_users xfu
RIGHT JOIN users_levels ul ON ul.UserID = xfu.uid AND ul.PermissionID = $SeederPermissionID
RIGHT JOIN torrents t ON xfu.fid = t.ID
WHERE active = 1 AND remaining = 0 AND mtime > Unix_timestamp(Now() - interval 1 hour)
GROUP BY UserID";
    G::$DB->query($sql);
    $Ret = G::$DB->to_array(false, MYSQLI_ASSOC);
    foreach ($Ret as $Data) {
        $RewardInfo = new RewardInfo;
        $RewardInfo->bonus =  CONFIG['SecondaryClassAwardConfig']['SEEDER_SALARY'] * $Data['Size'];
        $rewardManager->sendReward($RewardInfo, [$Data['UserID']], "Seeder salary.", true, true);
    }
}
