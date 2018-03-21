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
    private $container;

    public function __construct(\Slim\Container $c)
    {
        $this->logger = $c->logger;
        $this->container = $c;
    }

    public function getList(Request $req, Response $res, array $args)
    {
        $domains = new \Operations\Domains($this->container);

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
}
