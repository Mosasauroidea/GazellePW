<?php

require __DIR__ . '/../config.default.php';
@include __DIR__ . '/../config.local.php';
define('CONFIG', $CONFIG);

$WINDOW_CONFIG = [
    'SITE_NAME' => $CONFIG['SITE_NAME'],
    'TG_GROUP' => $CONFIG['TG_GROUP'],
    'TG_DISBALE_CHANNEL' => $CONFIG['TG_DISBALE_CHANNEL'],
    'BOT_HELP_CHAN' => $CONFIG['BOT_HELP_CHAN'],
    'MAIL_HOST' => $CONFIG['MAIL_HOST'],
];

$WINDOW_DATA = [
    'IMAGE_HOST_WHITELIST' => $CONFIG['IMAGE_HOST_WHITELIST'],
];
