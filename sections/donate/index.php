<?
enforce_login();

if (isset($_REQUEST['action'])) {
    switch ($_REQUEST['action']) {
        case 'step1':
            include(SERVER_ROOT . '/sections/donate/step1.php');
            break;
        case 'step2':
            include(SERVER_ROOT . '/sections/donate/step2.php');
            break;
        case 'donate':
            include(SERVER_ROOT . '/sections/donate/donate.php');
            break;
    }
} else {
    include(SERVER_ROOT . '/sections/donate/step1.php');
}
