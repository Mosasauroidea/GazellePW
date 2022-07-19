<?
enforce_login();
switch ($_REQUEST['action']) {
    case 'users':
        include(CONFIG['SERVER_ROOT'] . '/sections/stats/users.php');
        break;
    case 'torrents':
        include(CONFIG['SERVER_ROOT'] . '/sections/stats/torrents.php');
        break;
    default:
        include(CONFIG['SERVER_ROOT'] . '/sections/stats/browse.php');
        break;
}
