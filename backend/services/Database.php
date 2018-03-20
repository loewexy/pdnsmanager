<?php

namespace Services;

require '../vendor/autoload.php';

class Database
{
    public function __invoke(\Slim\Container $c)
    {
        $config = $c['config']['db'];

        try {
            $pdo = new \PDO(
            'mysql:host=' . $config['host'] . ';port=' . $config['port'] . ';dbname=' . $config['dbname'],
            $config['user'],
            $config['password']
        );
        } catch (\PDOException $e) {
            $c->logger->critical("SQL Connect Error: " . $e->getMessage());
            $c->logger->critical("DB Config was", $config);
            exit();
        }

        try {
            $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            $c->logger->critical("SQL Parameter Error: " . $e->getMessage());
            exit();
        }

        $c->logger->debug("Database setup successfull");
        
        return $pdo;
    }
}
