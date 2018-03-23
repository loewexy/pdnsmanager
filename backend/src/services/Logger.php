<?php

namespace Services;

require '../vendor/autoload.php';

class Logger
{
    public function __invoke(\Slim\Container $c)
    {
        $config = $c['config']['logging'];

        $logger = new \Monolog\Logger('pdnsmanager');

        $loglevel = \Monolog\Logger::toMonologLevel($config['level']);
        $path = $config['path'];

        if (\strlen($path) > 0) {
            $fileHandler = new \Monolog\Handler\StreamHandler($path, $loglevel);
            $logger->pushHandler($fileHandler);
        } else {
            $errorLogHandler = new \Monolog\Handler\ErrorLogHandler(0, $loglevel);
            $logger->pushHandler($errorLogHandler);
        }

        return $logger;
    }
}
