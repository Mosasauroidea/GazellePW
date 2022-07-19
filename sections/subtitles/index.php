<?php
enforce_login();
// if (!check_perms('site_upload_subtitle')) {
//     error("Your userclass does not allow you to upload subtitle.");
// }
// if ($LoggedUser['DisableUploadSubtitle']) {
//     error('Your subtitle upload privileges have been revoked.');
// }
if (!empty($_REQUEST['action'])) {
    switch ($_REQUEST['action']) {
        case 'download':
            require(CONFIG['SERVER_ROOT'] . '/sections/subtitles/download.php');
            break;
        case 'delete':
            require(CONFIG['SERVER_ROOT'] . '/sections/subtitles/delete.php');
            break;
        case 'takedelete':
            require(CONFIG['SERVER_ROOT'] . '/sections/subtitles/takedelete.php');
            break;
        case 'upload':
            require(CONFIG['SERVER_ROOT'] . '/sections/subtitles/upload.php');
            break;
        case 'ajax_get':
            require(CONFIG['SERVER_ROOT'] . '/sections/subtitles/ajax_get.php');
            break;
    }
} else if (!empty($_POST['submit'])) {
    include(CONFIG['SERVER_ROOT'] . '/sections/subtitles/upload_handle.php');
} else {
    die();
}
