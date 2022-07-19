<?
$P = db_array($_POST);
enforce_login();
if (!empty($_REQUEST['friendid']) && !is_number($_REQUEST['friendid'])) {
    error(404);
}

if (!empty($_REQUEST['action'])) {
    switch ($_REQUEST['action']) {
        case 'add':
            include(CONFIG['SERVER_ROOT'] . '/sections/friends/add.php');
            break;
        case 'Remove friend':
            authorize();
            include(CONFIG['SERVER_ROOT'] . '/sections/friends/remove.php');
            break;
        case 'Update':
            authorize();
            include(CONFIG['SERVER_ROOT'] . '/sections/friends/comment.php');
            break;

        case 'Contact':
            header('Location: inbox.php?action=compose&to=' . $_POST['friendid']);
            break;
        default:
            error(404);
    }
} else {
    include(CONFIG['SERVER_ROOT'] . '/sections/friends/friends.php');
}
