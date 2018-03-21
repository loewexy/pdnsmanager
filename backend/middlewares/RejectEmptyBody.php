<?php

namespace Middlewares;

require '../vendor/autoload.php';

use \Slim\Http\Request as Request;
use \Slim\Http\Response as Response;

class RejectEmptyBody
{
    /** @var \Monolog\Logger */
    private $logger;

    public function __construct(\Slim\Container $c)
    {
        $this->logger = $c->logger;
    }

    public function __invoke(Request $req, Response $res, callable $next)
    {
        if (($req->isPost() || $req->isPut() || $req->isPatch()) && $req->getParsedBody() == null) {
            $this->logger->warning('Got empty body in request with method ' . $req->getMethod());

            return $res->withJson(['error' => 'The supplied body was empty'], 400);
        } else {
            return $next($req, $res);
        }
    }
}
