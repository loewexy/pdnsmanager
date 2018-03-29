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
}
