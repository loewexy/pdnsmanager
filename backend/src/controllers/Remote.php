<?php

namespace Controllers;

require '../vendor/autoload.php';

use \Slim\Http\Request as Request;
use \Slim\Http\Response as Response;

class Remote
{
    /** @var \Monolog\Logger */
    private $logger;

    /** @var \Slim\Container */
    private $c;

    public function __construct(\Slim\Container $c)
    {
        $this->logger = $c->logger;
        $this->c = $c;
    }

    public function ip(Request $req, Response $res, array $args)
    {
        return $res->withJson([
            'ip' => $req->getAttribute('clientIp')
        ], 200);
    }

    public function updatePassword(Request $req, Response $res, array $args)
    {
        $record = $req->getParam('record');
        $content = $req->getParam('content');
        $password = $req->getParam('password');

        if ($record === null || $content === null || $password === null) {
            return $res->withJson(['error' => 'One of the required fields is missing.'], 422);
        }

        $remote = new \Operations\Remote($this->c);

        try {
            $remote->updatePassword(intval($record), $content, $password);
        } catch (\Exceptions\NotFoundException $e) {
            $this->logger->debug('User tried to update non existent record via changepw api.');
            return $res->withJson(['error' => 'The given record does not exist.'], 404);
        } catch (\Exceptions\ForbiddenException $e) {
            $this->logger->debug('User tried to update an record via changepw api with incorrect password.');
            return $res->withJson(['error' => 'The provided password was invalid.'], 403);
        }

        $this->logger->info('Record ' . $record . ' was changed via the changepw api.');
        return $res->withStatus(204);
    }

    public function servertime(Request $req, Response $res, array $args)
    {
        return $res->withJson([
            'time' => time()
        ], 200);
    }
}
