<?
enforce_login();
switch ($_REQUEST['action']) {
    case 'users':
        include(SERVER_ROOT . '/sections/stats/users.php');
        break;
    case 'torrents':
        include(SERVER_ROOT . '/sections/stats/torrents.php');
        break;
    default:
        include(SERVER_ROOT . '/sections/stats/browse.php');
        break;
}
