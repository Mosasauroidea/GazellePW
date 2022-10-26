<?php

namespace Gazelle\API;

use Gazelle\Upload as Uploader;

ini_set('max_file_uploads', 100);
define('MAX_FILENAME_LENGTH', 255);

class Upload extends AbstractAPI {

    public function run() {
        return $this->uploadTorrent();
    }

    private function uploadTorrent() {
        $IsNewGroup = empty($_POST['groupid']);
        $uploader = new Uploader($IsNewGroup);
        try {
            $uploadedTorrent = $uploader->uploadTorrent($_POST, $_FILES);
        } catch (\Exception $e) {
            $Err = $e->getMessage();
            json_error($Err);
        }
        $response = array();
        $response['torrent_id'] = $uploadedTorrent->TorrentID;
        return $response;
    }
}
