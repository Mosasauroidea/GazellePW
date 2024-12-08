<?
function queryUsersByGroup($GroupName) {
    $sql = "select ID from users_main where PermissionID=(select ID from permissions where name='$GroupName')
        UNION select userid from users_levels where PermissionID=(select ID from permissions where name='$GroupName')";
    G::$DB->query($sql);
    return G::$DB->to_array(false, MYSQLI_ASSOC, false);
}

function getAwardData($GroupName, $Time) {
    $data = ['GroupName' => $GroupName, 'Users' => []];
    $Users = queryUsersByGroup($GroupName);
    foreach ($Users as $User) {
        $EditCnt = getEditCount($User['ID'], $Time);
        $data['Users'][] = [
            'UserID' => $User['ID'],
            'DownloadCount' => getDownloadCount($User['ID'], $Time),
            'UploadCount' => getUploadCount($User['ID'], $Time),
            'CheckCount' => getCheckCount($User['ID'], $Time),
            'RSReportCount' => getRSReportCount($User['ID'], $Time),
            'RPReportCount' => getRPReportCount($User['ID'], $Time),
            'EditCount' => $EditCnt,
            'PostCount' => getPostCount($User['ID'], $Time) - $EditCnt,
            'SendJF' => getSendJFCount($User['ID'], $Time),
            'ApplyCount' => getApplyCount($User['ID'], $Time),
            'TGCount' => 0
        ];
    }
    return $data;
}

function printYearTR($StartYear = 2021, $focus) {
    $now = date("Y");
    echo "<tr><th>年份：</th><td>";
    for ($y = $StartYear; $y <= $now; $y++) {
        echo $focus == $y ? "<span>$y</span>　" : "<a href=\"tools.php?action=award&year=$y\">$y</a>　";
    }
    echo "</td></tr>";
}
function printQuarterTR($Year, $focus) {
    $nowYear = date("Y");
    $nowMonth = date("n");
    $QUARTER = array("", "Q1", "Q2", "Q3", "Q4");
    echo "<tr><th>季度：</th><td>";
    if ($Year < $nowYear) {
        $nowMonth = 12;
    }
    for ($q = 1; $nowMonth > 0; $nowMonth -= 3, $q++) {
        echo $focus == $q ? "<span>$QUARTER[$q]</span>　" : "<a href=\"tools.php?action=award&year=$Year&quarter=$q\">$QUARTER[$q]</a>　";
    }
    echo "</td></tr>";
}
function printMonthTR($Year, $focus) {
    $nowYear = date("Y");
    $nowMonth = date("n");
    echo "<tr><th>月份：</th><td>";
    if ($Year < $nowYear) {
        $nowMonth = 12;
    }
    for ($m = 1; $m <= $nowMonth; $m++) {
        echo $focus == $m ? "<span>$m</span>　" : "<a href=\"tools.php?action=award&year=$Year&month=$m\">$m</a>　";
    }
    echo "</td></tr>";
}
function timeConvert($Year, $Quarter, $Month) {
    $fromY = $Year;
    $fromM = $Quarter ? ($Quarter - 1) * 3 + 1 : ($Month ? $Month : 1);
    $toY = ($Quarter == 4 || $Month == 12 || (!$Quarter && !$Month)) ? $fromY + 1 : $fromY;
    $toM = $Quarter ? ($Quarter * 3) % 12 + 1 : ($Month ? $Month % 12 + 1 : 1);
    return  array($fromY, $fromM, $toY, $toM);
}
function getCheckCount($UserID, $Time) {
    list($fromY, $fromM, $toY, $toM) = $Time;
    $sql =
        "
select count(*) 
from 
	torrents t, 
	(select torrentid 
   	from torrents_check
    where 
       userid=$UserID and type=1 
       and '$fromY-$fromM-1' <= time and time < '$toY-$toM-1'
    group by torrentid) tc
where tc.torrentid=t.id and t.checked=$UserID";
    G::$DB->query($sql);
    $ret = G::$DB->next_record();
    return $ret[0];
}
function getRSReportCount($UserID, $Time) {
    list($fromY, $fromM, $toY, $toM) = $Time;
    $sql =
        "SELECT
    count(*)
FROM reportsv2 
where ResolverID = $UserID 
    and '$fromY-$fromM-1' <= LastChangeTime 
    and LastChangeTime < '$toY-$toM-1'";
    G::$DB->query($sql);
    $ret = G::$DB->next_record();
    return $ret[0];
}
function getRPReportCount($UserID, $Time) {
    list($fromY, $fromM, $toY, $toM) = $Time;
    $sql =
        "SELECT
    count(*)
FROM reportsv2 
where ReporterID = $UserID 
    and '$fromY-$fromM-1' <= LastChangeTime 
    and LastChangeTime < '$toY-$toM-1'";
    G::$DB->query($sql);
    $ret = G::$DB->next_record();
    return $ret[0];
}
function getEditCount($UserID, $Time) {
    list($fromY, $fromM, $toY, $toM) = $Time;
    $sql =
        "SELECT count(*) 
FROM `forums_posts` 
WHERE 
    topicid in (
        select id from forums_topics 
        where forumid=34 
        and '$fromY-$fromM-1' <= lastposttime and lastposttime < '$toY-$toM-1'
    ) 
    and authorid=$UserID";
    G::$DB->query($sql);
    $ret = G::$DB->next_record();
    return $ret[0];
}
function getSendJFCount($UserID, $Time) {
    list($fromY, $fromM, $toY, $toM) = $Time;
    $sql =
        "SELECT
    count(*)
FROM forums_posts_jf_log 
where sentuid = $UserID 
    and '$fromY-$fromM-1' <= logtime 
    and logtime < '$toY-$toM-1'
    and sys=1";
    G::$DB->query($sql);
    $ret = G::$DB->next_record();
    return $ret[0];
}
function getPostCount($UserID, $Time) {
    list($fromY, $fromM, $toY, $toM) = $Time;
    $sql =
        "SELECT count(*) 
FROM `forums_posts` 
WHERE 
    '$fromY-$fromM-1' <= addedtime and addedtime < '$toY-$toM-1'
    and authorid=$UserID";
    G::$DB->query($sql);
    $ret = G::$DB->next_record();
    return $ret[0];
}
function getUploadCount($UserID, $Time) {
    list($fromY, $fromM, $toY, $toM) = $Time;
    $sql =
        "SELECT count(*) 
FROM `torrents` 
WHERE 
    '$fromY-$fromM-1' <= time and time < '$toY-$toM-1'
    and userid=$UserID";
    G::$DB->query($sql);
    $ret = G::$DB->next_record();
    return $ret[0];
}
function getDownloadCount($UserID, $Time) {
    list($fromY, $fromM, $toY, $toM) = $Time;
    $sql =
        "SELECT count(*)
FROM xbt_snatched 
WHERE 
    uid = $UserID 
    and unix_timestamp('$fromY-$fromM-1') <= tstamp
    and tstamp < unix_timestamp('$toY-$toM-1')";
    G::$DB->query($sql);
    $ret = G::$DB->next_record();
    return $ret[0];
}
function getApplyCount($UserID, $Time) {
    list($fromY, $fromM, $toY, $toM) = $Time;
    if ($fromY == 2019) {
        $sql =
            "SELECT count(*)
        FROM register_apply 
        WHERE 
            id_mod = $UserID 
            and '$fromY-$fromM-1' <= ts_mod
            and ts_mod < '$toY-$toM-1'
            and (apply_status=1 or apply_status=2)";
    } else {
        $sql =
            "SELECT count(*)
        FROM register_apply_log
        WHERE
            UserID = $UserID 
            and '$fromY-$fromM-1' <= Time
            and Time < '$toY-$toM-1'
            and (ApplyStatus=1 or ApplyStatus=2 or ApplyStatus=3)";
    }
    G::$DB->query($sql);
    $ret = G::$DB->next_record();
    return $ret[0];
}

