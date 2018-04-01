<?php

namespace Controllers;

require '../vendor/autoload.php';

use \Slim\Http\Request as Request;
use \Slim\Http\Response as Response;

class Permissions
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
            $this->logger->info('Non admin user tries to get permissions');
            return $res->withJson(['error' => 'You must be admin to use this feature'], 403);
        }

        $paging = new \Utils\PagingInfo($req->getQueryParam('page'), $req->getQueryParam('pagesize'));
        $user = intval($args['user']);

        $permissions = new \Operations\Permissions($this->c);

        $results = $permissions->getPermissions($paging, $user);

        return $res->withJson([
            'paging' => $paging->toArray(),
            'results' => $results
        ], 200);
    }

    public function postNew(Request $req, Response $res, array $args)
    {
        $ac = new \Operations\AccessControl($this->c);
        if (!$ac->isAdmin($req->getAttribute('userId'))) {
            $this->logger->info('Non admin user tries to add permissions');
            return $res->withJson(['error' => 'You must be admin to use this feature'], 403);
        }

        $body = $req->getParsedBody();

        if (!array_key_exists('domainId', $body)) {
            $this->logger->debug('One of the required fields is missing');
            return $res->withJson(['error' => 'One of the required fields is missing'], 422);
        }

        $user = intval($args['user']);

        $permissions = new \Operations\Permissions($this->c);

        try {
            $permissions->addPermission($user, $body['domainId']);

            $this->logger->info('Permission was added:', ['by' => $req->getAttribute('userId'), 'user' => $user, 'domain' => $body['domainId']]);
            return $res->withStatus(204);
        } catch (\Exceptions\NotFoundException $e) {
            return $res->withJson(['error' => 'Either domain or user were not found'], 404);
        }
    }

    public function delete(Request $req, Response $res, array $args)
    {
        $ac = new \Operations\AccessControl($this->c);
        if (!$ac->isAdmin($req->getAttribute('userId'))) {
            $this->logger->info('Non admin user tries to add permissions');
            return $res->withJson(['error' => 'You must be admin to use this feature'], 403);
        }

        $user = intval($args['user']);
        $domainId = intval($args['domainId']);

        $permissions = new \Operations\Permissions($this->c);

        try {
            $permissions->deletePermission($user, $domainId);

            $this->logger->info('Permission was removed:', ['by' => $req->getAttribute('userId'), 'user' => $user, 'domain' => $domainId]);
            return $res->withStatus(204);
        } catch (\Exceptions\NotFoundException $e) {
            return $res->withJson(['error' => 'Either domain or user were not found'], 404);
        }
    }
}
