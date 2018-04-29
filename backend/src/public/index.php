<?php

require '../vendor/autoload.php';

use \Slim\Http\Request as Request;
use \Slim\Http\Response as Response;

// Load config
$config = require('../config/ConfigDefault.php');

// If no config exists load installer
if ($config === false) {
    require('setup.php');
    exit();
}

// Prepare dependency container
$container = new \Slim\Container($config);

$container['logger'] = new \Services\Logger;
$container['db'] = new \Services\Database;

$container['notFoundHandler'] = new \Controllers\NotFound;
$container['notAllowedHandler'] = new \Controllers\NotAllowed;

// Create application
$app = new \Slim\App($container);

// Configure routing
$app->group('/v1', function () {
    $this->post('/sessions', '\Controllers\Sessions:post');

    $this->get('/remote/ip', '\Controllers\Remote:ip');
    $this->get('/remote/servertime', '\Controllers\Remote:servertime');
    $this->get('/remote/updatepw', '\Controllers\Remote:updatePassword');
    $this->post('/remote/updatekey', '\Controllers\Remote:updateKey');

    $this->get('/update', '\Controllers\Update:get');
    $this->post('/update', '\Controllers\Update:post');

    $this->group('', function () {
        $this->delete('/sessions/{sessionId}', '\Controllers\Sessions:delete');

        $this->get('/domains', '\Controllers\Domains:getList');
        $this->post('/domains', '\Controllers\Domains:postNew');
        $this->delete('/domains/{domainId}', '\Controllers\Domains:delete');
        $this->get('/domains/{domainId}', '\Controllers\Domains:getSingle');
        $this->put('/domains/{domainId}', '\Controllers\Domains:put');

        $this->put('/domains/{domainId}/soa', '\Controllers\Domains:putSoa');
        $this->get('/domains/{domainId}/soa', '\Controllers\Domains:getSoa');

        $this->get('/records', '\Controllers\Records:getList');
        $this->post('/records', '\Controllers\Records:postNew');
        $this->delete('/records/{recordId}', '\Controllers\Records:delete');
        $this->get('/records/{recordId}', '\Controllers\Records:getSingle');
        $this->put('/records/{recordId}', '\Controllers\Records:put');

        $this->get('/records/{recordId}/credentials', '\Controllers\Credentials:getList');
        $this->post('/records/{recordId}/credentials', '\Controllers\Credentials:postNew');
        $this->delete('/records/{recordId}/credentials/{credentialId}', '\Controllers\Credentials:delete');
        $this->get('/records/{recordId}/credentials/{credentialId}', '\Controllers\Credentials:getSingle');
        $this->put('/records/{recordId}/credentials/{credentialId}', '\Controllers\Credentials:put');

        $this->get('/users', '\Controllers\Users:getList');
        $this->post('/users', '\Controllers\Users:postNew');
        $this->delete('/users/{user}', '\Controllers\Users:delete');
        $this->get('/users/{user}', '\Controllers\Users:getSingle');
        $this->put('/users/{user}', '\Controllers\Users:put');

        $this->get('/users/{user}/permissions', '\Controllers\Permissions:getList');
        $this->post('/users/{user}/permissions', '\Controllers\Permissions:postNew');
        $this->delete('/users/{user}/permissions/{domainId}', '\Controllers\Permissions:delete');
    })->add('\Middlewares\Authentication');
});

// Add global middlewares
$app->add('\Middlewares\LogRequests');
$app->add('\Middlewares\RejectEmptyBody');
$app->add('\Middlewares\ClientIp');

// Run application
$app->run();
