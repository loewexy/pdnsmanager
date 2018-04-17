<?php

namespace Controllers;

require '../vendor/autoload.php';

use \Slim\Http\Request as Request;
use \Slim\Http\Response as Response;

class Sessions
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

    public function post(Request $req, Response $res, array $args)
    {
        $body = $req->getParsedBody();

        if (!array_key_exists('username', $body) ||
            !array_key_exists('password', $body)) {
            return $res->withJson(['error' => 'One of the required fields is missing'], 422);
        }

        $userAuth = new \Operations\UserAuth($this->container);
        $sessionStorage = new \Operations\Sessionstorage($this->container);

        $sessionTimeout = $this->container['config']['sessionstorage']['timeout'];

        try {
            $userId = $userAuth->authenticate($body['username'], $body['password']);
        } catch (\Exceptions\PluginNotFoundException $e) {
            return $res->withJson(['error' => $e->getMessage()], 500);
        }

        if ($userId >= 0) {
            $secret = openssl_random_pseudo_bytes(64);
            $secretString = base64_encode($secret);
            $secretString = rtrim(strtr($secretString, '+/', '-_'), '=');

            $sessionStorage->set($secretString, $userId, $sessionTimeout);

            $this->logger->info('User authenticated successfully', ['username' => $body['username']]);
            return $res->withJson([
                'username' => $body['username'],
                'token' => $secretString
            ], 201);
        } else {
            $this->logger->info('User failed to authenticate', ['username' => $body['username'], 'ip' => $req->getAttribute('clientIp')]);
            return $res->withJson(['error' => 'Username or password is invalid'], 403);
        }
    }

    public function delete(Request $req, Response $res, array $args)
    {
        $sessionStorage = new \Operations\Sessionstorage($this->container);

        if ($sessionStorage->exists($args['sessionId'])) {
            $sessionStorage->delete($args['sessionId']);

            $this->logger->info('Deleting session', ['token' => $args['sessionId']]);
            return $res->withStatus(204);
        } else {
            $this->logger->warning('Trying to delete non existing session', ['token' => $args['sessionId']]);
            return $res->withJson(['error' => 'Session not found'], 404);
        }
    }
}
