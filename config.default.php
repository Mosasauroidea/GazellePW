<?php

date_default_timezone_set('UTC');

$CONFIG = [];

// 主设置 | Main settings
$CONFIG['SITE_NAME'] = "GPW DEV"; //站名 | The name of your site
$CONFIG['SITE_HOST'] = "localhost"; //站点域名 | The host for your site (e.g. localhost, orpheus.network)
$CONFIG['SITE_URL'] = "http://${CONFIG['SITE_HOST']}:9000"; // The base URL to access the site (e.g. http://localhost:8080, https://orpheus.network)
$CONFIG['SERVER_ROOT'] = "/var/www"; //The root of the server, used for includes, purpose is to shorten the path string
$CONFIG['ANNOUNCE_URL'] = "http://${CONFIG['SITE_HOST']}:2710"; //Announce HTTP URL
$CONFIG['VITE_SERVER'] = "http://${CONFIG['SITE_HOST']}:9002";
$CONFIG['IS_DEV'] = true;
$CONFIG['TORRENT_SOURCE'] = "GPW-DEV";

// Allows you to run static content off another server. Default is usually what you want.
$CONFIG['STATIC_SERVER'] = "static/";

// Movie info related key
$CONFIG['TMDB_API_KEY'] = "TMDB_API_KEY";
$CONFIG['OMDB_API_KEY'] = "OMDB_API_KEY";
$CONFIG['DOUBAN_API_URL'] = "DOUBAN_API_URL";

// Keys
$CONFIG['ENCKEY'] = "OL9n0m2JxhBxYyMvXWJg"; //Random key. The key for encryption
$CONFIG['SITE_SALT'] = ''; // TODO: not used. Random key. Default site wide salt for passwords, DO NOT LEAVE THIS BLANK/CHANGE AFTER LAUNCH!
$CONFIG['SCHEDULE_KEY'] = "OL9n0m2JxhBxYyMvXWJg"; // Random key. This key must be the argument to schedule.php for the schedule to work.
$CONFIG['RSS_HASH'] = ""; //Random key. Used for generating unique RSS auth key.
$CONFIG['CRYPT_HASH_PREFIX'] = "$2y$07$"; // TODO: not used.
$CONFIG['GEOIP_LICENSE_KEY'] = '';

// MySQL details
$CONFIG['SQLHOST'] = "mysql"; //The MySQL host ip/fqdn
$CONFIG['SQLLOGIN'] = "gazelle"; //The MySQL login
$CONFIG['SQLPASS'] = "password"; //The MySQL password
$CONFIG['SQLDB'] = "gazelle"; //The MySQL database to use
$CONFIG['SQLPORT'] = 3306; //The MySQL port to connect on
$CONFIG['SQLSOCK'] = "/var/run/mysqld/mysqld.sock";

// Memcached details
$CONFIG['MemcachedServers'] = [
    // unix sockets are fast, and other people can't telnet into them
    ['host' => 'memcached', 'port' => 11211, 'buckets' => 1],
];


// Telegram details
$CONFIG['TG_DISABLE_CHANNEL_NAME'] = "GPW - DEV - Disabled";
$CONFIG['TG_DISBALE_CHANNEL'] = "https://t.me/disabled";
$CONFIG['TG_GROUP'] = "https://t.me/group";

// Sphinx details
$CONFIG['SPHINX_HOST'] = 'manticoresearch';  // TODO: not used
$CONFIG['SPHINX_PORT'] = 9312; // TODO: not used
$CONFIG['SPHINXQL_HOST'] = "manticoresearch";
$CONFIG['SPHINXQL_PORT'] = 9306;
$CONFIG['SPHINXQL_SOCK'] = false;
$CONFIG['SPHINX_MAX_MATCHES'] = 1000; // Must be <= the server's max_matches variable (default 1000)
$CONFIG['SPHINX_INDEX'] = 'torrents'; // TODO: not used

// Ocelot details
$CONFIG['DISABLE_TRACKER'] = true;
$CONFIG['TRACKER_HOST'] = "localhost";
$CONFIG['TRACKER_PORT'] = 2710;
$CONFIG['TRACKER_SECRET'] = ""; // Must be 32 characters and match site_password in Ocelot's config.cpp
$CONFIG['TRACKER_REPORTKEY'] = ""; // Must be 32 characters and match report_password in Ocelot's config.cpp

$CONFIG['TMPDIR'] = "${CONFIG['SERVER_ROOT']}/.cache";

