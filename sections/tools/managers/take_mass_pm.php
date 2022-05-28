<?
set_time_limit(0);

authorize();

if (!check_perms("users_mod")) {
    error(403);
}

if (!is_number($_POST['class_id']) || empty($_POST['subject']) || empty($_POST['body'])) {
    error("Error in message form");
}

$PermissionID = $_POST['class_id'];
$Subject = $_POST['subject'];
$Body = $_POST['body'];
$FromID = empty($_POST['from_system']) ? G::$LoggedUser['ID'] : 0;
if ($PermissionID == '99') {
    G::$DB->query("SELECT ID AS UserID FROM users_main WHERE `Enabled` != '2' AND ID != '$FromID'");
} else if ($PermissionID == '100') {
    G::$DB->query("SELECT id from users_main where permissionid in (SELECT id FROM `permissions` WHERE `Level` > 750) and ID != '$FromID'");
} else {
    G::$DB->query("(SELECT ID AS UserID FROM users_main WHERE PermissionID = '$PermissionID' AND ID != '$FromID') UNION (SELECT UserID FROM users_levels WHERE PermissionID = '$PermissionID' AND UserID != '$FromID')");
}

while (list($UserID) = G::$DB->next_record()) {
    Misc::send_pm($UserID, $FromID, $Subject, $Body);
}

header("Location: tools.php");
