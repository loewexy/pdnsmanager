<?php

$defaultConfig = [
    'db' => [
        'host' => 'localhost',
        'user' => 'user',
        'password' => 'password',
        'dbname' => 'pdnsmanager',
        'port' => 3306
    ],
    'logging' => [
        'level' => 'info',
        'path' => ''
    ],
    'sessionstorage' => [
        'plugin' => 'apcu',
        'timeout' => 3600,
        'config' => null
    ],
    'authentication' => [
        'default' => [
            'plugin' => 'native',
            'config' => null
        ]
    ]
];

if (file_exists('../config/ConfigOverride.php')) {
    $userConfig = require('ConfigOverride.php');
} else {
    $userConfig = require('ConfigUser.php');
}

return array('config' => array_replace_recursive($defaultConfig, $userConfig));
