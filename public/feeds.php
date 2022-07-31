<?php
/*-- Feed Start Class ----------------------------------*/
/*------------------------------------------------------*/
/* Simplified version of script_start, used for the  */
/* sitewide RSS system.                              */
/*------------------------------------------------------*/

/********************************************************/

// Let's prevent people from clearing feeds
if (isset($_GET['clearcache'])) {
    unset($_GET['clearcache']);
}

require __DIR__ . '/../classes/config.php'; // The config contains all site-wide configuration information as well as memcached rules
require __DIR__ . '/../classes/const.php';

require(CONFIG['SERVER_ROOT'] . '/classes/classloader.php');
require(CONFIG['SERVER_ROOT'] . '/classes/misc.class.php'); // Require the misc class
require(CONFIG['SERVER_ROOT'] . '/classes/cache.class.php'); // Require the caching class
require(CONFIG['SERVER_ROOT'] . '/classes/feed.class.php'); // Require the feeds class
require(CONFIG['SERVER_ROOT'] . '/classes/util.php'); // Require the feeds class

$Cache = new CACHE($CONFIG['MemcachedServers']); // Load the caching class
$Feed = new FEED; // Load the time class

header('Cache-Control: no-cache, must-revalidate, post-check=0, pre-check=0');
header('Pragma:');
header('Expires: ' . date('D, d M Y H:i:s', time() + (2 * 60 * 60)) . ' GMT');
header('Last-Modified: ' . date('D, d M Y H:i:s') . ' GMT');

require(CONFIG['SERVER_ROOT'] . '/sections/feeds/index.php');
