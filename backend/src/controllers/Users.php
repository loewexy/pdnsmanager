<?php

namespace Controllers;

require '../vendor/autoload.php';

use \Slim\Http\Request as Request;
use \Slim\Http\Response as Response;

class Users
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

    public function getList(Request $req, Response $res, array $args)
    {
        $ac = new \Operations\AccessControl($this->c);
        if (!$ac->isAdmin($req->getAttribute('userId'))) {
            $this->logger->info('Non admin user tries to get users');
            return $res->withJson(['error' => 'You must be admin to use this feature'], 403);
        }

        $users = new \Operations\Users($this->c);

        $paging = new \Utils\PagingInfo($req->getQueryParam('page'), $req->getQueryParam('pagesize'));
        $query = $req->getQueryParam('query');
        $sort = $req->getQueryParam('sort');
        $type = $req->getQueryParam('type');

        $results = $users->getUsers($paging, $query, $type, $sort);

        return $res->withJson([
            'paging' => $paging->toArray(),
            'results' => $results
        ], 200);
    }

    public function postNew(Request $req, Response $res, array $args)
    {
        $ac = new \Operations\AccessControl($this->c);
        if (!$ac->isAdmin($req->getAttribute('userId'))) {
            $this->logger->info('Non admin user tries to add user');
            return $res->withJson(['error' => 'You must be admin to use this feature'], 403);
        }

        $body = $req->getParsedBody();

        if (!array_key_exists('name', $body) ||
            !array_key_exists('type', $body) ||
            !array_key_exists('password', $body)) {
            $this->logger->debug('One of the required fields is missing');
            return $res->withJson(['error' => 'One of the required fields is missing'], 422);
        }

        $name = $body['name'];
        $type = $body['type'];
        $password = $body['password'];

        $users = new \Operations\Users($this->c);

        try {
            $result = $users->addUser($name, $type, $password);

            $this->logger->info('Created user', $result);
            return $res->withJson($result, 201);
        } catch (\Exceptions\AlreadyExistentException $e) {
            $this->logger->debug('User with name ' . $name . ' already exists.');
            return $res->withJson(['error' => 'User with name ' . $name . ' already exists.'], 409);
        } catch (\Exceptions\SemanticException $e) {
            $this->logger->info('Invalid type for new user', ['type' => $type]);
            return $res->withJson(['error' => 'Invalid type allowed are admin and user'], 400);
        }
    }

    public function delete(Request $req, Response $res, array $args)
    {
        $ac = new \Operations\AccessControl($this->c);
        if (!$ac->isAdmin($req->getAttribute('userId'))) {
            $this->logger->info('Non admin user tries to delete user');
            return $res->withJson(['error' => 'You must be admin to use this feature'], 403);
        }

        $users = new \Operations\Users($this->c);

        $user = intval($args['user']);

        try {
            $users->deleteDomain($user);

            $this->logger->info('Deleted user', ['id' => $user]);
            return $res->withStatus(204);
        } catch (\Exceptions\NotFoundException $e) {
            return $res->withJson(['error' => 'No user found for id ' . $user], 404);
        }
    }

    public function getSingle(Request $req, Response $res, array $args)
    {
        $ac = new \Operations\AccessControl($this->c);
        if ($args['user'] === 'me') {
            $user = $req->getAttribute('userId');
        } elseif ($ac->isAdmin($req->getAttribute('userId'))) {
            $user = intval($args['user']);
        } else {
            $this->logger->info('Non admin user tries to get other user');
            return $res->withJson(['error' => 'You must be admin to use this feature'], 403);
        }

        $users = new \Operations\Users($this->c);

        try {
            $result = $users->getUser($user);

            $this->logger->debug('Get user info', ['id' => $user]);
            return $res->withJson($result, 200);
        } catch (\Exceptions\NotFoundException $e) {
            return $res->withJson(['error' => 'No user found for id ' . $user], 404);
        }
    }

    public function put(Request $req, Response $res, array $args)
    {
        $body = $req->getParsedBody();

        $name = array_key_exists('name', $body) ? $body['name'] : null;
        $type = array_key_exists('type', $body) ? $body['type'] : null;
        $password = array_key_exists('password', $body) ? $body['password'] : null;

        $ac = new \Operations\AccessControl($this->c);
        if ($args['user'] === 'me') {
            $user = $req->getAttribute('userId');
            $name = null;
            $type = null;
        } elseif ($ac->isAdmin($req->getAttribute('userId'))) {
            $user = intval($args['user']);
        } else {
            $this->logger->info('Non admin user tries to get other user');
            return $res->withJson(['error' => 'You must be admin to use this feature'], 403);
        }

        $users = new \Operations\Users($this->c);

        try {
            $result = $users->updateUser($user, $name, $type, $password);

            $this->logger->debug('Update user', ['id' => $user]);
            return $res->withStatus(204);
        } catch (\Exceptions\NotFoundException $e) {
            $this->logger->debug('Trying to update non existing user', ['id' => $user]);
            return $res->withJson(['error' => 'No user found for id ' . $user], 404);
        } catch (\Exceptions\AlreadyExistentException $e) {
            $this->logger->debug('Trying to rename user to conflicting name', ['id' => $user]);
            return $res->withJson(['error' => 'The new name already exists.'], 409);
        }
    }
}
