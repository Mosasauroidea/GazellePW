<?php

//Captcha fonts should be located in /classes/fonts
$CaptchaFonts = array(
    'ARIBLK.TTF',
    'IMPACT.TTF',
    'TREBUC.TTF',
    'TREBUCBD.TTF',
    'TREBUCBI.TTF',
    'TREBUCIT.TTF',
    'VERDANA.TTF',
    'VERDANAB.TTF',
    'VERDANAI.TTF',
    'VERDANAZ.TTF'
);
//Captcha images should be located in /captcha
$CaptchaBGs = array(
    'captcha1.png',
    'captcha2.png',
    'captcha3.png',
    'captcha4.png',
    'captcha5.png',
    'captcha6.png',
    'captcha7.png',
    'captcha8.png',
    'captcha9.png'
);

$Codecs = array('DivX', 'XviD', 'x264', 'H.264', 'x265', 'H.265', 'Other');
$Sources = array('VHS', 'DVD', 'HD-DVD', 'TV', 'HDTV', 'WEB', 'Blu-ray', 'Other');
$Containers = array('AVI', 'MPG', 'MP4', 'MKV', 'VOB IFO', 'ISO', 'm2ts', 'Other');
$Resolutions = array('NTSC', 'PAL', '480p', '576p', '720p', '1080i', '1080p', '2160p', 'Other');
$Processings = array("---", "Encode", "Remux", "DIY", "Untouched");

$StandardDefinition = ['Other', 'NTSC', 'PAL', '480p', '576p'];
$HighDefinition = ['720p', '1080i', '1080p'];
$UltraDefinition = ['2160p'];

define('SUBGROUP_SD', 1);
define('SUBGROUP_HD', 2);
define('SUBGROUP_UHD', 3);
define('SUBGROUP_3D', 4);
define('SUBGROUP_Extra', 5);

$Categories = array('Movies');
$GroupedCategories = array_intersect(array('Movies'), $Categories);
$CategoryIcons = array('movies.png');

$CollageCats = [1, 2, 3, 4, 5, 6, 7, 8];

$PersonalCollageCategoryCat = 1;

$ReleaseTypes = [1, 2, 5, 6];

// Ratio requirements, in descending order
// Columns: Download amount, required ratio, grace period
$RatioRequirements = array(
    array(50 * 1024 * 1024 * 1024, 0.60, date('Y-m-d H:i:s')),
    array(40 * 1024 * 1024 * 1024, 0.50, date('Y-m-d H:i:s')),
    array(30 * 1024 * 1024 * 1024, 0.40, date('Y-m-d H:i:s')),
    array(20 * 1024 * 1024 * 1024, 0.30, date('Y-m-d H:i:s')),
    array(10 * 1024 * 1024 * 1024, 0.20, date('Y-m-d H:i:s')),
    array(5 * 1024 * 1024 * 1024,  0.15, date('Y-m-d H:i:s', time() - (60 * 60 * 24 * 14)))
);

// Special characters, and what they should be converted to
// Used for torrent searching
$SpecialChars = array(
    '&' => 'and'
);

$UserDemoteCriteria = [
    [
        'From' => CONFIG['USER_CLASS']['GURU'],
        'To' => CONFIG['USER_CLASS']['ELITE_TM'],
        'MinUploads' => 1000,
    ],
    [
        'From' => CONFIG['USER_CLASS']['ELITE_TM'],
        'To' => CONFIG['USER_CLASS']['POWER_TM'],
        'MinUploads' => 500,
    ],

    [
        'From' => CONFIG['USER_CLASS']['POWER_TM'],
        'To' => CONFIG['USER_CLASS']['TORRENT_MASTER'],
        'MinUploads' => 250,
    ],

    [
        'From' => CONFIG['USER_CLASS']['TORRENT_MASTER'],
        'To' => CONFIG['USER_CLASS']['ELITE'],
        'MinUploads' => 100,
    ],
    [
        'From' => CONFIG['USER_CLASS']['ELITE'],
        'To' => CONFIG['USER_CLASS']['POWER'],
        'MinUploads' => 25,
    ],
    [
        'From' => CONFIG['USER_CLASS']['POWER'],
        'To' => CONFIG['USER_CLASS']['MEMBER'],
        'MinRatio' => 0.95,
        'MinUploads' => 1,
    ],
    [
        'From' => CONFIG['USER_CLASS']['MEMBER'],
        'To' => CONFIG['USER_CLASS']['USER'],
        'MinRatio' => 0.65,
    ],

];

