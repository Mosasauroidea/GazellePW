<?php
require(CONFIG['SERVER_ROOT'] . '/sections/torrents/functions.php');

$GroupID = (int)$_GET['id'];
if ($GroupID === 0) {
    error('bad id parameter', true);
}

$TorrentDetails = Torrents::get_group($GroupID, true, 0, false);
$Image = $TorrentDetails['WikiImage'];
if (!$Image) { // handle no artwork
    $Image = CONFIG['STATIC_SERVER'] . 'common/noartwork/' . $CategoryIcons[$TorrentDetails['CategoryID'] - 1];
}

ajax_json_success(array(
    'wikiImage' => $Image
));
