<?
/*
if (!check_perms('admin_send_bonus')) {
    echo json_encode(array('ret' => 0,'msg' =>''));
    die();
}
*/
if (!isset($_GET['j']) || (!is_number($_GET['j']) && $_GET['j'] != "") || $_GET['j'] < 0) {
    echo json_encode(array('ret' => 0, 'msg' => ''));
    die();
}
$JF = $_GET['j'];
if ($JF == "") $JF = 0;
if (!isset($_GET['r'])) {
    echo json_encode(array('ret' => 0, 'msg' => ''));
    die();
}
$auth_code = openssl_decrypt(hex2bin($_GET['r']), 'AES-128-CBC', 'hfjs05@^eIU$AfJW', OPENSSL_RAW_DATA, '0000000000000000');
if (!$auth_code) {
    echo json_encode(array('ret' => 0, 'msg' => ''));
    die();
}
if (strlen($_GET['c']) > 100) {
    echo json_encode(array('ret' => 0, 'msg' => ''));
    die();
}
$comment = db_string(trim($_GET['c']));
$auth_code_arr = explode("|", $auth_code);
$JF_uid = $LoggedUser['ID'];
if ($JF_uid != $auth_code_arr[0]) {
    echo json_encode(array('ret' => 0, 'msg' => ''));
    die();
}

$JF_sys = false;
if (isset($_GET['s'])) {
    if (!(check_perms('admin_send_bonus') || isset($LoggedUser['ExtraClasses']['31']))) {
        echo json_encode(array('ret' => 0, 'msg' => ''));
        die();
    }
    $JF_sys = $_GET['s'] == "true";
}
$JF_TopicID = $auth_code_arr[1];
$JF_AuthorID = $auth_code_arr[2];
$JF_PostID = $auth_code_arr[3];
if (isset($auth_code_arr[4])) {
    $delete_ID = $auth_code_arr[4];
    $DB->query("select Sentjf from forums_posts_jf_log where ID=$delete_ID");
    $DB->query("UPDATE `users_main` SET BonusPoints=BonusPoints-" . $DB->next_record()[0] . " WHERE `ID` = '$JF_AuthorID';");
    $DB->query("DELETE FROM `forums_posts_jf_log` WHERE ID=$delete_ID");
    $Cache->delete_value('user_stats_' . $JF_AuthorID);
    Misc::send_pm_with_tpl($JF_AuthorID, 'award_withdrawal', ['JF_TopicID' => $JF_TopicID, 'JF_PostID' => $JF_PostID]);
} else {
    if ($JF_AuthorID == $JF_uid && !$JF_sys) {
        echo json_encode(array('ret' => 0, 'msg' => t('server.forums.you_can_t_award_yourself')));
        die();
    }
    if ($JF == 0 && $comment == "") {
        echo json_encode(array('ret' => 0, 'msg' => ''));
        die();
    }
    if (!$JF_sys) {
        if (!in_array($JF, $ForumBonus)) {
            echo json_encode(array('ret' => 0, 'msg' => ''));
            die();
        }
        $DB->query("select BonusPoints>=$JF from users_main where ID=$JF_uid");
        if ($DB->next_record()[0]) {
            $DB->query("UPDATE `users_main` SET BonusPoints=BonusPoints-$JF WHERE `ID` = '$JF_uid';");
            $Cache->delete_value('user_stats_' . $JF_uid);
        } else {
            echo json_encode(array('ret' => 0, 'msg' => ''));
            die();
        }
    }
    $DB->query("INSERT INTO `forums_posts_jf_log` (`TopicID`, `AuthorID`, `PostID`, `LogTime`, `Sentuid`, `Sentjf`, `Comment`, `Sys`)
    VALUES ('$JF_TopicID', '$JF_AuthorID', '$JF_PostID', now(), '$JF_uid', '$JF', '$comment', " . ($JF_sys ? 1 : 0) . ");");
    if (!$JF_sys) {
        $JF *= 0.9;
    }
    $DB->query("UPDATE `users_main` SET BonusPoints=BonusPoints+$JF WHERE `ID` = '$JF_AuthorID';");
    $Cache->delete_value('user_stats_' . $JF_AuthorID);
    Misc::send_pm_with_tpl($JF_AuthorID, 'post_awarded', ['JF_TopicID' => $JF_TopicID, 'JF_PostID' => $JF_PostID, 'JF' => $JF]);
}
echo json_encode(array('ret' => 'success'));
