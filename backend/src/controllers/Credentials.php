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
}
