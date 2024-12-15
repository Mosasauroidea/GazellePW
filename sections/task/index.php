<?
enforce_login();
if (isset($_GET['method'])) {
    switch ($_GET['method']) {
        case 'transcode':
            include(CONFIG['SERVER_ROOT'] . '/sections/task/transcode.php');
            break;
        case 'transcode_beta':
            include(CONFIG['SERVER_ROOT'] . '/sections/task/transcode_beta.php');
            break;
        case 'single':
            include(CONFIG['SERVER_ROOT'] . '/sections/task/single.php');
            break;
        case 'snatch':
            include(CONFIG['SERVER_ROOT'] . '/sections/task/snatch.php');
            break;
        case 'artistless':
            include(CONFIG['SERVER_ROOT'] . '/sections/task/artistless.php');
            break;
        case 'checksum':
            include(CONFIG['SERVER_ROOT'] . '/sections/task/checksum.php');
            break;
        case 'tags':
            include(CONFIG['SERVER_ROOT'] . '/sections/task/tags.php');
            break;
        case 'folders':
            include(CONFIG['SERVER_ROOT'] . '/sections/task/folders.php');
            break;
        case 'files':
            include(CONFIG['SERVER_ROOT'] . '/sections/task/files.php');
            break;
        case 'upload':
            include(CONFIG['SERVER_ROOT'] . '/sections/task/upload.php');
            break;
        case 'artwork':
            include(CONFIG['SERVER_ROOT'] . '/sections/task/artwork.php');
            break;
        case 'artistimage':
            include(CONFIG['SERVER_ROOT'] . '/sections/task/artistimage.php');
            break;
        case 'description':
            include(CONFIG['SERVER_ROOT'] . '/sections/task/description.php');
            break;
        case 'lineage':
            include(CONFIG['SERVER_ROOT'] . '/sections/task/lineage.php');
            break;
        case 'img':
            include(CONFIG['SERVER_ROOT'] . '/sections/task/img.php');
            break;
        case 'compress':
            include(CONFIG['SERVER_ROOT'] . '/sections/task/compress.php');
            break;
        case 'custom':
            include(CONFIG['SERVER_ROOT'] . '/sections/task/custom.php');
            break;
        default:
            error(404);
            break;
    }
} else {
    include(CONFIG['SERVER_ROOT'] . '/sections/task/task.php');
}
