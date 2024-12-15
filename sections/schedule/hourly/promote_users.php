<?php

//------------- Promote users -------------------------------------------//

use Gazelle\Manager\Reward;
use Gazelle\Action\RewardInfo;

sleep(5);
$DeadPeriod = TORRENT_DEAD_PERIOD;
foreach ($UserPromoteCriteria as $L) { // $L = Level
    $Query = "
				SELECT ID
				FROM users_main AS um
					JOIN users_info ON um.ID = users_info.UserID
                LEFT JOIN(
                    SELECT UserID,
                       SUM(Downloaded) AS Downloaded
                    FROM
                        users_freetorrents
                    GROUP BY
                        UserID
                ) AS ft 
                ON
                    um.ID = ft.UserID
                LEFT JOIN(
                    SELECT UserID,
                       SUM(Downloaded) AS Downloaded
                    FROM
                        users_freeleeches
                    GROUP BY
                        UserID
                ) AS fl
                ON
                    um.ID = fl.UserID
				WHERE PermissionID = " . $L['From'] . "
					AND Warned = '0000-00-00 00:00:00'
					AND Uploaded >= '$L[MinUpload]'
					AND um.Downloaded + IFNULL(ft.Downloaded,0) + IFNULL(fl.Downloaded, 0) >= '$L[MinDownload]'
					AND (um.Uploaded / um.Downloaded >= '$L[MinRatio]' OR (um.Uploaded / um.Downloaded IS NULL))
					AND JoinDate < now() - INTERVAL '$L[Weeks]' WEEK
					AND (
						SELECT COUNT(ID)
						FROM torrents
						WHERE UserID = um.ID and date_sub(NOW(), INTERVAL $DeadPeriod DAY) < last_action
						) >= '$L[MinUploads]'
					AND Enabled = '1'";
    if (!empty($L['Extra'])) {
        $Query .= ' AND ' . $L['Extra'];
    }

    $DB->query($Query);

    $UserIDs = $DB->collect('ID');

    if (count($UserIDs) > 0) {
        $DB->query(
            "UPDATE users_main
				SET PermissionID = " . $L['To'] . "
				WHERE ID IN(" . implode(',', $UserIDs) . ')'
        );
        foreach ($UserIDs as $UserID) {
            /*$Cache->begin_transaction("user_info_$UserID");
            $Cache->update_row(false, array('PermissionID' => $L['To']));
            $Cache->commit_transaction(0);*/
            $Cache->delete_value("user_info_$UserID");
            $Cache->delete_value("user_info_heavy_$UserID");
            $Cache->delete_value("user_stats_$UserID");
            $Cache->delete_value("enabled_$UserID");
            $DB->query(
                "UPDATE users_info
					SET AdminComment = CONCAT('" . sqltime() . " - Class changed to " . Users::make_class_string($L['To']) . " by System\n\n', AdminComment)
					WHERE UserID = $UserID"
            );
            if ($L['Invite']) {
                $DB->query("select AwardLevel from users_main where ID=$UserID and AwardLevel < " . $L['AwardLevel']);
                $AwardLevel = $DB->collect('AwardLevel');
                if (count($AwardLevel) > 0) {
                    $DB->query("UPDATE users_main SET AwardLevel = " . $L['AwardLevel']  . " WHERE ID = $UserID");
                    $rewardManager = new Reward;
                    $reward = new RewardInfo;
                    $reward->inviteCount = $L['Invite'];
                    $rewardManager->sendReward($reward, [$UserID], "Level up reward.", false, true);
                }
            }
            Misc::send_pm_with_tpl($UserID, 'promote_users', ['UserClass' => Users::make_class_string($L['To']), 'CONFIG' => CONFIG]);
        }
    }
}
