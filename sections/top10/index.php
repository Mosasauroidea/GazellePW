<?
enforce_login();

if (!check_perms('site_top10')) {
    error(403);
}

include(CONFIG['SERVER_ROOT'] . '/sections/torrents/functions.php'); //Has get_reports($TorrentID);
if (empty($_GET['type']) || $_GET['type'] == 'movies') {
    include(CONFIG['SERVER_ROOT'] . '/sections/top10/browse.php');
} else {
    switch ($_GET['type']) {
        case 'torrents':
            include(CONFIG['SERVER_ROOT'] . '/sections/top10/torrents.php');
            break;
        case 'users':
            include(CONFIG['SERVER_ROOT'] . '/sections/top10/users.php');
            break;
        case 'tags':
            include(CONFIG['SERVER_ROOT'] . '/sections/top10/tags.php');
            break;
        case 'history':
            include(CONFIG['SERVER_ROOT'] . '/sections/top10/history.php');
            break;
        case 'original':
            include(CONFIG['SERVER_ROOT'] . '/sections/top10/original.php');
            break;
        case 'votes':
            include(CONFIG['SERVER_ROOT'] . '/sections/top10/votes.php');
            break;
        case 'donors':
            include(CONFIG['SERVER_ROOT'] . '/sections/top10/donors.php');
            break;
        default:
            error(404);
            break;
    }
}
