<?
enforce_login();

if (!check_perms('site_top10')) {
    View::show_header('', '', 'PageTop10Index');
?>
    <div class="content_basiccontainer">
        <?= t('server.top10.you_do_not_have_access_to_view_this_feature') ?>
    </div>
<?
    View::show_footer();
    die();
}

include(CONFIG['SERVER_ROOT'] . '/sections/torrents/functions.php'); //Has get_reports($TorrentID);
if (empty($_GET['type']) || $_GET['type'] == 'torrents') {
    include(CONFIG['SERVER_ROOT'] . '/sections/top10/torrents.php');
} else {
    switch ($_GET['type']) {
        case 'movies':
            include(CONFIG['SERVER_ROOT'] . '/sections/top10/browse.php');
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
?>