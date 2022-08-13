<?

/**************************************************************************
Artists Switch Center

This page acts as a switch that includes the real artist pages (to keep
the root less cluttered).

enforce_login() is run here - the entire artist pages are off limits for
non members.
 ****************************************************************************/

// Width and height of similar artist map
define('WIDTH', 585);
define('HEIGHT', 400);

enforce_login();

if (!empty($_POST['action'])) {
    switch ($_POST['action']) {
        case 'edit':
            require(CONFIG['SERVER_ROOT'] . '/sections/artist/takeedit.php');
            break;
        case 'download':
            require(CONFIG['SERVER_ROOT'] . '/sections/artist/download.php');
            break;
        case 'add_similar':
            require(CONFIG['SERVER_ROOT'] . '/sections/artist/add_similar.php');
            break;
        case 'add_alias':
            require(CONFIG['SERVER_ROOT'] . '/sections/artist/add_alias.php');
            break;
        case 'change_artistid':
            require(CONFIG['SERVER_ROOT'] . '/sections/artist/change_artistid.php');
            break;
        case 'concert_thread':
            include(CONFIG['SERVER_ROOT'] . '/sections/artist/concert_thread.php');
            break;
        case 'take_concert_thread':
            include(CONFIG['SERVER_ROOT'] . '/sections/artist/take_concert_thread.php');
            break;
        case 'takeeditrequest':
            include(CONFIG['SERVER_ROOT'] . '/sections/artist/takeeditrequest.php');
            break;
        default:
            error(0);
    }
} elseif (!empty($_GET['action'])) {
    switch ($_GET['action']) {
        case 'autocomplete':
            require('sections/artist/autocomplete.php');
            break;

        case 'edit':
            require(CONFIG['SERVER_ROOT'] . '/sections/artist/edit.php');
            break;
        case 'delete':
            require(CONFIG['SERVER_ROOT'] . '/sections/artist/delete.php');
            break;
        case 'revert':
            require(CONFIG['SERVER_ROOT'] . '/sections/artist/takeedit.php');
            break;
        case 'history':
            require(CONFIG['SERVER_ROOT'] . '/sections/artist/history.php');
            break;
        case 'vote_similar':
            require(CONFIG['SERVER_ROOT'] . '/sections/artist/vote_similar.php');
            break;
        case 'delete_similar':
            require(CONFIG['SERVER_ROOT'] . '/sections/artist/delete_similar.php');
            break;
        case 'similar':
            require(CONFIG['SERVER_ROOT'] . '/sections/artist/similar.php');
            break;
        case 'similar_bg':
            require(CONFIG['SERVER_ROOT'] . '/sections/artist/similar_bg.php');
            break;
        case 'notify':
            require(CONFIG['SERVER_ROOT'] . '/sections/artist/notify.php');
            break;
        case 'notifyremove':
            require(CONFIG['SERVER_ROOT'] . '/sections/artist/notifyremove.php');
            break;
        case 'delete_alias':
            require(CONFIG['SERVER_ROOT'] . '/sections/artist/delete_alias.php');
            break;
        case 'change_artistid':
            require(CONFIG['SERVER_ROOT'] . '/sections/artist/change_artistid.php');
            break;
        case 'editrequest':
            require(CONFIG['SERVER_ROOT'] . '/sections/artist/editrequest.php');
            break;
        default:
            error(0);
            break;
    }
} else {
    if (!empty($_GET['id'])) {

        include(CONFIG['SERVER_ROOT'] . '/sections/artist/browse.php');
    } elseif (!empty($_GET['artistname'])) {

        $NameSearch = str_replace('\\', '\\\\', trim($_GET['artistname']));
        $DB->query("
			SELECT ArtistID, Name
			FROM artists_alias
			WHERE Name LIKE '" . db_string($NameSearch) . "'");
        if (!$DB->has_results()) {
            header('Location: torrents.php?action=advanced&artistname=' . urlencode($_GET['artistname']));
            die();
        }
        list($FirstID, $Name) = $DB->next_record(MYSQLI_NUM, false);
        if ($DB->record_count() === 1 || !strcasecmp($Name, $NameSearch)) {
            header("Location: artist.php?id=$FirstID");
            die();
        }
        while (list($ID, $Name) = $DB->next_record(MYSQLI_NUM, false)) {
            if (!strcasecmp($Name, $NameSearch)) {
                header("Location: artist.php?id=$ID");
                die();
            }
        }
        header("Location: artist.php?id=$FirstID");
        die();
    } else {
        header('Location: torrents.php');
    }
}
