<?
//users_edit_profiles
if (check_perms('users_edit_profiles')) {
    $DB->query("update users_info set TGID=NULL where UserID=" . intval($_GET['userid']));
    $Cache->delete("user_info_heavy_" . intval($_GET['userid']));
} else {
    $DB->query("update users_info set TGID=NULL where UserID=" . $LoggedUser['ID']);
    $Cache->delete("user_info_heavy_" . $LoggedUser['ID']);
}
