<?
enforce_login();
if (!check_perms('site_upload')) {
    error(403);
}
if ($LoggedUser['DisableUpload']) {
    error('Your upload privileges have been revoked.');
}

if (!empty($_REQUEST['action'])) {
    switch ($_REQUEST['action']) {
        case 'parse_html':
            include CONFIG['SERVER_ROOT'] . '/sections/upload/parse_html.php';
            break;
        case 'image':
            include CONFIG['SERVER_ROOT'] . '/sections/upload/image.php';
            break;
        case 'movie_info':
            include CONFIG['SERVER_ROOT'] . '/sections/upload/movie_info.php';
            break;
        case 'imgupload':
            include CONFIG['SERVER_ROOT'] . '/sections/upload/imgupload_handle.php';
            break;
        default:
            error(404);
    }
} elseif (!empty($_POST['submit'])) {
    include(CONFIG['SERVER_ROOT'] . '/sections/upload/upload_handle_v2.php');
} else {
    include(CONFIG['SERVER_ROOT'] . '/sections/upload/upload.php');
}
