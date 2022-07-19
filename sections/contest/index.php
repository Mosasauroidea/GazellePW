<?
enforce_login();

if (isset($_GET['action'])) {
    switch ($_GET['action']) {
        case 'leaderboard':
            include(CONFIG['SERVER_ROOT'] . '/sections/contest/leaderboard.php');
            break;
        case 'admin':
            include(CONFIG['SERVER_ROOT'] . '/sections/contest/admin.php');
            break;
    }
} else {
    include(CONFIG['SERVER_ROOT'] . '/sections/contest/intro.php');
}
