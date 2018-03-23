<?php

namespace Middlewares;

require '../vendor/autoload.php';

use \Slim\Http\Request as Request;
use \Slim\Http\Response as Response;

class LogRequests
{
    /** @var \Monolog\Logger */
    private $logger;

    public function __construct(\Slim\Container $c)
    {
        $this->logger = $c->logger;
    }

    public function __invoke(Request $req, Response $res, callable $next)
    {
        $this->logger->debug($req->getMethod() . ' ' . $req->getUri()->getPath());

        return $next($req, $res);
    }
}
