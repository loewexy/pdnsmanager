<?php

namespace Controllers;

require '../vendor/autoload.php';

use \Slim\Http\Request as Request;
use \Slim\Http\Response as Response;

class Domains
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
        $domains = new \Operations\Domains($this->c);

        $paging = new \Utils\PagingInfo($req->getQueryParam('page'), $req->getQueryParam('pagesize'));
        $query = $req->getQueryParam('query');
        $sort = $req->getQueryParam('sort');
        $type = $req->getQueryParam('type');

        $userId = $req->getAttribute('userId');

        $results = $domains->getDomains($paging, $userId, $query, $sort, $type);

        return $res->withJson([
            'paging' => $paging->toArray(),
            'results' => $results
        ], 200);
    }

    public function postNew(Request $req, Response $res, array $args)
    {
        $ac = new \Operations\AccessControl($this->c);
        if (!$ac->isAdmin($req->getAttribute('userId'))) {
            $this->logger->info('Non admin user tries to add domain');
            return $res->withJson(['error' => 'You must be admin to use this feature'], 403);
        }

        $body = $req->getParsedBody();

        if (!array_key_exists('name', $body) ||
            !array_key_exists('type', $body) || ($body['type'] === 'SLAVE' && !array_key_exists('master', $body))) {
            $this->logger->debug('One of the required fields is missing');
            return $res->withJson(['error' => 'One of the required fields is missing'], 422);
        }

        $name = $body['name'];
        $type = $body['type'];
        $master = isset($body['master']) ? $body['master'] : null;

        $domains = new \Operations\Domains($this->c);

        try {
            $result = $domains->addDomain($name, $type, $master);

            $this->logger->info('Created domain', $result);
            return $res->withJson($result, 201);
        } catch (\Exceptions\AlreadyExistentException $e) {
            $this->logger->debug('Zone with name ' . $name . ' already exists.');
            return $res->withJson(['error' => 'Zone with name ' . $name . ' already exists.'], 409);
        } catch (\Exceptions\SemanticException $e) {
            $this->logger->info('Invalid type for new domain', ['type' => $type]);
            return $res->withJson(['error' => 'Invalid type allowed are MASTER, NATIVE and SLAVE'], 400);
        }
    }

    public function delete(Request $req, Response $res, array $args)
    {
        $ac = new \Operations\AccessControl($this->c);
        if (!$ac->isAdmin($req->getAttribute('userId'))) {
            $this->logger->info('Non admin user tries to delete domain');
            return $res->withJson(['error' => 'You must be admin to use this feature'], 403);
        }

        $domains = new \Operations\Domains($this->c);

        $domainId = intval($args['domainId']);

        try {
            $domains->deleteDomain($domainId);

            $this->logger->info('Deleted domain', ['id' => $domainId]);
            return $res->withStatus(204);
        } catch (\Exceptions\NotFoundException $e) {
            return $res->withJson(['error' => 'No domain found for id ' . $domainId], 404);
        }
    }

    public function getSingle(Request $req, Response $res, array $args)
    {
        $userId = $req->getAttribute('userId');
        $domainId = intval($args['domainId']);

        $ac = new \Operations\AccessControl($this->c);
        if (!$ac->canAccessDomain($userId, $domainId)) {
            $this->logger->info('Non admin user tries to get domain without permission.');
            return $res->withJson(['error' => 'You have no permissions for this domain.'], 403);
        }

        $domains = new \Operations\Domains($this->c);

        try {
            $result = $domains->getDomain($domainId);

            $this->logger->debug('Get domain info', ['id' => $domainId]);
            return $res->withJson($result, 200);
        } catch (\Exceptions\NotFoundException $e) {
            return $res->withJson(['error' => 'No domain found for id ' . $domainId], 404);
        }
    }

    public function put(Request $req, Response $res, array $args)
    {
        $userId = $req->getAttribute('userId');
        $domainId = intval($args['domainId']);
        $ac = new \Operations\AccessControl($this->c);
        if (!$ac->canAccessDomain($userId, $domainId)) {
            $this->logger->info('User tries to update domain without permission');
            return $res->withJson(['error' => 'You have no permissions for this domain.'], 403);
        }

        $body = $req->getParsedBody();

        if (!array_key_exists('master', $body)) {
            $this->logger->debug('One of the required fields is missing');
            return $res->withJson(['error' => 'One of the required fields is missing'], 422);
        }

        $master = $body['master'];

        $domains = new \Operations\Domains($this->c);

        try {
            $result = $domains->updateSlave($domainId, $master);

            $this->logger->debug('Update master', ['id' => $domainId]);
            return $res->withStatus(204);
        } catch (\Exceptions\NotFoundException $e) {
            $this->logger->debug('Trying to update non existing slave zone', ['id' => $domainId]);
            return $res->withJson(['error' => 'No domain found for id ' . $domainId], 404);
        } catch (\Exceptions\SemanticException $e) {
            $this->logger->debug('Trying to update non slave zone', ['id' => $domainId]);
            return $res->withJson(['error' => 'Domain is not a slave zone'], 405);
        }
    }

    public function putSoa(Request $req, Response $res, array $args)
    {
        $userId = $req->getAttribute('userId');
        $domainId = $args['domainId'];

        $ac = new \Operations\AccessControl($this->c);
        if (!$ac->canAccessDomain($userId, $domainId)) {
            $this->logger->info('Non admin user tries to get domain without permission.');
            return $res->withJson(['error' => 'You have no permissions for this domain.'], 403);
        }

        $body = $req->getParsedBody();

        if (!array_key_exists('primary', $body) ||
            !array_key_exists('email', $body) ||
            !array_key_exists('refresh', $body) ||
            !array_key_exists('retry', $body) ||
            !array_key_exists('expire', $body) ||
            !array_key_exists('ttl', $body)) {
            $this->logger->debug('One of the required fields is missing');
            return $res->withJson(['error' => 'One of the required fields is missing'], 422);
        }

        $soa = new \Operations\Soa($this->c);

        try {
            $soa->setSoa(
                intval($domainId),
                $body['email'],
                $body['primary'],
                intval($body['refresh']),
                intval($body['retry']),
                intval($body['expire']),
                intval($body['ttl'])
            );

            return $res->withStatus(204);
        } catch (\Exceptions\NotFoundException $e) {
            $this->logger->warning('Trying to set soa for not existing domain.', ['domainId' => $domainId]);
            return $res->withJson(['error' => 'No domain found for id ' . $domainId], 404);
        } catch (\Exceptions\SemanticException $e) {
            $this->logger->warning('Trying to set soa for slave domain.', ['domainId' => $domainId]);
            return $res->withJson(['error' => 'SOA can not be set for slave domains'], 405);
        }
    }

    public function getSoa(Request $req, Response $res, array $args)
    {
        $userId = $req->getAttribute('userId');
        $domainId = $args['domainId'];

        $ac = new \Operations\AccessControl($this->c);
        if (!$ac->canAccessDomain($userId, $domainId)) {
            $this->logger->info('Non admin user tries to get domain without permission.');
            return $res->withJson(['error' => 'You have no permissions for this domain.'], 403);
        }

        $soa = new \Operations\Soa($this->c);

        try {
            $soaArray = $soa->getSoa($domainId);

            return $res->withJson($soaArray, 200);
        } catch (\Exceptions\NotFoundException $e) {
            $this->logger->debug('User tried to get non existing soa.', ['domainId' => $domainId]);
            return $res->withJson(['error' => 'This domain has no soa record.'], 404);
        }
    }
}
