<?php
/*-- API Start Class -------------------------------*/
/*--------------------------------------------------*/
/* Simplified version of script_start, used for the    */
/* site API calls                                    */
/*--------------------------------------------------*/

/****************************************************/


$ScriptStartTime = microtime(true); //To track how long a page takes to create

//Lets prevent people from clearing feeds
if (isset($_GET['clearcache'])) {
    unset($_GET['clearcache']);
}

require(__DIR__ . '/../classes/includes.php');

$available = [
    'generate_invite',
    'user',
    'wiki',
    'forum',
    'request',
    'artist',
    'collage',
    'torrent',
    "upload",
    'movie_info',
    "img_upload"
];

if (!in_array($_GET['action'], $available)) {
    json_error('invalid action');
}

if (empty($_GET['api_key'])) {
    json_error('invalid parameters');
}

$token = $_GET['api_key'];

$app = $Cache->get_value("api_apps_{$token}");
if (!is_array($app)) {
    $DB->prepared_query("
        SELECT Token, Name, UserID
        FROM api_applications
        WHERE Token = ?
        LIMIT 1", $token);
    if ($DB->record_count() === 0) {
        json_error('invalid token');
    }
    $app = $DB->to_array(false, MYSQLI_ASSOC);
    G::$Cache->cache_value("api_apps_{$token}", $app);
}
$app = $app[0];


if ($app['Token'] !== $token) {
    json_error('invalid token');
}

// Get info such as username
$LightInfo = Users::user_info($app['UserID']);
$HeavyInfo = Users::user_heavy_info($app['UserID']);
$UserStats = Users::user_stats($app['UserID']);
// Create LoggedUser array
$LoggedUser = array_merge($HeavyInfo, $LightInfo, $UserStats);
G::$LoggedUser = &$LoggedUser;



header('Expires: ' . date('D, d M Y H:i:s', time() + (2 * 60 * 60)) . ' GMT');
header('Last-Modified: ' . date('D, d M Y H:i:s') . ' GMT');
header('Content-type: application/json');
require_once(__DIR__ . '/../sections/api/index.php');