// Site settings
$CONFIG['DEBUG_EMAIL'] = true; // //Set to true to write messages to CONFIG['TMPDIR']/emails instead of delivering
$CONFIG['DEBUG_MODE'] = true; //Set to false if you dont want everyone to see debug information, can be overriden with 'site_debug'
$CONFIG['DEBUG_WARNINGS'] = true; //Set to true if you want to see PHP warnings in the footer

$CONFIG['SHOW_PUBLIC_INDEX'] = true; // Show the public index.php landing page

$CONFIG['OPEN_EXTERNAL_REFERRALS'] = false; //Set to false to disable external tracker referrals, true to allow them

// Open registration details
$CONFIG['OPEN_REGISTRATION'] = false;
$CONFIG['OPEN_REGISTRATION_FROM'] = "2022-01-01 00:00";
$CONFIG['OPEN_REGISTRATION_TO'] = "2032-01-03 23:59";
$CONFIG['OPEN_REGISTRATION_EMAIL'] = [];
$CONFIG['NOT_ALLOWED_REGISTRATION_EMAIL'] = [];

# Close login page
$CONFIG['CLOSE_LOGIN'] = false;
$CONFIG['CLOSE_REDIRECT_URL'] = "";

$CONFIG['USER_LIMIT'] = 5000; //The maximum number of users the site can have, 0 for no limit

$CONFIG['REQUEST_TAX'] = 0.0; //Percentage Tax (0 - 1) to charge users on making requests
$CONFIG['REQUEST_MIN_VOTE'] = 1 * 1024 * 1024 * 1024;

$CONFIG['STARTING_UPLOAD'] = 3221225472; //Upload given to newly registered users, in bytes using IEC standard (1024 bytes per KiB)

$CONFIG['STARTING_INVITES'] = 0; //# of invites to give to newly registered users

$CONFIG['BLOCK_OPERA_MINI'] = false; //Set to true to block Opera Mini proxy

$CONFIG['SYSTEM_USER_ID'] = 0; // ID for user to create "system" threads under (e.g. Edit Requests)
$CONFIG['TRASH_FORUM_ID'] = 0; // ID of forum to send threads to when trash button is pressed
$CONFIG['EDITING_FORUM_ID'] = 0; // ID of forum to send editing requests to
$CONFIG['ANNOUNCEMENT_FORUM_ID'] = 0;
$CONFIG['NEWS_FORUM_ID'] = 0;
$CONFIG['DONOR_FORUM'] = 0;
$CONFIG['FEATURED_MOVIE_FORUM'] = 0;
$CONFIG['STAFF_BLOG_FORUM'] = 0;
$CONFIG['STAFF_FORUM'] = 0;

$CONFIG['ForumsRevealVoters'] = [];

// Site option
$CONFIG['ENABLE_BADGE'] = false; // Set to enable badge system
$CONFIG['ENABLE_COLLAGES'] = true; // Set to enable collages system
$CONFIG['ENABLE_VOTES'] = false; // Set to enable votes system
$CONFIG['ENABLE_HNR'] = false; // Set to enable H&R
$CONFIG['ENABLE_SITEHISTORY'] = false; // Set to enable site history

// Email delivery method and information
$CONFIG['EMAIL_DELIVERY_TYPE'] = "smtp"; // should be either 'mailgun' to use mailgun services or 'smtp' to use a SMTP server
$CONFIG['MAIL_HOST'] = "mail.${CONFIG['SITE_HOST']}"; // The host to use for mail delivery (e.g. gpw.com)
// Mailgun config
$CONFIG['MAILGUN_API_KEY'] = "";
$CONFIG['MAILGUN_API_URL'] = ""; // TODO: not used
// SMTP config
$CONFIG['MAIL_SMTP_HOST'] = "";
$CONFIG['MAIL_SMTP_DEBUG'] = true;
$CONFIG['MAIL_SMTP_USERNAME'] = "";
$CONFIG['MAIL_SMTP_PASSWORD'] = "";
$CONFIG['MAIL_SMTP_PORT'] = 465;

// Pagination
$CONFIG['TORRENT_COMMENTS_PER_PAGE'] = 10;
$CONFIG['POSTS_PER_PAGE'] = 25;
$CONFIG['TOPICS_PER_PAGE'] = 50;
$CONFIG['TORRENTS_PER_PAGE'] = 50;
$CONFIG['REQUESTS_PER_PAGE'] = 25;
$CONFIG['MESSAGES_PER_PAGE'] = 25;
$CONFIG['LOG_ENTRIES_PER_PAGE'] = 50;
$CONFIG['DONATIONS_PER_PAGE'] = 50;
$CONFIG['ARTICLES_PER_PAGE'] = 25;