function makePoint(&$AwardDatas, $Bases, $PointRadios, &$MaxValue) {
    foreach ($AwardDatas as &$data) {
        foreach ($data['Users'] as &$User) {
            $User['Base'] = isset($Bases[$data['GroupName']]) ? $Bases[$data['GroupName']] : 0;
            $User['Point'] = 0;
            foreach ($User as $ColName => $Col) {
                $User['Point'] += $Col * (isset($PointRadios[$ColName]) ? $PointRadios[$ColName] : 0);
                if ($MaxValue[$ColName] < $Col) {
                    $MaxValue[$ColName] = $Col;
                }
            }
            if ($MaxValue['Point'] < $User['Point']) {
                $MaxValue['Point'] = $User['Point'];
            }
            $User['Brokerage'] = 50 * $User['Point'];
            if ($User['Point'] < 50) {
                $User['Salary'] = $User['Base'] * 4;
                $User['Gear'] = 1;
            } else if ($User['Point'] < 150) {
                $User['Salary'] = $User['Base'] * 4 + $User['Brokerage'] * 1.5;
                $User['Gear'] = 2;
            } else if ($User['Point'] < 300) {
                $User['Salary'] = $User['Base'] * 4 + $User['Brokerage'] * 3;
                $User['Gear'] = 3;
            } else if ($User['Point'] < 500) {
                $User['Salary'] = $User['Base'] * 4 + $User['Brokerage'] * 5;
                $User['Gear'] = 4;
            } else {
                $User['Salary'] = $User['Base'] * 4 + $User['Brokerage'] * 6;
                $User['Gear'] = 5;
            }
        }
    }
}
