<?php

require(__DIR__ . '/../config.default.php');
require(__DIR__ . '/../config.local.php');
define('CONFIG', $CONFIG);
define('IS_DEV', $CONFIG['IS_DEV']);
