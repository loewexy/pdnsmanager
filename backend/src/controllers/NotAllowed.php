<?php

namespace Controllers;

require '../vendor/autoload.php';

class NotAllowed
{
    public function __invoke(\Slim\Container $c)
    {
        return function ($request, $response, $methods) use ($c) {
            $c->logger->warning('Method ' . $request->getMethod() . ' is not valid for ' . $request->getUri()->getPath());
            return $c['response']
                ->withHeader('Allow', \implode(', ', $methods))
                ->withJson(array('error' => 'Method ' . $request->getMethod() . ' is not valid use on of ' . implode(', ', $methods)), 405);
        };
    }
}
