<?php
enforce_login();

if (G::$LoggedUser['DisablePoints']) {
    error('Your points have been disabled.');
}

$Bonus = new \Gazelle\Bonus(G::$DB, G::$Cache);

const DEFAULT_PAGE = '/sections/bonus/store.php';

if (isset($_GET['action'])) {
    switch ($_GET['action']) {
        case 'purchase':
            /* handle validity and cost as early as possible */
            if (isset($_REQUEST['label']) && preg_match('/^[a-z]{1,15}(-\w{1,15}){0,4}/', $_REQUEST['label'])) {
                $Label = $_REQUEST['label'];
                $Item = $Bonus->getItem($Label);
                if ($Item) {
                    if ($Item['MinClass'] > G::$LoggedUser['EffectiveClass']) {
                        error(403);
                    }
                    $Price = $Bonus->getEffectivePrice($Label, G::$LoggedUser['EffectiveClass']);
                    if ($Price > G::$LoggedUser['BonusPoints']) {
                        error('You cannot afford this item.');
                    }
                    switch ($Label) {
                        case 'token-1':
                        case 'token-2':
                        case 'token-3':
                        case 'token-4':
                        case 'other-1':
                        case 'other-2':
                        case 'other-3':
                            require_once(SERVER_ROOT . '/sections/bonus/tokens.php');
                            break;
                        case 'invite':
                            require_once(SERVER_ROOT . '/sections/bonus/invite.php');
                            break;
                        case 'title-bb-y':
                        case 'title-bb-n':
                        case 'title-off':
                            require_once(SERVER_ROOT . '/sections/bonus/title.php');
                            break;
                        case 'eliminate_a_hnr':
                            require_once(SERVER_ROOT . '/sections/bonus/hnr.php');
                            break;
                        case 'upload-1':
                        case 'upload-2':
                        case 'upload-3':
                        case 'upload-4':
                            require_once(SERVER_ROOT . '/sections/bonus/upload.php');
                            break;
                        default:
                            require_once(SERVER_ROOT . DEFAULT_PAGE);
                            break;
                    }
                } else {
                    require_once(SERVER_ROOT . DEFAULT_PAGE);
                    break;
                }
            }
            break;
        case 'bprates':
            require_once(SERVER_ROOT . '/sections/bonus/bprates.php');
            break;
        case 'title':
            require_once(SERVER_ROOT . '/sections/bonus/title.php');
            break;
        case 'history':
            require_once(SERVER_ROOT . '/sections/bonus/history.php');
            break;
        default:
            require_once(SERVER_ROOT . DEFAULT_PAGE);
            break;
    }
} else {
    require_once(SERVER_ROOT . DEFAULT_PAGE);
}
