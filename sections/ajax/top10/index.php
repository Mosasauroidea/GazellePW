<?
// Already done in /sections/ajax/index.php
//enforce_login();

if (!check_perms('site_top10')) {
    print json_encode(array('status' => 'failure'));
    die();
}


if (empty($_GET['type']) || $_GET['type'] == 'torrents') {
    include(CONFIG['SERVER_ROOT'] . '/sections/ajax/top10/torrents.php');
} else {
    switch ($_GET['type']) {
        case 'users':
            include(CONFIG['SERVER_ROOT'] . '/sections/ajax/top10/users.php');
            break;
        case 'tags':
            include(CONFIG['SERVER_ROOT'] . '/sections/ajax/top10/tags.php');
            break;
        case 'history':
            include(CONFIG['SERVER_ROOT'] . '/sections/ajax/top10/history.php');
            break;
        default:
            print json_encode(array('status' => 'failure'));
            break;
    }
}
