<?
authorize();

$TorrentView = $_POST['torrent_view'];
$Scene = $_POST['scene'];

Users::update_site_options($LoggedUser['ID'], [
    "TorrentView$Scene" => $TorrentView,
]);

$Location = (empty($_SERVER['HTTP_REFERER'])) ? "/index.php" : $_SERVER['HTTP_REFERER'];
header("Location: $Location");
