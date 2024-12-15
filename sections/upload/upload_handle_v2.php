<?


ini_set('max_file_uploads', 100);
define('MAX_FILENAME_LENGTH', 255);

use Gazelle\Upload;
use Gazelle\Exception\InvalidParamException;

$IsNewGroup = empty($_POST['groupid']);

$uploader = new Upload($IsNewGroup);
try {
    $uploadedTorrent = $uploader->uploadTorrent($_POST, $_FILES);
} catch (InvalidParamException $e) {
    $Err = $e->getMessage();
    include(CONFIG['SERVER_ROOT'] . '/sections/upload/upload.php');
    die();
} catch (Exception $e) {
    error_log($e->getMessage());
    $Err = 'internal error';
    include(CONFIG['SERVER_ROOT'] . '/sections/upload/upload.php');
    die();
}
$GroupID = $uploadedTorrent->GroupID;
$TorrentID = $uploadedTorrent->TorrentID;

if (!$uploadedTorrent->$IsPrivate) {
    View::show_header(t('server.upload.header_warning'), '', 'PageUploadHandle');
?>
    <div class="LayoutBody">
        <div class="BodyHeader">
            <div class="BodyHeader-nav"><?= t('server.upload.upload_handle_warning') ?></div>
        </div>
        <p><?= t('server.upload.need_download_new_torrent1') ?><a href="torrents.php?id=<?= $GroupID ?>&torrentid=<?= $TorrentID ?>"><?= t('server.upload.here') ?></a><?= t('server.upload.need_download_new_torrent2') ?></p>
    </div>
<? View::show_footer();
} elseif ($uploadedTorrent->RequestID) {
    header("Location: requests.php?action=takefill&requestid=$RequestID&torrentid=$TorrentID&auth=" . $LoggedUser['AuthKey']);
} else {
    header("Location: torrents.php?id=$GroupID");
}
