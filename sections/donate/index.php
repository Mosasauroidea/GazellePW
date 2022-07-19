<?
enforce_login();

if (isset($_REQUEST['action'])) {
    switch ($_REQUEST['action']) {
        case 'step1':
            include(CONFIG['SERVER_ROOT'] . '/sections/donate/step1.php');
            break;
        case 'step2':
            include(CONFIG['SERVER_ROOT'] . '/sections/donate/step2.php');
            break;
        case 'donate':
            include(CONFIG['SERVER_ROOT'] . '/sections/donate/donate.php');
            break;
    }
} else {
    include(CONFIG['SERVER_ROOT'] . '/sections/donate/step1.php');
}
