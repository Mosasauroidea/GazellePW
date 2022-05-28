<?php

//------------- Delete dead torrents ------------------------------------//
sleep(10);
$Criteria = array();
$Criteria[] = array('ddt' => 500 * 250 * 1024 * 1024, 'tdt' => 500, 'token' => 50, 'bonus' => 6000);
$Criteria[] = array('ddt' => 320 * 250 * 1024 * 1024, 'tdt' => 320, 'token' => 32, 'bonus' => 2800);
$Criteria[] = array('ddt' => 200 * 250 * 1024 * 1024, 'tdt' => 200, 'token' => 20, 'bonus' => 1300);
$Criteria[] = array('ddt' => 120 * 250 * 1024 * 1024, 'tdt' => 120, 'token' => 12, 'bonus' => 600);
$Criteria[] = array('ddt' => 60 * 250 * 1024 * 1024, 'tdt' => 60, 'token' => 6, 'bonus' => 240);
$Criteria[] = array('ddt' => 25 * 250 * 1024 * 1024, 'tdt' => 25, 'token' => 2, 'bonus' => 100);
$Criteria[] = array('ddt' => 10 * 250 * 1024 * 1024, 'tdt' => 10, 'token' => 1, 'bonus' => 0);
/*
$DB->query("select u.ID, u.Downloaded ND, l.Downloaded LD, TorrentCnt LT from users_main as u left join users_last_month as l on u.ID=l.ID");

$Users = $DB->to_array(false, MYSQLI_NUM, false);
foreach ($Users as $User) {
    list($ID, $ND, $LD, $LT) = $User;
    $DB->query("SELECT COUNT(DISTINCT x.fid)
    FROM xbt_snatched AS x
            INNER JOIN torrents AS t ON t.ID = x.fid
        WHERE x.uid = '$ID'");
    $TorrentCnt = $DB->to_array(false, MYSQLI_NUM, false);
    $NT = $TorrentCnt[0][0];
    if ($LD == 0 && $ND != 0) {
        $DB->query("insert into users_last_month VALUES ($ID,$ND,$NT)");
    }
    if ($ND != $LD) {
        $DB->query("update users_last_month set Downloaded='$ND',TorrentCnt='$NT' where ID=$ID");
        foreach ($Criteria as $L) {
            if ($ND - $LD >= $L['ddt'] && $NT - $LT >= $L['tdt']) {
                $DB->query("UPDATE users_main SET FLTokens = FLTokens + ".$L['token'].",BonusPoints = BonusPoints + ".$L['bonus']." WHERE ID = $ID");
                for ($i = 0; $i < $L['token']; $i ++) {
                    $DB->query("INSERT into tokens_typed (`UserID`,`EndTime`,`Type`) VALUES ($ID,DATE_FORMAT(date_sub(date_add(now(), interval 1 month), interval 1 day),'%Y-%m-%d'),'time')");
                }
                $Cache->delete_value('user_info_heavy_'.$ID);
                $Cache->delete_value('user_stats_'.$ID);
                Misc::send_pm($ID, 0, "本月奖励", "你拿到了 ".$L['tdt']." 个种子的奖励：".$L['token']." 枚令牌".($L['bonus']? $L['bonus']." 积分。": "。"));
                break;
            }
        }
    }
}
*/
$DB->query("SELECT xs.uid, xs.tstamp, xs.fid, t.Size 
                FROM xbt_snatched AS xs left join torrents AS t ON t.ID = xs.fid 
                WHERE xs.tstamp >= unix_timestamp(date_format(date_sub(now(), interval 1 month),'%Y-%m-01')) order by 2");
$Requests = $DB->to_array();
$SnatchedByUser;
foreach ($Requests as $Request) {
    list($UserID, $Time, $TorrentID, $Size) = $Request;
    if (!isset($SnatchedByUser[$UserID][$TorrentID])) {
        $SnatchedByUser[$UserID][$TorrentID]['size'] = $Size;
        $SnatchedByUser[$UserID][$TorrentID]['free'] = 0;
        $SnatchedByUser[$UserID][$TorrentID]['unfree'] = 0;
    }
    $SnatchedByUser[$UserID][$TorrentID]['time'][] = $Time;
}
$DB->query("SELECT `UserID`, `TorrentID`, unix_timestamp(`Time`) 
                FROM `users_freeleeches_time` 
                WHERE unix_timestamp(Time) >= unix_timestamp(date_format(date_sub(now(), interval 1 month),'%Y-%m-01')) order by 3");
$Requests = $DB->to_array();
$TokenByUser;
foreach ($Requests as $Request) {
    list($UserID, $TorrentID, $Time) = $Request;
    $TokenByUser[$UserID][$TorrentID][] = $Time;
}
$UsersCnt;
foreach ($SnatchedByUser as $UserID => &$Torrents) {
    $UsersCnt[$UserID]['size'] = 0;
    $UsersCnt[$UserID]['cnt'] = 0;
    foreach ($Torrents as $TorrentID => &$Torrent) {
        if (isset($TokenByUser[$UserID][$TorrentID])) {
            foreach ($Torrent['time'] as $Time) {
                $free = false;
                foreach ($TokenByUser[$UserID][$TorrentID] as $key => $TokenTime) {
                    if ($Time < $TokenTime + 345600) {
                        unset($TokenByUser[$UserID][$TorrentID][$key]);
                        $Torrent['free'] += 1;
                        $free = true;
                        break;
                    }
                }
                if (!$free) {
                    $Torrent['unfree'] += 1;
                }
            }
        } else {
            $Torrent['unfree'] = 1;
        }
    }
}
$DB->query("select u.ID, u.Downloaded ND, l.Downloaded LD, TorrentCnt LT from users_main as u left join users_last_month as l on u.ID=l.ID");
$Requests = $DB->to_array();
foreach ($Requests as $User) {
    list($ID, $ND, $LD, $LT) = $User;
    if ($LD == 0 && $ND != 0) {
        $DB->query("insert ignore into users_last_month VALUES ($ID,$ND,0)");
    }
    if ($ND != $LD) {
        $DB->query("update users_last_month set Downloaded='$ND',TorrentCnt=0 where ID=$ID");
    }
    $UsersCnt[$ID]['dt'] = $ND - $LD;
}
unset($Torrents, $Torrent);
foreach ($SnatchedByUser as $UserID => $Torrents) {
    foreach ($Torrents as $Torrent) {
        if ($Torrent['unfree']) {
            $UsersCnt[$UserID]['size'] += $Torrent['size'];
            if ($Torrent['size']) {
                $UsersCnt[$UserID]['cnt']++;
            }
        }
    }
}
// foreach ($UsersCnt as $UserID => $User) {
//     $LogSize = min ($User['size'], $User['dt']);
//     foreach ($Criteria as $L) {
//         if ($LogSize >= $L['ddt'] && $User['cnt'] >= $L['tdt']) {
//             $DB->query("UPDATE users_main SET FLTokens = FLTokens + ".$L['token'].",BonusPoints = BonusPoints + ".$L['bonus']." WHERE ID = $UserID");
//             for ($i = 0; $i < $L['token']; $i++) {
//                 $DB->query("INSERT into tokens_typed (`UserID`,`EndTime`,`Type`) VALUES ($UserID,DATE_FORMAT(date_sub(date_add(now(), interval 1 month), interval 1 day),'%Y-%m-%d'),'time')");
//             }
//             $Cache->delete_value('user_info_heavy_'.$UserID);
//             $Cache->delete_value('user_stats_'.$UserID);
//             Misc::send_pm($UserID, 0, "本月奖励", "你拿到了 ".$L['tdt']." 个种子的奖励：".$L['token']." 枚令牌".($L['bonus']? "和 ".$L['bonus']." 积分。": "。"));
//             break;
//         }
//     }
// }
