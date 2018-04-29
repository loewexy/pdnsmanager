<?php

namespace Controllers;

require '../vendor/autoload.php';

use \Slim\Http\Request as Request;
use \Slim\Http\Response as Response;

class Update
{
    /** @var \Monolog\Logger */
    private $logger;

    /** @var \Slim\Container */
    private $c;

    /** @var \PDO */
    private $db;

    public function __construct(\Slim\Container $c)
    {
        $this->logger = $c->logger;
        $this->db = $c->db;
        $this->c = $c;
    }

    public function get(Request $req, Response $res, array $args)
    {
        $currentVersion = $this->getCurrentVersion();

        $targetVersion = $this->c['config']['dbVersion'];

        if ($currentVersion < $targetVersion) {
            return $res->withJson([
                'updateRequired' => true,
                'currentVersion' => $currentVersion,
                'targetVersion' => $targetVersion
            ], 200);
        } else {
            return $res->withJson(['updateRequired' => false], 200);
        }
    }

    public function post(Request $req, Response $res, array $args)
    {
        $currentVersion = $this->getCurrentVersion();

        $targetVersion = $this->c['config']['dbVersion'];

        if ($currentVersion < $targetVersion) {
            try {
                for ($i = $currentVersion + 1; $i <= $targetVersion; $i++) {
                    $sqlLines = explode(';', file_get_contents('../sql/Update' . $i . '.sql'));

                    foreach ($sqlLines as $sql) {
                        if (strlen(preg_replace('/\s+/', '', $sql)) > 0) {
                            $this->db->exec($sql);
                        }
                    }

                    $this->logger->info('Upgrade to version ' . $i . ' successfull!');
                }
            } catch (\Exception $e) {
                $this->logger->error('Upgrade failed with: ' . $e->getMessage());
                return $res->withJson(['error' => $e->getMessage()], 500);
            }
        }

        return $res->withStatus(204);
    }

    private function getCurrentVersion() : int
    {
        $query = $this->db->prepare('SHOW TABLES LIKE \'options\';');
        $query->execute();
        if ($query->fetch() === false) {
            return 0;
        }

        $query = $this->db->prepare('SELECT value FROM options WHERE name=\'schema_version\'');
        $query->execute();

        return intval($query->fetch()['value']);
    }
}