$TorrentBonus = [20, 120, 400, 1200];
$ForumBonus = [40, 200, 800, 2000];
$ForumAdminBonus = [400, 2000, 4000, 12000];

$UserPromoteCriteria = [
    [
        'From' => CONFIG['USER_CLASS']['USER'],
        'To' => CONFIG['USER_CLASS']['MEMBER'],
        'MinUpload' => 0,
        'MinDownload' => 80 * 1024 * 1024 * 1024,
        'MinRatio' => 0.8,
        'MinUploads' => 0,
        'Weeks' => 1,
        'AwardLevel' => 1,
    ],
    [
        'From' => CONFIG['USER_CLASS']['MEMBER'],
        'To' => CONFIG['USER_CLASS']['POWER'],
        'MinUpload' => 0,
        'MinDownload' => 200 * 1024 * 1024 * 1024,
        'MinRatio' => 1.2,
        'MinUploads' => 1,
        'Weeks' => 2,
        'AwardLevel' => 2,
        'Invite' => 1,
    ],
    [
        'From' => CONFIG['USER_CLASS']['POWER'],
        'To' => CONFIG['USER_CLASS']['ELITE'],
        'MinUpload' => 0,
        'MinDownload' => 500 * 1024 * 1024 * 1024,
        'MinRatio' => 1.2,
        'MinUploads' => 25,
        'Weeks' => 4,
        'AwardLevel' => 3,
        'Invite' => 1,
    ],
    [
        'From' => CONFIG['USER_CLASS']['ELITE'],
        'To' => CONFIG['USER_CLASS']['TORRENT_MASTER'],
        'MinUpload' => 0,
        'MinDownload' => 1 * 1024 * 1024 * 1024 * 1024,
        'MinRatio' => 1.2,
        'MinUploads' => 100,
        'Weeks' => 8,
        'AwardLevel' => 4,
        'Invite' => 2,

    ],
    [
        'From' => CONFIG['USER_CLASS']['TORRENT_MASTER'],
        'To' => CONFIG['USER_CLASS']['POWER_TM'],
        'MinUpload' => 0,
        'MinDownload' => 2 * 1024 * 1024 * 1024 * 1024,
        'MinRatio' => 1.2,
        'MinUploads' => 250,
        'Weeks' => 12,
        'AwardLevel' => 5,
        'Invite' => 2,

    ],
    [
        'From' => CONFIG['USER_CLASS']['POWER_TM'],
        'To' => CONFIG['USER_CLASS']['ELITE_TM'],
        'MinUpload' => 0,
        'MinDownload' => 5 * 1024 * 1024 * 1024 * 1024,
        'MinRatio' => 1.2,
        'MinUploads' => 500,
        'Weeks' => 16,
        'AwardLevel' => 6,
        'Invite' => 3,
    ],
    [
        'From' => CONFIG['USER_CLASS']['ELITE_TM'],
        'To' => CONFIG['USER_CLASS']['GURU'],
        'MinUpload' => 0,
        'MinDownload' => 10 * 1024 * 1024 * 1024 * 1024,
        'MinRatio' => 1.2,
        'MinUploads' => 1000,
        'Weeks' => 20,
        'AwardLevel' => 7,
    ]
];

define('HNR_MIN_SIZE_PERCENT', 0.2);
define('HNR_MIN_MIN_RATIO', 1);
define('HNR_MIN_SEEEDING_TIME', 72 * 3600);
define('HNR_INTERVAL', 14 * 24 * 3600);

define('MAX_RANK', 6);
define('MAX_SPECIAL_RANK', 5);
define('DONOR_FORUM_RANK', 6);

define('VERSION', @file_get_contents(CONFIG['SERVER_ROOT'] . '/VERSION'));

define('DURATION', [
    '12h' => 12 * 3600 * 1000,
    '24h' => 24 * 3600 * 1000,
]);

define('MAX_PERS_COLLAGES', 3); // How many personal collages should be shown by default
define('MAX_COLLAGES', 5); // How many normal collages should be shown by default

define('TORRENT_DEAD_PERIOD', 28);
