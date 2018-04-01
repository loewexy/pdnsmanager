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
}
