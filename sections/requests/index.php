<?
enforce_login();

$RequestTax = CONFIG['REQUEST_TAX'];

// Minimum and default amount of upload to remove from the user when they vote.
// Also change in static/functions/requests.js
$MinimumVote = CONFIG['REQUEST_MIN_VOTE'];

if (!empty($LoggedUser['DisableRequests'])) {
    error('Your request privileges have been removed.');
}

if (!isset($_REQUEST['action'])) {
    include(CONFIG['SERVER_ROOT'] . '/sections/requests/browse.php');
} else {
    switch ($_REQUEST['action']) {
        case 'new':
        case 'edit':
            include(CONFIG['SERVER_ROOT'] . '/sections/requests/new_edit.php');
            break;
        case 'takevote':
            include(CONFIG['SERVER_ROOT'] . '/sections/requests/take_vote.php');
            break;
        case 'takefill':
            include(CONFIG['SERVER_ROOT'] . '/sections/requests/take_fill.php');
            break;
        case 'takenew':
        case 'takeedit':
            include(CONFIG['SERVER_ROOT'] . '/sections/requests/take_new_edit.php');
            break;
        case 'delete':
        case 'unfill':
            include(CONFIG['SERVER_ROOT'] . '/sections/requests/interim.php');
            break;
        case 'takeunfill':
            include(CONFIG['SERVER_ROOT'] . '/sections/requests/take_unfill.php');
            break;
        case 'takedelete':
            include(CONFIG['SERVER_ROOT'] . '/sections/requests/take_delete.php');
            break;
        case 'view':
        case 'viewrequest':
            include(CONFIG['SERVER_ROOT'] . '/sections/requests/request.php');
            break;
        case 'autofill':
            include(CONFIG['SERVER_ROOT'] . '/sections/requests/auto_fill.php');
            break;
        default:
            error(0);
    }
}
