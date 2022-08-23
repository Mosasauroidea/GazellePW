<?
// echo out the slice of the form needed for the selected upload type ($_GET['section']).


// Include the necessary form class
include(CONFIG['SERVER_ROOT'] . '/classes/torrent_form.class.php');
$TorrentForm = new TORRENT_FORM();

$UploadForm = $Categories[$_GET['categoryid']];

switch ($UploadForm) {
    case 'Movies':
        $TorrentForm->movie_form();
        break;
    default:
        echo 'Invalid action!';
}
