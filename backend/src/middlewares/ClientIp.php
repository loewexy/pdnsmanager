<?php

namespace Middlewares;

require '../vendor/autoload.php';

use \Slim\Http\Request as Request;
use \Slim\Http\Response as Response;

class ClientIp
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
        $proxys = $this->container['config']['proxys'];

        $headerContent = $req->getHeaderLine('X-Forwarded-For');

        if (strlen($headerContent) === 0) {
            $ip = $_SERVER['REMOTE_ADDR'];
        } else {
            if (!in_array($_SERVER['REMOTE_ADDR'], $proxys)) { // Client is not trusted proxy
                $ip = $_SERVER['REMOTE_ADDR'];
            } else {
                $parts = array_map('trim', explode(',', $headerContent));

                $ip = $_SERVER['REMOTE_ADDR'];

                for ($i = count($parts) - 1; $i >= 0; $i--) {
                    if (!in_array($parts[$i], $proxys)) {
                        $ip = $parts[$i];
                        break;
                    }
                }
            }
        }

        $req = $req->withAttribute('clientIp', $ip);
        return $next($req, $res);
    }
}
