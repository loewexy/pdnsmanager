<?php

namespace Controllers;

require '../vendor/autoload.php';

class NotFound
{
    public function __invoke(\Slim\Container $c)
    {
        return function ($request, $response) use ($c) {
            $c->logger->warning('No valid endpoint found for: ' . $request->getUri()->getPath());
            return $c['response']->withJson(array('error' => 'No valid endpoint found!'), 404);
        };
    }
}
