<?
class Subtitles {
    public static function get($TorrentID) {
        $Data = G::$Cache->get_value("torrent_sub_title_$TorrentID");
        if (!$Data) {
            G::$DB->prepared_query("
        select 
            subtitles.id, 
            subtitles.languages, 
            subtitles.torrent_id, 
            subtitles.source, 
            subtitles.download_times, 
            subtitles.format, 
            subtitles.size, 
            subtitles.uploader, 
            subtitles.upload_time, 
            subtitles.name
        from subtitles 
            where torrent_id= ? ", $TorrentID);
            $AllSubtitles = G::$DB->to_array();
            G::$Cache->cache_value("torrent_sub_title_$TorrentID", [$AllSubtitles]);
        } else {
            list($AllSubtitles) = $Data;
        }
        return $AllSubtitles;
    }
}
