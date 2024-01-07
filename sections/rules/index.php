<?
//Include all the basic stuff...
enforce_login();
if (!isset($_GET['p'])) {
    require(CONFIG['SERVER_ROOT'] . '/sections/rules/browse.php');
} else {
    switch ($_GET['p']) {
        case 'ratio':
            require(CONFIG['SERVER_ROOT'] . '/sections/rules/ratio.php');
            break;
        case 'golden':
            require(CONFIG['SERVER_ROOT'] . '/sections/rules/browse.php');
            break;
        case 'clients':
            require(CONFIG['SERVER_ROOT'] . '/sections/rules/clients.php');
            break;
        case 'chat':
            require(CONFIG['SERVER_ROOT'] . '/sections/rules/chat.php');
            break;
        case 'upload':
            require(CONFIG['SERVER_ROOT'] . '/sections/rules/upload.php');
            break;
        case 'slots':
            require(CONFIG['SERVER_ROOT'] . '/sections/rules/slots.php');
            break;
        case 'requests';
            require(CONFIG['SERVER_ROOT'] . '/sections/rules/requests.php');
            break;
        case 'collages';
            require(CONFIG['SERVER_ROOT'] . '/sections/rules/collages.php');
            break;
        case 'tag':
            require(CONFIG['SERVER_ROOT'] . '/sections/rules/tag.php');
            break;
        case 'bonus':
            require(CONFIG['SERVER_ROOT'] . '/sections/rules/bonus.php');
            break;
        case 'invite':
            require(CONFIG['SERVER_ROOT'] . '/sections/rules/invite.php');
            break;
        case 'blacklist':
            require(CONFIG['SERVER_ROOT'] . '/sections/rules/blacklist.php');
            break;

        default:
            error(0);
    }
}