// Cache catalogues
$CONFIG['THREAD_CATALOGUE'] = 500; // posts per cache key.

// IRC settings
$CONFIG['DISABLE_IRC'] = true;
$CONFIG['BOT_NICK'] = "";
$CONFIG['BOT_SERVER'] = ""; // IRC server address. Used for onsite chat tool.
$CONFIG['BOT_PORT'] = 6667;
$CONFIG['BOT_CHAN'] = "#${CONFIG['SITE_URL']}";
$CONFIG['BOT_ANNOUNCE_CHAN'] = "#";
$CONFIG['BOT_STAFF_CHAN'] = "#";
$CONFIG['BOT_DISABLED_CHAN'] = "#"; // Channel to refer disabled users to.
$CONFIG['BOT_HELP_CHAN'] = "#";
$CONFIG['BOT_DEBUG_CHAN'] = "#";
$CONFIG['BOT_REPORT_CHAN'] = "#";
$CONFIG['BOT_NICKSERV_PASS'] = "";
$CONFIG['BOT_INVITE_CHAN'] = "${CONFIG['BOT_CHAN']}-invites"; // Channel for non-members seeking an interview
$CONFIG['BOT_INTERVIEW_CHAN'] = "${CONFIG['BOT_CHAN']}-interview"; // Channel for the interviews
$CONFIG['BOT_INTERVIEW_NUM'] = 5;
$CONFIG['BOT_INTERVIEW_STAFF'] = "${CONFIG['BOT_CHAN']}-interviewers"; // Channel for the interviewers
$CONFIG['SOCKET_LISTEN_PORT'] = 51010;
$CONFIG['SOCKET_LISTEN_ADDRESS'] = "localhost";
$CONFIG['ADMIN_CHAN'] = "#";
$CONFIG['LAB_CHAN'] = "#";
$CONFIG['STATUS_CHAN'] = "#";

// array to store external site credentials and API URIs, stored in cache to keep user sessions alive
$CONFIG['ExternalServicesConfig'] = [
    "PassThePopcorn" => [
        'type' => 'gazelle',
        'inviter_id' => 2,
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
$CONFIG['IMAGE_PROVIDER'] = "local"; // Image storage service provider (local or minio)
$CONFIG['IMAGE_URL'] = $CONFIG['SITE_URL']; // Image storage service base URL
$CONFIG['IMAGE_PATH_PREFIX'] = "gpw";

$CONFIG['IMAGE_HOST_BLACKLIST'] = [];
$CONFIG['IMAGE_HOST_WHITELIST'] = [];

// Minio service config
$CONFIG['MINIO_ENDPOINT'] = "";
$CONFIG['MINIO_KEY'] = "";
$CONFIG['MINIO_SECRET'] = "";
$CONFIG['MINIO_BUCKET'] = "";

// Banner info
$CONFIG['BANNER_URL'] = "/apply.php";
$CONFIG['BANNER_TEXT'] = [
    "en" => "Banner Notification",
    "zh-Hans" => "顶部通知"
];

$CONFIG['USER_CLASS'] = [
    'USER' => "2",
    'MEMBER' => "3",
    'POWER' => "4",
    'ELITE' => "5",
    'TORRENT_MASTER' => "25",
    'GURU' => "28",
    'POWER_TM' => "29",
    'ELITE_TM' => "44",
    'MOD' => "11",
    'DESIGNER' => "32",
    'SYSOP' => "15",
    'DONOR' => "20",
    'FORUM_MOD' => "21",
    'TORRENT_MOD' => "22",
    'FLS_TEAM' => "23",
    'INTERVIEW' => "30",
];


$CONFIG['INDEX_ARTICLE'] = 1;

$CONFIG['DONATE_ARTILCE_ID'] = 2;
$CONFIG['DONATE_MONTH_GOAL'] = 75;

$CONFIG['GLOBAL_FREELEECH'] = false;

$CONFIG['FEATURE_EMAIL_REENABLE'] = true;
$CONFIG['FREE_PROBABILITY'] = 20;

$CONFIG['PUSH_CONFIG_SOCKET_LISTEN_ADDRESS'] = "127.0.0.1";
$CONFIG['PUSH_CONFIG_SOCKET_LISTEN_PORT'] = 6789;

$CONFIG['TORRENT_UPLOAD_FREE'] = false;
