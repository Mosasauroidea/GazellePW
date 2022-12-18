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

require(__DIR__ . '/../classes/includes.php');

header('Cache-Control: no-cache, must-revalidate, post-check=0, pre-check=0');
header('Pragma:');
header('Expires: ' . date('D, d M Y H:i:s', time() + (2 * 60 * 60)) . ' GMT');
header('Last-Modified: ' . date('D, d M Y H:i:s') . ' GMT');

$Feed = new FEED; // Load the time class

require(CONFIG['SERVER_ROOT'] . '/sections/feeds/index.php');
