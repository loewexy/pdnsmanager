<?php
require '../vendor/autoload.php';

use \Slim\Http\Request as Request;
use \Slim\Http\Response as Response;

if (file_exists('../config/ConfigUser.php')) {
    echo "Not accessible!";
    http_response_code(403);
    exit();
}

// Prepare dependency container
$container = new \Slim\Container();

// Create application
$app = new \Slim\App($container);

// Create route
$app->post('/v1/setup', '\Controllers\Setup:setup');

// Run application
$app->run();
