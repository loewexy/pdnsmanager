<?php

namespace Controllers;

require '../vendor/autoload.php';

use \Slim\Http\Request as Request;
use \Slim\Http\Response as Response;

class Records
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
        $records = new \Operations\Records($this->c);

        $paging = new \Utils\PagingInfo($req->getQueryParam('page'), $req->getQueryParam('pagesize'));
        $domain = $req->getQueryParam('domain');
        $queryName = $req->getQueryParam('queryName');
        $type = $req->getQueryParam('type');
        $queryContent = $req->getQueryParam('queryContent');
        $sort = $req->getQueryParam('sort');

        $userId = $req->getAttribute('userId');

        $results = $records->getRecords($paging, $userId, $domain, $queryName, $type, $queryContent, $sort);

        return $res->withJson([
            'paging' => $paging->toArray(),
            'results' => $results
        ], 200);
    }

    public function postNew(Request $req, Response $res, array $args)
    {
        $body = $req->getParsedBody();

        if (!array_key_exists('name', $body) ||
            !array_key_exists('type', $body) ||
            !array_key_exists('content', $body) ||
            !array_key_exists('priority', $body) ||
            !array_key_exists('ttl', $body) ||
            !array_key_exists('domain', $body)) {
            $this->logger->debug('One of the required fields is missing');
            return $res->withJson(['error' => 'One of the required fields is missing'], 422);
        }

        $userId = $req->getAttribute('userId');
        $ac = new \Operations\AccessControl($this->c);
        if (!$ac->canAccessDomain($userId, $body['domain'])) {
            $this->logger->info('User tries to add record for domain without permission.');
            return $res->withJson(['error' => 'You have no permissions for the given domain.'], 403);
        }

        $records = new \Operations\Records($this->c);

        try {
            $result = $records->addRecord($body['name'], $body['type'], $body['content'], $body['priority'], $body['ttl'], $body['domain']);
            return $res->withJson($result, 201);
        } catch (\Exceptions\NotFoundException $e) {
            $this->logger->debug('User tries to add record for invalid domain.');
            return $res->withJson(['error' => 'The domain does not exist or is neighter MASTER nor NATIVE.'], 404);
        } catch (\Exceptions\SemanticException $e) {
            $this->logger->debug('User tries to add record with invalid type.', ['type' => $body['type']]);
            return $res->withJson(['error' => 'The provided type is invalid.'], 400);
        }
    }

    public function delete(Request $req, Response $res, array $args)
    {
        $userId = $req->getAttribute('userId');
        $recordId = intval($args['recordId']);
        $ac = new \Operations\AccessControl($this->c);
        if (!$ac->canAccessRecord($userId, $recordId)) {
            $this->logger->info('User tries to delete record without permissions.');
            return $res->withJson(['error' => 'You have no permission to delete this record'], 403);
        }

        $records = new \Operations\Records($this->c);

        try {
            $records->deleteRecord($recordId);

            $this->logger->info('Deleted record', ['id' => $recordId]);
            return $res->withStatus(204);
        } catch (\Exceptions\NotFoundException $e) {
            return $res->withJson(['error' => 'No record found for id ' . $recordId], 404);
        }
    }

    public function getSingle(Request $req, Response $res, array $args)
    {
        $userId = $req->getAttribute('userId');
        $recordId = intval($args['recordId']);

        $ac = new \Operations\AccessControl($this->c);
        if (!$ac->canAccessRecord($userId, $recordId)) {
            $this->logger->info('Non admin user tries to get record without permission.');
            return $res->withJson(['error' => 'You have no permissions for this record.'], 403);
        }

        $records = new \Operations\Records($this->c);

        try {
            $result = $records->getRecord($recordId);

            $this->logger->debug('Get record info', ['id' => $recordId]);
            return $res->withJson($result, 200);
        } catch (\Exceptions\NotFoundException $e) {
            return $res->withJson(['error' => 'No record found for id ' . $recordId], 404);
        }
    }

    public function put(Request $req, Response $res, array $args)
    {
        $userId = $req->getAttribute('userId');
        $recordId = intval($args['recordId']);

        $ac = new \Operations\AccessControl($this->c);
        if (!$ac->canAccessRecord($userId, $recordId)) {
            $this->logger->info('Non admin user tries to update record without permission.');
            return $res->withJson(['error' => 'You have no permissions for this record.'], 403);
        }

        $body = $req->getParsedBody();

        $name = array_key_exists('name', $body) ? $body['name'] : null;
        $type = array_key_exists('type', $body) ? $body['type'] : null;
        $content = array_key_exists('content', $body) ? $body['content'] : null;
        $priority = array_key_exists('priority', $body) ? $body['priority'] : null;
        $ttl = array_key_exists('ttl', $body) ? $body['ttl'] : null;

        $records = new \Operations\Records($this->c);

        try {
            $records->updateRecord($recordId, $name, $type, $content, $priority, $ttl);
            return $res->withStatus(204);
        } catch (\Exceptions\NotFoundException $e) {
            $this->logger->debug('User tries to update not existing record.');
            return $res->withJson(['error' => 'The record does not exist.'], 404);
        } catch (\Exceptions\SemanticException $e) {
            $this->logger->debug('User tries to update record with invalid type.', ['type' => $type]);
            return $res->withJson(['error' => 'The provided type is invalid.'], 400);
        }
    }
}
