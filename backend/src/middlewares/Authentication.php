<?php

namespace Middlewares;

require '../vendor/autoload.php';

use \Slim\Http\Request as Request;
use \Slim\Http\Response as Response;

class Authentication
{
    /** @var \Monolog\Logger */
    private $logger;

    /** @var \Slim\Container */
    private $container;

    public function __construct(\Slim\Container $c)
    {
        $this->logger = $c->logger;
        $this->container = $c;
    }

    public function __invoke(Request $req, Response $res, callable $next)
    {
        $token = $req->getHeaderLine('X-Authentication');

        $sessionStorage = new \Operations\Sessionstorage($this->container);

        if ($sessionStorage->exists($token)) {
            $sessionTimeout = $this->container['config']['sessionstorage']['timeout'];

            $userId = $sessionStorage->get($token, $sessionTimeout);

            $this->logger->debug('Authentication was successfull', ['token' => $token, 'userId' => $userId]);

            $req = $req->withAttribute('userId', $userId);
            return $next($req, $res);
        } else {
            $this->logger->warning('No valid authentication token found');
            return $res->withJson(['error' => 'No valid authentication token suplied', 'code' => 'invalid_session'], 403);
        }
    }
}
