<?
authorize();
include(CONFIG['SERVER_ROOT'] . '/sections/user/linkedfunctions.php');

if (!check_perms('users_mod')) {
    error(403);
}

$UserID = (int) $_REQUEST['userid'];

switch ($_REQUEST['dupeaction']) {
    case 'remove':
        unlink_user($_REQUEST['removeid']);
        break;

    case 'update':
        if ($_REQUEST['target']) {
            $Target = $_REQUEST['target'];
            $DB->query("
				SELECT ID
				FROM users_main
				WHERE Username LIKE '" . db_string($Target) . "'");
            if (list($TargetID) = $DB->next_record()) {
                link_users($UserID, $TargetID, (isset($_REQUEST['ignore_comments'])) ? true : false);
            } else {
                error("User '$Target' not found.");
            }
        }

        $DB->query("
			SELECT GroupID
			FROM users_dupes
			WHERE UserID = '$UserID'");
        list($GroupID) = $DB->next_record();

        if ($_REQUEST['dupecomments'] && $GroupID) {
            dupe_comments($GroupID, $_REQUEST['dupecomments'], (isset($_REQUEST['ignore_comments'])) ? true : false);
        }
        break;

    default:
        error(403);
}
echo '\o/';
header("Location: user.php?id=$UserID");
