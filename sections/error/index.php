<?

function notify($Channel, $Message) {
    global $LoggedUser;
    send_irc("PRIVMSG " . $Channel . " :" . $Message . " error by " . (!empty($LoggedUser['ID']) ? site_url() . "user.php?id=" . $LoggedUser['ID'] . " (" . $LoggedUser['Username'] . ")" : $_SERVER['REMOTE_ADDR'] . " (" . Tools::geoip($_SERVER['REMOTE_ADDR']) . ")") . " accessing " . site_url() . "" . $_SERVER['REQUEST_URI'] . (!empty($_SERVER['HTTP_REFERER']) ? " from " . $_SERVER['HTTP_REFERER'] : ''));
}

$Errors = array('403', '404', '413', '504');

if (!empty($_GET['e']) && in_array($_GET['e'], $Errors)) {
    // Web server error i.e. http://sitename/madeupdocument.php
    include($_GET['e'] . '.php');
} else {
    // Gazelle error (Come from the error() function)
    switch ($Error) {

        case '403':
            $Title = t('server.common.error_403_title');
            $Description = t('server.common.error_403_description');
            notify(CONFIG['STATUS_CHAN'], '403');
            break;
        case '404':
            $Title = t('server.common.error_404_title');
            $Description = t('server.common.error_404_description');
            break;
        case '0':
            $Title = t('server.common.invalid_input_title');
            $Description = t('server.common.invalid_input_description');
            notify(CONFIG['STATUS_CHAN'], 'PHP-0');
            break;
        case '-1':
            $Title = t('server.common.invalid_request_title');
            $Description = t('server.common.invalid_request_description');
            break;
        default:
            if (!empty($Error)) {
                if (!$Title) $Title = t('server.common.error');
                $Description = $Error;
            } else {
                $Title = t('server.common.unexpected_error_title');
                $Description = t('server.common.unexpected_error_description');
            }
    }

    if ($Log) {
        $Description .= ' <a href="log.php?search=' . $Log . '">Search Log</a>';
    }

    if (empty($NoHTML) && $Error != -1) {
        View::show_header($Title, '', 'PageErrorHome');
?>
        <div class="LayoutBody">
            <div class="BodyHeader">
                <h2 class="BodyHeader-nav">
                    <?= $Title ?>
                </h2>
            </div>
            <p><?= $Description ?></p>
        </div>
<?
        View::show_footer();
    } else {
        echo $Description;
    }
}
