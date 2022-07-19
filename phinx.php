<?php

require(__DIR__ . '/classes/config.php');
require(__DIR__ . '/classes/const.php');

return
    [
        'paths' => [
            'migrations' => '%%PHINX_CONFIG_DIR%%/db/migrations',
            'seeds' => '%%PHINX_CONFIG_DIR%%/db/seeds'
        ],
        'environments' => [
            'default_migration_table' => 'phinxlog',
            'default_database' => 'gazelle',
            'gazelle' => [
                'adapter' => 'mysql',
                'host' => CONFIG['SQLHOST'],
                'name' => CONFIG['SQLDB'],
                'user' => CONFIG['SQLLOGIN'],
                'pass' => CONFIG['SQLPASS'],
                'port' => CONFIG['SQLPORT'],
                'charset' => 'utf8'
            ],
        ],
        'version_order' => 'creation'
    ];
