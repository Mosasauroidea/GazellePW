<?php

date_default_timezone_set('UTC');


// Main settings
define('SITE_NAME', 'GPW DEV'); //The name of your site
define('SITE_HOST', '$SITE_HOST'); // The host for your site (e.g. localhost, orpheus.network)
define('SITE_URL', 'http://' . SITE_HOST . ':9000'); // The base URL to access the site (e.g. http://localhost:8080, https://orpheus.network)
define('SERVER_ROOT', '/var/www'); //The root of the server, used for includes, purpose is to shorten the path string
define('ANNOUNCE_URL', 'http://' . SITE_HOST . ':2710'); //Announce HTTP URL
define('VITE_SERVER', 'http://' . SITE_HOST . ':9002');
define('IS_DEV', true);
define('TORRENT_SOURCE', 'GPW-DEV');

// Allows you to run static content off another server. Default is usually what you want.
define('STATIC_SERVER', 'static/');

// Movie info related key
define('TMDB_API_KEY', '$TMDB_API_KEY');
define('OMDB_API_KEY', '$OMDB_API_KEY');
define('DOUBAN_API_URL', '$DOUBAN_API_URL');

// Keys
define('ENCKEY', 'OL9n0m2JxhBxYyMvXWJg'); //Random key. The key for encryption
define('SITE_SALT', ''); //Random key. Default site wide salt for passwords, DO NOT LEAVE THIS BLANK/CHANGE AFTER LAUNCH!
define('SCHEDULE_KEY', ''); // Random key. This key must be the argument to schedule.php for the schedule to work.
define('RSS_HASH', ''); //Random key. Used for generating unique RSS auth key.
define('CRYPT_HASH_PREFIX', '$2y$07$');

// MySQL details
define('SQLHOST', 'localhost'); //The MySQL host ip/fqdn
define('SQLLOGIN', '$MYSQL_USER'); //The MySQL login
define('SQLPASS', '$MYSQL_PASSWORD'); //The MySQL password
define('SQLDB', 'gazelle'); //The MySQL database to use
define('SQLPORT', 3306); //The MySQL port to connect on
define('SQLSOCK', '/var/run/mysqld/mysqld.sock');

// Memcached details
$MemcachedServers = array(
    // unix sockets are fast, and other people can't telnet into them
    array('host' => 'unix:///var/run/memcached.sock', 'port' => 0, 'buckets' => 1),
);


// Telegram details
define('TG_DISABLE_CHANNEL_NAME', 'GPW - DEV - Disabled');
define('TG_DISBALE_CHANNEL', '');
define('TG_GROUP', '');

// Sphinx details
define('SPHINX_HOST', 'localhost');
define('SPHINX_PORT', 9312);
define('SPHINXQL_HOST', '127.0.0.1');
define('SPHINXQL_PORT', 9306);
define('SPHINXQL_SOCK', false);
define('SPHINX_MAX_MATCHES', 1000); // Must be <= the server's max_matches variable (default 1000)
define('SPHINX_INDEX', 'torrents');

// Ocelot details
define('DISABLE_TRACKER', true);
define('TRACKER_HOST', 'localhost');
define('TRACKER_PORT', 2710);
define('TRACKER_SECRET', ''); // Must be 32 characters and match site_password in Ocelot's config.cpp
define('TRACKER_REPORTKEY', ''); // Must be 32 characters and match report_password in Ocelot's config.cpp

define('TMPDIR', SERVER_ROOT . '/.cache');

// Site settings
define('DEBUG_EMAIL', true); // //Set to true to write messages to TMPDIR/emails instead of delivering
define('DEBUG_MODE', true); //Set to false if you dont want everyone to see debug information, can be overriden with 'site_debug'
define('DEBUG_WARNINGS', true); //Set to true if you want to see PHP warnings in the footer

define('SHOW_PUBLIC_INDEX', true); // Show the public index.php landing page

define('OPEN_EXTERNAL_REFERRALS', true); //Set to false to disable external tracker referrals, true to allow them

// Open registration details
define('OPEN_REGISTRATION', false);
define('OPEN_REGISTRATION_FROM', "2022-01-01 00:00");
define('OPEN_REGISTRATION_TO', "2032-01-03 23:59");
define('OPEN_REGISTRATION_EMAIL', []);
define('NOT_ALLOWED_REGISTRATION_EMAIL', []);

define('USER_LIMIT', 5000); //The maximum number of users the site can have, 0 for no limit

define('REQUEST_TAX', 0.0); //Percentage Tax (0 - 1) to charge users on making requests

define('STARTING_UPLOAD', 3221225472); //Upload given to newly registered users, in bytes using IEC standard (1024 bytes per KiB)

define('STARTING_INVITES', 0); //# of invites to give to newly registered users

define('BLOCK_OPERA_MINI', false); //Set to true to block Opera Mini proxy

