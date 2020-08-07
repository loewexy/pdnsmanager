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
        'native' => [
            'plugin' => 'native',
            'prefix' => 'default',
            'config' => null
        ]
    ],
    'remote' => [
        'timestampWindow' => 15
    ],
    'records' => [
        'allowedTypes' => [
            'A', 'A6', 'AAAA', 'AFSDB', 'ALIAS', 'CAA', 'CDNSKEY', 'CDS', 'CERT', 'CNAME', 'DHCID',
            'DLV', 'DNAME', 'DNSKEY', 'DS', 'EUI48', 'EUI64', 'HINFO',
            'IPSECKEY', 'KEY', 'KX', 'LOC', 'LUA', 'MAILA', 'MAILB', 'MINFO', 'MR',
            'MX', 'NAPTR', 'NS', 'NSEC', 'NSEC3', 'NSEC3PARAM', 'OPENPGPKEY',
            'OPT', 'PTR', 'RKEY', 'RP', 'RRSIG', 'SIG', 'SPF',
            'SRV', 'TKEY', 'SSHFP', 'TLSA', 'TSIG', 'TXT', 'WKS', 'MBOXFW', 'URL'
        ]
    ],
    'proxys' => [],
    'dbVersion' => 7
];

if (file_exists('../config/ConfigOverride.php')) {
    $userConfig = require('ConfigOverride.php');
} elseif (file_exists('../config/ConfigUser.php')) {
    $userConfig = require('ConfigUser.php');
} else {
    return false;
}

return array('config' => array_replace_recursive($defaultConfig, $userConfig));
