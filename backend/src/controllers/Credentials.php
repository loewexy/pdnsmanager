<?php

namespace Controllers;

require '../vendor/autoload.php';

use \Slim\Http\Request as Request;
use \Slim\Http\Response as Response;

class Credentials
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
        $userId = $req->getAttribute('userId');
        $recordId = intval($args['recordId']);

        $ac = new \Operations\AccessControl($this->c);
        if (!$ac->canAccessRecord($userId, $recordId)) {
            $this->logger->info('Non admin user tries to get credentials for record without permission.');
            return $res->withJson(['error' => 'You have no permissions for this record.'], 403);
        }

        $credentials = new \Operations\Credentials($this->c);

        $paging = new \Utils\PagingInfo($req->getQueryParam('page'), $req->getQueryParam('pagesize'));

        $results = $credentials->getCredentials($paging, $recordId);

        return $res->withJson([
            'paging' => $paging->toArray(),
            'results' => $results
        ], 200);
    }

    public function postNew(Request $req, Response $res, array $args)
    {
        $body = $req->getParsedBody();

        if (!array_key_exists('description', $body) ||
            !array_key_exists('type', $body) || ($body['type'] === 'key' &&
            !array_key_exists('key', $body)) || ($body['type'] === 'password' &&
            !array_key_exists('password', $body))) {
            $this->logger->debug('One of the required fields is missing');
            return $res->withJson(['error' => 'One of the required fields is missing'], 422);
        }

        $userId = $req->getAttribute('userId');
        $recordId = intval($args['recordId']);

        $ac = new \Operations\AccessControl($this->c);
        if (!$ac->canAccessRecord($userId, $recordId)) {
            $this->logger->info('User tries to add credential for record without permission.');
            return $res->withJson(['error' => 'You have no permissions for the given record.'], 403);
        }

        $credentials = new \Operations\Credentials($this->c);

        $key = array_key_exists('key', $body) ? $body['key'] : null;
        $password = array_key_exists('password', $body) ? $body['password'] : null;

        try {
            $result = $credentials->addCredential($recordId, $body['description'], $body['type'], $key, $password);
            return $res->withJson($result, 201);
        } catch (\Exceptions\SemanticException $e) {
            $this->logger->debug('User tries to add credential with wrong type.');
            return $res->withJson(['error' => 'The type is invalid.'], 400);
        } catch (\Exceptions\InvalidKeyException $e) {
            $this->logger->debug('User tries to add invalid credential key.');
            return $res->withJson(['error' => 'The provided key is invalid.'], 400);
        }
    }

    public function delete(Request $req, Response $res, array $args)
    {
        $userId = $req->getAttribute('userId');
        $recordId = intval($args['recordId']);
        $credentialId = intval($args['credentialId']);

        $ac = new \Operations\AccessControl($this->c);
        if (!$ac->canAccessRecord($userId, $recordId)) {
            $this->logger->info('User tries to delete credential without permissions.');
            return $res->withJson(['error' => 'You have no permission for this record'], 403);
        }

        $credentials = new \Operations\Credentials($this->c);

        try {
            $credentials->deleteCredential($recordId, $credentialId);

            $this->logger->info('Deleted credential', ['id' => $credentialId]);
            return $res->withStatus(204);
        } catch (\Exceptions\NotFoundException $e) {
            return $res->withJson(['error' => 'No credential found for id ' . $credentialId], 404);
        }
    }
}