define('SYSTEM_USER_ID', 0); // ID for user to create "system" threads under (e.g. Edit Requests)
define('TRASH_FORUM_ID', 0); // ID of forum to send threads to when trash button is pressed
define('EDITING_FORUM_ID', 0); // ID of forum to send editing requests to
define('EDITING_TRASH_FORUM_ID', 0); // ID of forum to send editing threads to when trash button is pressed in EDITING_FORUM_ID
define('ANNOUNCEMENT_FORUM_ID', 0);
define('NEWS_FORUM_ID', 0);
define('DONOR_FORUM', 0);

$ForumsRevealVoters = array();
$ForumsDoublePost = array();

// Site option
define('ENABLE_BADGE', false); // Set to enable badge system
define('ENABLE_COLLAGES', true); // Set to enable collages system
define('ENABLE_VOTES', false); // Set to enable votes system
define('ENABLE_HNR', false); // Set to enable H&R
define('ENABLE_SITEHISTORY', false); // Set to enable site history

// Email delivery method and information
define('EMAIL_DELIVERY_TYPE', 'local'); // should be either 'mailgun' to use mailgun services or 'local' to use a local SMTP server
define('MAIL_HOST', 'mail.' . SITE_HOST); // The host to use for mail delivery (e.g. gpw.con)
// Mailgun config
define('MAILGUN_API_KEY', '');
define('MAILGUN_API_URL', '');

// Pagination
define('TORRENT_COMMENTS_PER_PAGE', 10);
define('POSTS_PER_PAGE', 25);
define('TOPICS_PER_PAGE', 50);
define('TORRENTS_PER_PAGE', 50);
define('REQUESTS_PER_PAGE', 25);
define('MESSAGES_PER_PAGE', 25);
define('LOG_ENTRIES_PER_PAGE', 50);

// Cache catalogues
define('THREAD_CATALOGUE', 500); // Limit to THREAD_CATALOGUE posts per cache key.

// IRC settings
define('DISABLE_IRC', true);
define('BOT_NICK', '');
define('BOT_SERVER', ''); // IRC server address. Used for onsite chat tool.
define('BOT_PORT', 6667);
define('BOT_CHAN', '#' . SITE_URL);
define('BOT_ANNOUNCE_CHAN', '#');
define('BOT_STAFF_CHAN', '#');
define('BOT_DISABLED_CHAN', '#'); // Channel to refer disabled users to.
define('BOT_HELP_CHAN', '#');
define('BOT_DEBUG_CHAN', '#');
define('BOT_REPORT_CHAN', '#');
define('BOT_NICKSERV_PASS', '');
define('BOT_INVITE_CHAN', BOT_CHAN . '-invites'); // Channel for non-members seeking an interview
define('BOT_INTERVIEW_CHAN', BOT_CHAN . '-interview'); // Channel for the interviews
define('BOT_INTERVIEW_NUM', 5);
define('BOT_INTERVIEW_STAFF', BOT_CHAN . '-interviewers'); // Channel for the interviewers
define('SOCKET_LISTEN_PORT', 51010);
define('SOCKET_LISTEN_ADDRESS', 'localhost');
define('ADMIN_CHAN', '#');
define('LAB_CHAN', '#');
define('STATUS_CHAN', '#');



// array to store external site credentials and API URIs, stored in cache to keep user sessions alive
$ExternalServicesConfig = [
    "PassThePopcorn" => [
        'type' => 'gazelle',
        'inviter_id' => 1,
        'base_url' => 'https://passthepopcorn.me/',
        'api_path' => 'ajax.php?action=',
        'login_path' => 'login.php',
        'username' => 'foo',
        'password' => 'bar',
        'cookie' => '',
        'cookie_expiry' => 0,
        'status' => TRUE
    ]
];

// Image hosting service
define('IMAGE_PROVIDER', 'local'); // Image storage service provider (local or minio)
define('IMAGE_URL', SITE_URL); // Image storage service base URL

// Minio service config
define('MINIO_ENDPOINT', '');
define('MINIO_KEY', "");
define('MINIO_SECRET', "");
define('MINIO_BUCKET', "");

// Banner info
define('BANNER_URL', '/apply.php');
define('BANNER_TEXT', 'Banner Text');

define('USER',      '2');
define('MEMBER',    '3');
define('POWER',     '4');
define('ELITE',     '5');
define('TORRENT_MASTER', '25');
define('GURU',  '28');
define('POWER_TM',  '29');
define('ELITE_TM',  '44');
define('MOD',       '11');
define('DESIGNER',  '32');
define('SYSOP',     '15');
define('DONOR',     '20');
define('FORUM_MOD', '21');
define('TORRENT_MOD', '22');
define('FLS_TEAM',  '23');
define('INTERVIEW', '30');


define('INDEX_ARTICLE', 1);
define('DONATE_ARTILCE_ID', 2);

define('GLOBAL_FREELEECH', true);

define('DONATE_MONTH_GOAL', 750);
