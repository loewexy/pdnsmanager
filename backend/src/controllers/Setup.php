<?php

namespace Controllers;

require '../vendor/autoload.php';

use \Slim\Http\Request as Request;
use \Slim\Http\Response as Response;

class Setup
{
    public function setup(Request $req, Response $res, array $args)
    {
        // Check if supplied data has all fields
        $body = $req->getParsedBody();

        if ($body === null) {
            return $res->withJson(['error' => 'The supplied body was empty'], 400);
        }

        if (!array_key_exists('db', $body) || !array_key_exists('admin', $body)) {
            return $res->withJson(['error' => 'One of the required fields is missing.'], 422);
        }

        $db = $body['db'];
        $admin = $body['admin'];

        if (!array_key_exists('host', $db) || !array_key_exists('user', $db) ||
            !array_key_exists('password', $db) || !array_key_exists('database', $db) ||
            !array_key_exists('port', $db) || !array_key_exists('name', $admin) ||
            !array_key_exists('password', $admin)) {
            return $res->withJson(['error' => 'One of the required fields is missing.'], 422);
        }

        // Check if pdo exists
        if (!extension_loaded('pdo')) {
            return $res->withJson(['error' => 'PDO extension is not enabled.'], 500);
        }
        if (!extension_loaded('pdo_mysql')) {
            return $res->withJson(['error' => 'PDO mysql extension is not enabled.'], 500);
        }

        // Check if apcu exists
        if (!extension_loaded('apcu')) {
            return $res->withJson(['error' => 'APCU extension is not enabled.'], 500);
        }

        try {
            // Test database connection
            $pdo = new \PDO(
                'mysql:host=' . $db['host'] . ';port=' . $db['port'] . ';dbname=' . $db['database'],
                $db['user'],
                $db['password']
            );

            // Configure db connection
            $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);

            // Check if database is empty
            $query = $pdo->prepare('SHOW TABLES');
            $query->execute();
            if ($query->fetch() !== false) {
                return $res->withJson(['error' => 'The database is not empty.'], 500);
            }

            // Check if config can be written
            if (file_put_contents('../config/ConfigUser.php', 'test') === false) {
                return $res->withJson(['error' => 'Write of config file failed, check that the PHP user can write in the config directory.'], 500);
            } else {
                unlink('../config/ConfigUser.php');
            }

            // Execute sql from setup file
            $sqlLines = explode(';', file_get_contents('../sql/setup.sql'));

            foreach ($sqlLines as $sql) {
                if (strlen(preg_replace('/\s+/', '', $sql)) > 0) {
                    $pdo->exec($sql);
                }
            }

            // Create admin user
            $query = $pdo->prepare('INSERT INTO users (name, backend, type, password) VALUES (:name, :backend, :type, :password)');
            $query->bindValue(':name', $admin['name']);
            $query->bindValue(':backend', 'native');
            $query->bindValue(':type', 'admin');
            $query->bindValue(':password', password_hash($admin['password'], PASSWORD_DEFAULT));
            $query->execute();

            // Save config file
            $config = [
                'db' => [
                    'host' => $db['host'],
                    'user' => $db['user'],
                    'password' => $db['password'],
                    'dbname' => $db['database'],
                    'port' => intval($db['port'])
                ]
            ];
            $configFile = '<?php' . "\n\n" . 'return ' . var_export($config, true) . ';';
            file_put_contents('../config/ConfigUser.php', $configFile);
        } catch (\PDOException $e) {
            return $res->withJson(['error' => $e->getMessage()], 500);
        }

        return $res->withStatus(204);
    }
}
