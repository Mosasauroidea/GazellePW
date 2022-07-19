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
            $Title = Lang::get('global', 'error_403_title');
            $Description = Lang::get('global', 'error_403_description');
            notify(CONFIG['STATUS_CHAN'], '403');
            break;
        case '404':
            $Title = Lang::get('global', 'error_404_title');
            $Description = Lang::get('global', 'error_404_description');
            break;
        case '0':
            $Title = Lang::get('global', 'invalid_input_title');
            $Description = Lang::get('global', 'invalid_input_description');
            notify(CONFIG['STATUS_CHAN'], 'PHP-0');
            break;
        case '-1':
            $Title = Lang::get('global', 'invalid_request_title');
            $Description = Lang::get('global', 'invalid_request_description');
            break;
        default:
            if (!empty($Error)) {
                if (!$Title) $Title = Lang::get('global', 'error');
                $Description = $Error;
            } else {
                $Title = Lang::get('global', 'unexpected_error_title');
                $Description = Lang::get('global', 'unexpected_error_description');
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
            <div class="Box">
                <div class="Box-body">
                    <p><?= $Description ?></p>
                </div>
            </div>
        </div>
<?
        View::show_footer();
    } else {
        echo $Description;
    }
}
