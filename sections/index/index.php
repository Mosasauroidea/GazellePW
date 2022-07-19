<?
if (isset($LoggedUser['ID'])) {
    if (!isset($_REQUEST['action'])) {
        include('private.php');
    } else {
        switch ($_REQUEST['action']) {
            case 'change_language':
                include('change_language.php');
            case 'poll':
                include(CONFIG['SERVER_ROOT'] . '/sections/forums/poll_vote.php');
                break;
            default:
                error(0);
        }
    }
} else {
    include('public.php');
}
