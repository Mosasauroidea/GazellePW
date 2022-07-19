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
require_once(__DIR__ . '/../classes/classloader.php');
require_once(__DIR__ . '/../classes/config.php');
require_once(__DIR__ . '/../classes/const.php');
require_once(__DIR__ . '/../vendor/autoload.php');
require_once(__DIR__ . '/../classes/util.php');

$Cache = new CACHE($MemcachedServers);
$DB = new DB_MYSQL;
$Debug = new DEBUG;
$Twig = new Twig\Environment(
    new \Twig\Loader\FilesystemLoader(__DIR__ . '/../templates'),
    [
        'debug' => CONFIG['DEBUG_MODE'],
        'cache' => __DIR__ . '/../cache/twig'
    ]
);
$Debug->handle_errors();

G::$Cache = &$Cache;
G::$DB = &$DB;
G::$Debug = &$Debug;
G::$Twig = &$Twig;

header('Expires: ' . date('D, d M Y H:i:s', time() + (2 * 60 * 60)) . ' GMT');
header('Last-Modified: ' . date('D, d M Y H:i:s') . ' GMT');
header('Content-type: application/json');
require_once(__DIR__ . '/../sections/api/index.php');
